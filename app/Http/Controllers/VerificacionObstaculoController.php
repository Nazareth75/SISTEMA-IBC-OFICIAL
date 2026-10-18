<?php

namespace App\Http\Controllers;

use App\Models\Obstaculo;
use App\Models\VerificacionObstaculo;
use App\Services\Aco\ServicioConsensoVerificacionObstaculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controlador de verificación colaborativa ciudadana y bandeja de decisión técnica.
 *
 * Responsabilidad ingenieril:
 * - Proporciona interfaces accesibles e inclusivas para voluntarios en terreno.
 * - Centraliza la bandeja de decisión para ingenieros y técnicos autorizados.
 * - Ejecuta las transiciones críticas de resolución bajo transacciones de base de datos seguras.
 */
class VerificacionObstaculoController extends Controller
{
    // ─── 1. Pantalla del Voluntario: Formulario accesible de verificación ───

    public function create(Obstaculo $obstaculo, ServicioConsensoVerificacionObstaculo $consensoService): View
    {
        $user = Auth::user();
        abort_unless($user && $user->puedeVerificarObstaculos(), 403, 'NO TIENE PERMISOS PARA VERIFICAR OBSTÁCULOS.');

        if ($obstaculo->est_obs !== 'ACTIVO') {
            abort(422, 'SOLO SE PUEDEN VERIFICAR OBSTÁCULOS EN ESTADO ACTIVO.');
        }

        $obstaculo->load(['tramo.rutas', 'verificaciones.usuario']);
        $consenso = $consensoService->evaluarConsenso($obstaculo);

        return view('obstaculos.verificar', compact('obstaculo', 'consenso'));
    }

    // ─── 2. Registro de la Verificación del Voluntario ───────────────────────

    public function store(Request $request, Obstaculo $obstaculo, ServicioConsensoVerificacionObstaculo $consensoService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->puedeVerificarObstaculos(), 403, 'NO TIENE PERMISOS PARA VERIFICAR OBSTÁCULOS.');

        if ($obstaculo->est_obs !== 'ACTIVO') {
            abort(422, 'NO SE PUEDE VERIFICAR UN OBSTÁCULO QUE NO ESTÁ ACTIVO.');
        }

        $data = $request->validate([
            'respuesta'     => 'required|in:' . implode(',', array_keys(VerificacionObstaculo::RESPUESTAS)),
            'observacion'   => 'nullable|string|max:500',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'latitud'       => 'nullable|numeric|between:-90,90',
            'longitud'      => 'nullable|numeric|between:-180,180',
            'precision_gps' => 'nullable|numeric|min:0',
        ], [
            'respuesta.required' => 'DEBE INDICAR SI EL OBSTÁCULO CONTINÚA PRESENTE O NO.',
            'respuesta.in'       => 'LA RESPUESTA SELECCIONADA NO ES VÁLIDA.',
            'observacion.max'    => 'LA OBSERVACIÓN NO PUEDE SUPERAR LOS 500 CARACTERES.',
            'foto.image'         => 'EL ARCHIVO ADJUNTO DEBE SER UNA IMAGEN.',
            'foto.mimes'         => 'FORMATOS PERMITIDOS: JPG, JPEG, PNG, WEBP.',
            'foto.max'           => 'LA FOTO NO DEBE EXCEDER 2 MB.',
            'latitud.between'    => 'LA LATITUD DEBE ESTAR ENTRE -90 Y 90.',
            'longitud.between'   => 'LA LONGITUD DEBE ESTAR ENTRE -180 Y 180.',
            'precision_gps.min'  => 'LA PRECISIÓN GPS NO PUEDE SER NEGATIVA.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('uploads/verificaciones', 'public');
        }

        VerificacionObstaculo::create([
            'cod_obstaculo' => $obstaculo->getKey(),
            'cod_usuario'   => $user->getKey(),
            'respuesta'     => $data['respuesta'],
            'latitud'       => $data['latitud'] ?? null,
            'longitud'      => $data['longitud'] ?? null,
            'precision_gps' => $data['precision_gps'] ?? null,
            'foto'          => $fotoPath,
            'observacion'   => !empty($data['observacion']) ? trim($data['observacion']) : null,
            'canal'         => 'WEB',
        ]);

        // Evaluar consenso para retroalimentación
        $consenso = $consensoService->evaluarConsenso($obstaculo);

        $mensajeExito = match($data['respuesta']) {
            'YA_NO_ESTA' => '¡GRACIAS POR TU REPORTE! Has verificado que el obstáculo ya no está.',
            'SIGUE_PRESENTE' => 'VERIFICACIÓN REGISTRADA: Se mantiene la alerta activa sobre este obstáculo.',
            default => 'VERIFICACIÓN REGISTRADA: Tu observación ha sido guardada.',
        };

        if ($consenso['es_candidato_resolucion']) {
            $mensajeExito .= ' Se ha alcanzado consenso comunitario para que el equipo técnico certifique la resolución.';
        }

        // Redirigir de regreso a la ruta asociada si existe o a la pantalla de obstáculo
        $ruta = $obstaculo->tramo?->rutas?->first();
        if ($ruta) {
            return redirect()->route('rutas.show', $ruta)->with('success', $mensajeExito);
        }

        return redirect()->route('obstaculos.index')->with('success', $mensajeExito);
    }

    // ─── 3. Bandeja Técnica: Verificaciones Pendientes de Decisión ──────────

    public function bandejaTecnico(Request $request, ServicioConsensoVerificacionObstaculo $consensoService): View
    {
        $user = Auth::user();
        abort_unless($user && $user->puedeGestionarObstaculos(), 403, 'SOLO EL PERSONAL TÉCNICO Y ADMINISTRATIVO PUEDE ACCEDER A LA BANDEJA.');

        $obstaculosPendientes = $consensoService->obtenerObstaculosParaBandejaTecnica();

        return view('obstaculos.bandeja_verificaciones', [
            'pendientes' => $obstaculosPendientes,
        ]);
    }

    // ─── 4. Confirmación Técnica: Transición Segura ACTIVO → RESUELTO ───────

    public function confirmarResolucion(Obstaculo $obstaculo): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->puedeGestionarObstaculos(), 403, 'NO TIENE PERMISOS PARA RESOLVER OBSTÁCULOS.');

        if ($obstaculo->est_obs === 'RESUELTO') {
            return redirect()->back()->with('error', 'EL OBSTÁCULO YA FUE MARCADO PREVIAMENTE COMO RESUELTO.');
        }

        if ($obstaculo->est_obs !== 'ACTIVO') {
            abort(422, 'SOLO SE PUEDEN RESOLVER OBSTÁCULOS EN ESTADO ACTIVO.');
        }

        DB::transaction(function () use ($obstaculo) {
            $obstaculo->resolver();
        });

        return redirect()->back()->with('success', "OBSTÁCULO #{$obstaculo->getKey()} ({$obstaculo->tip_obs}) CONFIRMADO COMO RESUELTO. El riesgo vial ha sido actualizado y las rutas asociadas han sido notificadas para recálculo ACO.");
    }

    // ─── 5. Descarte de Resolución Técnica: Mantener como ACTIVO ────────────

    public function mantenerActivo(Obstaculo $obstaculo): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->puedeGestionarObstaculos(), 403, 'NO TIENE PERMISOS PARA GESTIONAR ESTE OBSTÁCULO.');

        return redirect()->back()->with('info', "EL OBSTÁCULO #{$obstaculo->getKey()} SE MANTIENE EN ESTADO ACTIVO POR DECISIÓN TÉCNICA.");
    }
}
