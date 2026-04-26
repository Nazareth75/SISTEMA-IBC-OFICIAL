<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRutaRequest;
use App\Http\Requests\UpdateRutaRequest;
use App\Models\NivelSeguridad;
use App\Models\Ruta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RutaController extends Controller
{
    // ─── Listado ──────────────────────────────────────────────

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $estado = trim((string) $request->get('estado', ''));

        $rutas = Ruta::activas()
            ->with(['creador', 'aprobador'])
            ->buscador($buscar)
            ->when($estado !== '', fn ($q) => $q->porEstado($estado))
            ->orderByDesc('cod_rutas')
            ->paginate(10)
            ->withQueryString();

        return view('rutas.index', [
            'rutas'   => $rutas,
            'filtros' => compact('buscar', 'estado'),
            'estados' => array_keys(Ruta::ESTADOS),
        ]);
    }

    // ─── Crear ────────────────────────────────────────────────

    public function create(): View
    {
        abort_unless(Auth::user()?->puedeCrearRuta(), 403);

        return view('rutas.create');
    }

    public function store(StoreRutaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['ide_rut']              = Ruta::generarCodigo();
        $data['est_rut']              = 'BORRADOR';
        $data['cod_usuarios_rut_cre'] = Auth::id();

        $ruta = Ruta::create($data);

        return redirect()->route('rutas.show', $ruta)
            ->with('success', "RUTA {$ruta->ide_rut} CREADA CORRECTAMENTE.");
    }

    // ─── Detalle ──────────────────────────────────────────────

    public function show(Ruta $ruta): View
    {
        $ruta->load([
            'creador',
            'aprobador',
            'tramos.condiciones',
            'tramos.obstaculos.reportadoPor',
            'tramos.evaluaciones.nivelSeguridad',
            'tramos.evaluaciones.evaluador',
            'tramos.zonas.nivelSeguridad',
        ]);

        $nivelGeneral    = $ruta->nivelSeguridadGeneral();
        $transiciones    = $ruta->transicionesPosibles();
        $puedePublicarse = ($ruta->est_rut === 'APROBADA') ? $ruta->puedePublicarse() : null;

        return view('rutas.show', compact('ruta', 'nivelGeneral', 'transiciones', 'puedePublicarse'));
    }

    // ─── Editar ───────────────────────────────────────────────

    public function edit(Ruta $ruta): View
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        abort_unless(in_array($ruta->est_rut, ['BORRADOR', 'DESACTUALIZADA']), 403,
            'SOLO SE PUEDEN EDITAR RUTAS EN ESTADO BORRADOR O DESACTUALIZADA.');

        $ruta->load('tramos');

        return view('rutas.edit', compact('ruta'));
    }

    public function update(UpdateRutaRequest $request, Ruta $ruta): RedirectResponse
    {
        $ruta->update($request->validated());

        return redirect()->route('rutas.show', $ruta)
            ->with('success', 'RUTA ACTUALIZADA CORRECTAMENTE.');
    }

    // ─── Eliminar ─────────────────────────────────────────────

    public function destroy(Ruta $ruta): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEliminarRuta(), 403);

        if ($ruta->tramos()->exists()) {
            return redirect()->back()
                ->with('error', 'NO SE PUEDE ELIMINAR UNA RUTA CON TRAMOS REGISTRADOS.');
        }

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
}
