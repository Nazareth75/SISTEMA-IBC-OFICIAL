<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonaNoVidenteController;
use App\Http\Controllers\TutorContactoController;
use App\Http\Controllers\AfiliacionController;
use App\Http\Controllers\BeneficioController;
use App\Http\Controllers\AsignacionBeneficioController;
use App\Http\Controllers\InstitucionExternaController;
use App\Http\Controllers\TramiteInstitucionController;
use App\Http\Controllers\AccesoSaludController;
use App\Http\Controllers\TipoCapacitacionController;
use App\Http\Controllers\CapacitacionController;
use App\Http\Controllers\ParticipacionCapacitacionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\TramoController;
use App\Http\Controllers\CondicionEntornoController;
use App\Http\Controllers\EvaluacionRiesgoController;
use App\Http\Controllers\ZonaRiesgoController;
use App\Http\Controllers\ObstaculoController;
use App\Http\Controllers\NivelSeguridadController;



Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/api/dashboard/estadisticas', [DashboardController::class, 'estadisticas'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.estadisticas');

Route::middleware('auth')->group(function () {

    Route::post(
        'capacitaciones/{capacitacion}/participaciones',
        [ParticipacionCapacitacionController::class, 'store']
    )->name('capacitaciones.participaciones.store');

    Route::put(
        'capacitaciones/{capacitacion}/participaciones/{participacion}',
        [ParticipacionCapacitacionController::class, 'update']
    )->name('capacitaciones.participaciones.update');

    Route::delete(
        'capacitaciones/{capacitacion}/participaciones/{participacion}',
        [ParticipacionCapacitacionController::class, 'destroy']
    )->name('capacitaciones.participaciones.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('personas-no-videntes', PersonaNoVidenteController::class)
        ->parameters([
            'personas-no-videntes' => 'personaNoVidente',
        ]);


        Route::resource('personas-no-videntes.tutores', TutorContactoController::class)
    ->except(['show'])
    ->parameters([
        'personas-no-videntes' => 'personaNoVidente',
        'tutores' => 'tutore',
    ]);

        Route::resource('afiliaciones', AfiliacionController::class)
    ->parameters([
        'afiliaciones' => 'afiliacione',
    ]);

    Route::get('api/afiliaciones/buscar-personas', [AfiliacionController::class, 'buscarPersonasSinAfiliacion'])
        ->name('afiliaciones.buscar-personas');

    Route::resource('beneficios', BeneficioController::class)
        ->parameters([
            'beneficios' => 'beneficio',
        ]);

    Route::patch('beneficios/{beneficio}/toggle', [BeneficioController::class, 'toggle'])
        ->name('beneficios.toggle');

    Route::resource('asignacion-beneficios', AsignacionBeneficioController::class)
        ->parameters([
            'asignacion-beneficios' => 'asignacionBeneficio',
        ]);

    Route::get('api/afiliados-buscar', [AsignacionBeneficioController::class, 'buscarAfiliados'])
        ->name('afiliados.buscar');

    // Ruta de debug para verificar afiliados activos
    Route::get('api/afiliados-debug', function () {
        $afiliados = \App\Models\Afiliacion::activas()->with('persona')->limit(10)->get();
        return response()->json([
            'total' => $afiliados->count(),
            'afiliados' => $afiliados->map(function ($a) {
                return [
                    'id' => $a->cod_afiliaciones,
                    'nombre' => $a->persona?->nom_pnv,
                    'apellido' => $a->persona?->ape_pnv,
                    'ci' => $a->persona?->cii_pnv,
                    'estado' => $a->est_afi,
                ];
            }),
        ]);
    })->name('afiliados.debug');

    /**
     * MÓDULO 5: Trámites en Instituciones Externas
     */

    // Instituciones Externas
    Route::resource('instituciones-externas', InstitucionExternaController::class)
        ->parameters([
            'instituciones-externas' => 'institucionExterna',
        ]);

    Route::patch('instituciones-externas/{institucionExterna}/toggle', [InstitucionExternaController::class, 'toggle'])
        ->name('instituciones-externas.toggle');

    // Trámites Instituciones
    Route::resource('tramites-instituciones', TramiteInstitucionController::class)
        ->parameters([
            'tramites-instituciones' => 'tramiteInstitucion',
        ]);

    Route::get('api/afiliados-tramites', [TramiteInstitucionController::class, 'buscarAfiliados'])
        ->name('afiliados.tramites');

    Route::post('tramites-instituciones/{tramiteInstitucion}/cambiar-estado', [TramiteInstitucionController::class, 'cambiarEstado'])
        ->name('tramites-instituciones.cambiar-estado');

    // Accesos Salud
    Route::resource('accesos-salud', AccesoSaludController::class)
        ->parameters([
            'accesos-salud' => 'accesoSalud',
        ]);

    Route::get('api/afiliados-accesos', [AccesoSaludController::class, 'buscarAfiliados'])
        ->name('afiliados.accesos');

    // =====================================================
    // MÓDULO 6 — Capacitaciones
    // =====================================================

    Route::resource('tipos-capacitacion', TipoCapacitacionController::class)
    ->parameters(['tipos-capacitacion' => 'tipoCapacitacion']);

    Route::patch('tipos-capacitacion/{tipoCapacitacion}/toggle', [TipoCapacitacionController::class, 'toggle'])
        ->name('tipos-capacitacion.toggle');

    Route::resource('capacitaciones', CapacitacionController::class)
        ->parameters(['capacitaciones' => 'capacitacion']);

    Route::post('capacitaciones/{capacitacion}/cambiar-estado', [CapacitacionController::class, 'cambiarEstado'])
        ->name('capacitaciones.cambiar-estado');

    Route::get('api/personas-buscar', [ParticipacionCapacitacionController::class, 'buscarPersonas'])
        ->name('personas.buscar');

    // =====================================================
    // MÓDULO 7 — Rutas Seguras
    // =====================================================

    Route::resource('rutas', RutaController::class)
        ->parameters(['rutas' => 'ruta']);

    Route::post('api/rutas/estimar-trayecto', [RutaController::class, 'estimarTrayecto'])
        ->name('rutas.estimar-trayecto');

    Route::get('api/rutas/puntos-interes', [RutaController::class, 'buscarPuntosInteres'])
        ->name('rutas.puntos-interes');

    Route::post('rutas/{ruta}/cambiar-estado', [RutaController::class, 'cambiarEstado'])
        ->name('rutas.cambiar-estado');

    // Tramos (nested bajo rutas)
    Route::post('rutas/{ruta}/tramos', [TramoController::class, 'store'])
        ->name('rutas.tramos.store');
    Route::patch('rutas/{ruta}/tramos/reordenar', [TramoController::class, 'reordenar'])
        ->name('rutas.tramos.reordenar');
    Route::put('rutas/{ruta}/tramos/{tramo}', [TramoController::class, 'update'])
        ->name('rutas.tramos.update');
    Route::delete('rutas/{ruta}/tramos/{tramo}', [TramoController::class, 'destroy'])
        ->name('rutas.tramos.destroy');

    // Condiciones de entorno (nested bajo tramos)
    Route::post('tramos/{tramo}/condiciones', [CondicionEntornoController::class, 'store'])
        ->name('tramos.condiciones.store');
    Route::delete('tramos/{tramo}/condiciones/{condicion}', [CondicionEntornoController::class, 'destroy'])
        ->name('tramos.condiciones.destroy');

    // Evaluaciones de riesgo (nested bajo tramos)
    Route::post('tramos/{tramo}/evaluaciones', [EvaluacionRiesgoController::class, 'store'])
        ->name('tramos.evaluaciones.store');
    Route::delete('tramos/{tramo}/evaluaciones/{evaluacion}', [EvaluacionRiesgoController::class, 'destroy'])
        ->name('tramos.evaluaciones.destroy');

    // Zonas de riesgo (nested bajo tramos)
    Route::post('tramos/{tramo}/zonas', [ZonaRiesgoController::class, 'store'])
        ->name('tramos.zonas.store');
    Route::delete('tramos/{tramo}/zonas/{zona}', [ZonaRiesgoController::class, 'destroy'])
        ->name('tramos.zonas.destroy');

    // Obstáculos — reporte y flujo de validación técnico
    Route::get('obstaculos', [ObstaculoController::class, 'index'])
        ->name('obstaculos.index');
    Route::post('tramos/{tramo}/obstaculos', [ObstaculoController::class, 'store'])
        ->name('tramos.obstaculos.store');
    Route::patch('obstaculos/{obstaculo}/confirmar', [ObstaculoController::class, 'confirmar'])
        ->name('obstaculos.confirmar');
    Route::patch('obstaculos/{obstaculo}/resolver', [ObstaculoController::class, 'resolver'])
        ->name('obstaculos.resolver');
    Route::delete('obstaculos/{obstaculo}', [ObstaculoController::class, 'rechazar'])
        ->name('obstaculos.rechazar');

    // Niveles de seguridad — catálogo (Pantalla 5)
    Route::get('niveles-seguridad', [NivelSeguridadController::class, 'index'])
        ->name('niveles-seguridad.index');
    Route::get('niveles-seguridad/{nivelSeguridad}/edit', [NivelSeguridadController::class, 'edit'])
        ->name('niveles-seguridad.edit');
    Route::put('niveles-seguridad/{nivelSeguridad}', [NivelSeguridadController::class, 'update'])
        ->name('niveles-seguridad.update');
    });


require __DIR__.'/auth.php';
