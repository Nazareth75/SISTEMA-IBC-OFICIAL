<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRutaRequest;
use App\Http\Requests\UpdateRutaRequest;
use App\Models\NivelSeguridad;
use App\Models\Ruta;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\ServicioRecomendacionRutaAco;
use App\Services\Aco\ConfiguracionAco;
use App\Services\RutaEstimacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RutaController extends Controller
{
    // ─── Listado ──────────────────────────────────────────────

    public function index(Request $request, ConstructorGrafoRuta $constructorAco): View
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $estado = trim((string) $request->get('estado', ''));

        $rutas = Ruta::activas()
            ->with(['creador', 'aprobador', 'tramos'])
            ->buscador($buscar)
            ->when($estado !== '', fn ($q) => $q->porEstado($estado))
            ->orderByDesc('cod_rutas')
            ->paginate(10)
            ->withQueryString();

        return view('rutas.index', [
            'rutas'          => $rutas,
            'filtros'        => compact('buscar', 'estado'),
            'estados'        => array_keys(Ruta::ESTADOS),
            'constructorAco' => $constructorAco,
        ]);
    }

    // ─── Crear ────────────────────────────────────────────────

    public function create(): View
    {
        abort_unless(Auth::user()?->puedeCrearRuta(), 403);

        return view('rutas.create');
    }

    public function store(StoreRutaRequest $request, RutaEstimacionService $estimador): RedirectResponse
    {
        $data = $request->validated();
        $data['ide_rut']              = Ruta::generarCodigo();
        $data['est_rut']              = 'BORRADOR';
        $data['cod_usuarios_rut_cre'] = Auth::id();

        $geoPersistida = $this->normalizarGeoDesdeRequest($data['geo_rut'] ?? null);

        if ($geoPersistida) {
            $data['geo_rut'] = $geoPersistida;
            $data['dis_rut'] = $data['dis_rut'] ?? ($geoPersistida['distancia_metros'] ?? null);
            $data['tim_rut'] = $data['tim_rut'] ?? ($geoPersistida['tiempo_minutos'] ?? null);
        }

        $estimacion = $this->estimarSiFaltaDistanciaTiempo($data, $estimador);
        if ($estimacion) {
            $data['dis_rut'] = $estimacion['distancia_metros'];
            $data['tim_rut'] = $estimacion['tiempo_minutos'];
            $data['geo_rut'] = $this->normalizarGeoDesdeEstimacion($estimacion);
        }

        $ruta = Ruta::create($data);

        return redirect()->route('rutas.show', $ruta)
            ->with('success', "RUTA {$ruta->ide_rut} CREADA CORRECTAMENTE.");
    }

    // ─── Detalle ──────────────────────────────────────────────

    public function show(
        Ruta $ruta,
        RutaEstimacionService $estimador,
        ConstructorGrafoRuta $constructorAco,
        ServicioRecomendacionRutaAco $recomendadorAco,
        \App\Services\Aco\ServicioEstadoRecomendacionAco $estadoAcoService
    ): View {
        $ruta->load([
            'creador',
            'aprobador',
            'tramos.condiciones',
            'tramos.obstaculos.reportes',
            'tramos.obstaculos.reportadoPor',
            'tramos.evaluaciones.nivelSeguridad',
            'tramos.evaluaciones.evaluador',
            'tramos.zonas.nivelSeguridad',
        ]);

        $nivelGeneral    = $ruta->nivelSeguridadGeneral();
        $transiciones    = $ruta->transicionesPosibles();
        $puedePublicarse = ($ruta->est_rut === 'APROBADA') ? $ruta->puedePublicarse() : null;
        $trayectoDetalle = is_array($ruta->geo_rut) ? $ruta->geo_rut : null;

        if (!$trayectoDetalle && !empty($ruta->ori_rut) && !empty($ruta->dst_rut)) {
            $trayectoDetalle = $estimador->estimar((string) $ruta->ori_rut, (string) $ruta->dst_rut);
        }

        $analisisAco = $constructorAco->analizar($ruta);
        $recomendacionAco = null;
        $recomendacionDesactualizada = $estadoAcoService->estaDesactualizada($ruta);
        $infoDesactualizacion = $estadoAcoService->obtenerInfoDesactualizacion($ruta);
        $historialCambioAco = $estadoAcoService->obtenerHistorialCambio($ruta);

        if ($analisisAco->esOptimizable) {
            try {
                // Si la recomendación está desactualizada y no hay recálculo previo, calculamos nueva
                $recomendacionAco = $recomendadorAco->recomendar($ruta);
                if ($recomendacionAco->success) {
                    $estadoAcoService->guardarRecomendacion($ruta, $recomendacionAco);
                    $historialCambioAco = $estadoAcoService->obtenerHistorialCambio($ruta);
                }
            } catch (\Throwable $e) {
                $recomendacionAco = null;
            }
        }

        return view('rutas.show', compact(
            'ruta',
            'nivelGeneral',
            'transiciones',
            'puedePublicarse',
            'trayectoDetalle',
            'analisisAco',
            'recomendacionAco',
            'recomendacionDesactualizada',
            'infoDesactualizacion',
            'historialCambioAco'
        ));
    }

    // ─── Editar ───────────────────────────────────────────────

    public function edit(Ruta $ruta): View
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        abort_unless($ruta->puedeSerEditada(), 403,
            'NO TIENE PERMISOS PARA EDITAR ESTA RUTA.');

        $ruta->load('tramos');

        return view('rutas.edit', compact('ruta'));
    }

    public function update(UpdateRutaRequest $request, Ruta $ruta, RutaEstimacionService $estimador): RedirectResponse
    {
        $data = $request->validated();

        $geoPersistida = $this->normalizarGeoDesdeRequest($data['geo_rut'] ?? null);

        if ($geoPersistida) {
            $data['geo_rut'] = $geoPersistida;
            $data['dis_rut'] = $data['dis_rut'] ?? ($geoPersistida['distancia_metros'] ?? null);
            $data['tim_rut'] = $data['tim_rut'] ?? ($geoPersistida['tiempo_minutos'] ?? null);
        }

        $estimacion = $this->estimarSiFaltaDistanciaTiempo($data, $estimador);
        if ($estimacion) {
            $data['dis_rut'] = $estimacion['distancia_metros'];
            $data['tim_rut'] = $estimacion['tiempo_minutos'];
            $data['geo_rut'] = $this->normalizarGeoDesdeEstimacion($estimacion);
        }

        $cambioEstructural = (
            (isset($data['ori_rut']) && $data['ori_rut'] !== $ruta->ori_rut) ||
            (isset($data['dst_rut']) && $data['dst_rut'] !== $ruta->dst_rut) ||
            (isset($data['geo_rut']) && $data['geo_rut'] != $ruta->geo_rut)
        );

        $ruta->update($data);

        if ($cambioEstructural) {
            app(\App\Services\Aco\ServicioEstadoRecomendacionAco::class)->marcarDesactualizadaPorModificacion(
                $ruta,
                'Se modificó el trazado geométrico, origen o destino de la ruta.'
            );
        }

        return redirect()->route('rutas.show', $ruta)
            ->with('success', 'RUTA ACTUALIZADA CORRECTAMENTE.');
    }

    public function estimarTrayecto(Request $request, RutaEstimacionService $estimador): JsonResponse
    {
        abort_unless(Auth::user()?->puedeCrearRuta() || Auth::user()?->puedeEditarRuta(), 403);

        $usaCoordenadas = $request->filled(['origen_lat', 'origen_lng', 'destino_lat', 'destino_lng']);

        if ($usaCoordenadas) {
            $data = $request->validate([
                'origen_lat' => 'required|numeric|between:-90,90',
                'origen_lng' => 'required|numeric|between:-180,180',
                'destino_lat' => 'required|numeric|between:-90,90',
                'destino_lng' => 'required|numeric|between:-180,180',
            ]);

            $estimacion = $estimador->estimarPorCoordenadas(
                (float) $data['origen_lat'],
                (float) $data['origen_lng'],
                (float) $data['destino_lat'],
                (float) $data['destino_lng']
            );
        } else {
            $data = $request->validate([
                'origen' => 'required|string|max:200',
                'destino' => 'required|string|max:200',
            ], [
                'origen.required' => 'EL ORIGEN ES OBLIGATORIO.',
                'destino.required' => 'EL DESTINO ES OBLIGATORIO.',
            ]);

            $estimacion = $estimador->estimar(
                strtoupper(trim($data['origen'])),
                strtoupper(trim($data['destino']))
            );
        }

        if (!$estimacion) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'NO SE PUDO CALCULAR LA RUTA CON EL SERVICIO EXTERNO. VERIFIQUE ORIGEN/DESTINO E INTENTE NUEVAMENTE.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'distancia_metros' => $estimacion['distancia_metros'],
            'tiempo_minutos' => $estimacion['tiempo_minutos'],
            'origen_resuelto' => $estimacion['origen_resuelto'],
            'destino_resuelto' => $estimacion['destino_resuelto'],
            'origen_coords' => $estimacion['origen_coords'] ?? null,
            'destino_coords' => $estimacion['destino_coords'] ?? null,
            'trayecto_coords' => $estimacion['trayecto_coords'] ?? [],
        ]);
    }

    public function buscarPuntosInteres(Request $request, RutaEstimacionService $estimador): JsonResponse
    {
        abort_unless(Auth::user()?->puedeCrearRuta() || Auth::user()?->puedeEditarRuta(), 403);

        $data = $request->validate([
            'termino' => 'required|string|max:120',
        ], [
            'termino.required' => 'EL TÉRMINO DE BÚSQUEDA ES OBLIGATORIO.',
        ]);

        return response()->json([
            'ok' => true,
            'resultados' => $estimador->buscarPuntosInteres($data['termino']),
        ]);
    }

    // ─── Eliminar ─────────────────────────────────────────────

    public function destroy(Ruta $ruta): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEliminarRuta(), 403);

        // Desvincular de los tramos asociados en ruta_calles_tramos
        // (Los tramos físicos en calles_tramos y su reutilización en otras rutas se preservan intactos)
        $ruta->tramos()->detach();

        // 5. Purgar caché y estado ACO de la ruta
        app(\App\Services\Aco\ServicioEstadoRecomendacionAco::class)->limpiarEstado($ruta);

        // 6. Eliminación lógica mediante SoftDeletes
        $ruta->delete();

        return redirect()->route('rutas.index')
            ->with('success', "RUTA {$ruta->ide_rut} ELIMINADA CORRECTAMENTE.");
    }

    // ─── Cambio de estado (flujo de aprobación) ───────────────

    public function cambiarEstado(Request $request, Ruta $ruta): RedirectResponse
    {
        $request->validate([
            'nuevo_estado'  => 'required|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $nuevoEstado  = $request->input('nuevo_estado');
        $transiciones = Ruta::TRANSICIONES[$ruta->est_rut] ?? [];

        if (!in_array($nuevoEstado, $transiciones)) {
            return redirect()->back()
                ->with('error', "TRANSICIÓN DE {$ruta->est_rut} A {$nuevoEstado} NO ESTÁ PERMITIDA.");
        }

        $user = Auth::user();

        // Validar permisos según el estado destino
        if (in_array($nuevoEstado, ['APROBADA', 'ARCHIVADA'])) {
            abort_unless($user?->puedeAprobarRuta(), 403);
        } elseif ($nuevoEstado === 'PUBLICADA') {
            abort_unless($user?->puedePublicarRuta(), 403);

            if (!$ruta->puedePublicarse()) {
                return redirect()->back()
                    ->with('error', 'NO SE PUEDE PUBLICAR: EXISTEN TRAMOS CON NIVEL DE SEGURIDAD INSUFICIENTE O CRÍTICO (RN-03/RN-04).');
            }
        } else {
            // EN REVISIÓN, BORRADOR, DESACTUALIZADA → Técnico o Admin
            abort_unless($user?->puedeEnviarRutaARevision(), 403);
        }

        // Ejecutar la transición
        match ($nuevoEstado) {
            'EN REVISIÓN'    => $ruta->enviarARevision(),
            'BORRADOR'       => $ruta->update(['est_rut' => 'BORRADOR']),
            'APROBADA'       => $ruta->aprobar(Auth::id()),
            'PUBLICADA'      => $ruta->publicar(),
            'DESACTUALIZADA' => $ruta->desactualizar(),
            'ARCHIVADA'      => $ruta->archivar(),
            default          => null,
        };

        // Guardar observaciones si se enviaron
        if ($obs = $request->input('observaciones')) {
            $ruta->update(['obs_rut' => $obs]);
        }

        return redirect()->route('rutas.show', $ruta)
            ->with('success', "ESTADO ACTUALIZADO A {$nuevoEstado} CORRECTAMENTE.");
    }

    private function estimarSiFaltaDistanciaTiempo(array $data, RutaEstimacionService $estimador): ?array
    {
        if (!empty($data['dis_rut']) && !empty($data['tim_rut'])) {
            return null;
        }

        if (empty($data['ori_rut']) || empty($data['dst_rut'])) {
            return null;
        }

        return $estimador->estimar($data['ori_rut'], $data['dst_rut']);
    }

    private function normalizarGeoDesdeRequest(mixed $geo): ?array
    {
        if (!$geo) {
            return null;
        }

        $arr = is_array($geo) ? $geo : json_decode((string) $geo, true);

        if (!is_array($arr)) {
            return null;
        }

        $coords = collect($arr['trayecto_coords'] ?? [])
            ->filter(fn ($p) => is_array($p) && isset($p['lat'], $p['lng']))
            ->map(fn ($p) => [
                'lat' => (float) $p['lat'],
                'lng' => (float) $p['lng'],
            ])
            ->values()
            ->all();

        if (count($coords) < 2) {
            return null;
        }

        return [
            'distancia_metros' => isset($arr['distancia_metros']) ? (float) $arr['distancia_metros'] : null,
            'tiempo_minutos' => isset($arr['tiempo_minutos']) ? (int) $arr['tiempo_minutos'] : null,
            'origen_resuelto' => (string) ($arr['origen_resuelto'] ?? ''),
            'destino_resuelto' => (string) ($arr['destino_resuelto'] ?? ''),
            'origen_coords' => isset($arr['origen_coords']) && is_array($arr['origen_coords']) ? [
                'lat' => (float) ($arr['origen_coords']['lat'] ?? 0),
                'lng' => (float) ($arr['origen_coords']['lng'] ?? 0),
            ] : null,
            'destino_coords' => isset($arr['destino_coords']) && is_array($arr['destino_coords']) ? [
                'lat' => (float) ($arr['destino_coords']['lat'] ?? 0),
                'lng' => (float) ($arr['destino_coords']['lng'] ?? 0),
            ] : null,
            'trayecto_coords' => $coords,
        ];
    }

    private function normalizarGeoDesdeEstimacion(array $estimacion): ?array
    {
        return $this->normalizarGeoDesdeRequest($estimacion);
    }

    /**
     * Recalcula dinámicamente la recomendación bioinspirada ACO para la ruta dada.
     * Retorna una respuesta JSON estructurada para actualización inmediata en la sesión web.
     *
     * @param Request                      $request
     * @param Ruta                         $ruta
     * @param ServicioRecomendacionRutaAco $recomendadorAco
     * @return JsonResponse
     */
    public function recalcularAco(
        Request $request,
        Ruta $ruta,
        ServicioRecomendacionRutaAco $recomendadorAco,
        \App\Services\Aco\ServicioEstadoRecomendacionAco $estadoAcoService
    ): JsonResponse {
        abort_unless(Auth::user()?->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'), 403);

        $ruta->load([
            'tramos.condiciones',
            'tramos.obstaculos.reportes',
            'tramos.obstaculos.reportadoPor',
            'tramos.evaluaciones.nivelSeguridad',
            'tramos.evaluaciones.evaluador',
            'tramos.zonas.nivelSeguridad',
        ]);

        try {
            $config = null;
            if ($request->filled('w_distancia') || $request->filled('w_riesgo') || $request->filled('hormigas')) {
                $wDist = (float) $request->input('w_distancia', 0.5);
                $wRiesgo = (float) $request->input('w_riesgo', 0.5);
                $ants = (int) $request->input('hormigas', 20);
                $iters = (int) $request->input('iteraciones', 25);
                $config = new ConfiguracionAco(
                    ants: max(1, $ants),
                    iterations: max(1, $iters),
                    wDistance: $wDist,
                    wRisk: $wRiesgo
                );
            }

            $resultado = $recomendadorAco->recomendar($ruta, $config);

            if ($resultado->success) {
                $estadoAcoService->guardarRecomendacion($ruta, $resultado);
            }

            $historial = $estadoAcoService->obtenerHistorialCambio($ruta);

            return response()->json([
                'success'          => $resultado->success,
                'mensaje'          => $resultado->mensajeError,
                'recomendacion'    => $resultado->toArray(),
                'historial_cambio' => $historial,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error durante el recálculo de recomendación ACO: ' . $e->getMessage(),
            ], 500);
        }
    }
}
