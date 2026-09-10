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
use App\Http\Controllers\ReporteObstaculoController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\BackupController;



Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->tieneRol('ADMINISTRADOR_NO_VIDENTE')) {
            return view('dashboard.admin_no_vidente');
        }

        if ($user->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR')) {
            return view('dashboard.admin');
        }

        if ($user->tieneRol('TECNICO')) {
            $statsTecnico = [
                'rutasValidar' => \App\Models\Ruta::whereIn('est_rut', ['BORRADOR', 'REVISION'])->count(),
                'rutasHoy' => \App\Models\Ruta::whereIn('est_rut', ['BORRADOR', 'REVISION'])->whereDate('fcr_rut', today())->count(),
                'reportesPendientes' => \App\Models\ReporteObstaculo::where('estado', 'REPORTADO')->count(),
                'obstaculosCriticos' => \App\Models\ReporteObstaculo::where('nivel_riesgo', 'CRITICO')->whereIn('estado', ['REPORTADO', 'ACTIVO'])->count(),
                'reportesCriticosList' => \App\Models\ReporteObstaculo::where('nivel_riesgo', 'CRITICO')
                                                ->whereIn('estado', ['REPORTADO', 'ACTIVO'])
                                                ->latest()
                                                ->take(2)
                                                ->get()
            ];
            return view('dashboard.tecnico', compact('statsTecnico'));
        }

        if ($user->tieneRol('VOLUNTARIO')) {
            return view('dashboard.voluntario');
        }

        abort(403);
    })
        ->middleware(['auth'])
        ->name('dashboard');

    Route::get('/api/dashboard/estadisticas', [DashboardController::class, 'estadisticas'])
        ->middleware(['verified'])
        ->name('dashboard.estadisticas');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:SUPERADMINISTRADOR,ADMINISTRADOR'])->group(function () {
        // API Helpers para Asistente de Voz
        Route::get('api/asistente/beneficios-activos', function () {
            $beneficios = \App\Models\BeneficioSocial::where('est_bso', 'ACTIVO')
                ->whereNull('fbj_bso')
                ->get(['nom_bso', 'des_bso']);
            return response()->json($beneficios);
        })->name('api.asistente.beneficios-activos');

        Route::get('api/asistente/afiliaciones-vencidas', function () {
            $vencidas = \App\Models\Afiliacion::where(function($q) {
                    $q->where('est_afi', 'VENCIDA')
                      ->orWhere('fve_afi', '<', today());
                })
                ->whereNull('fbj_afi')
                ->with('persona')
                ->limit(10)
                ->get();

            return response()->json($vencidas->map(function ($a) {
                return [
                    'id' => $a->cod_afiliaciones,
                    'nombre' => $a->persona ? "{$a->persona->nom_pnv} {$a->persona->ape_pnv}" : 'Sin nombre',
                    'ci' => $a->persona ? $a->persona->cii_pnv : '',
                    'fecha_vencimiento' => $a->fve_afi ? $a->fve_afi->format('Y-m-d') : 'Sin fecha',
                ];
            }));
        })->name('api.asistente.afiliaciones-vencidas');

        Route::get('api/asistente/obstaculos-criticos', function () {
            $criticos = \App\Models\ReporteObstaculo::where('nivel_riesgo', 'CRITICO')
                ->whereIn('estado', ['REPORTADO', 'ACTIVO'])
                ->limit(10)
                ->get();
            return response()->json($criticos);
        })->name('api.asistente.obstaculos-criticos');

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

        Route::resource('personas-no-videntes', PersonaNoVidenteController::class)
            ->parameters([
                'personas-no-videntes' => 'personaNoVidente',
            ]);

        Route::get('personas-no-videntes/{personaNoVidente}/tutores/documento-base', [TutorContactoController::class, 'descargarDocumentoBase'])
            ->name('personas-no-videntes.tutores.documento-base');

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

        Route::resource('instituciones-externas', InstitucionExternaController::class)
            ->parameters([
                'instituciones-externas' => 'institucionExterna',
            ]);

        Route::patch('instituciones-externas/{institucionExterna}/toggle', [InstitucionExternaController::class, 'toggle'])
            ->name('instituciones-externas.toggle');

        Route::resource('tramites-instituciones', TramiteInstitucionController::class)
            ->parameters([
                'tramites-instituciones' => 'tramiteInstitucion',
            ]);

        Route::get('api/afiliados-tramites', [TramiteInstitucionController::class, 'buscarAfiliados'])
            ->name('afiliados.tramites');

        Route::post('tramites-instituciones/{tramiteInstitucion}/cambiar-estado', [TramiteInstitucionController::class, 'cambiarEstado'])
            ->name('tramites-instituciones.cambiar-estado');

        Route::resource('accesos-salud', AccesoSaludController::class)
            ->parameters([
                'accesos-salud' => 'accesoSalud',
            ]);

        Route::get('api/afiliados-accesos', [AccesoSaludController::class, 'buscarAfiliados'])
            ->name('afiliados.accesos');

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

        Route::get('niveles-seguridad', [NivelSeguridadController::class, 'index'])
            ->name('niveles-seguridad.index');
        Route::get('niveles-seguridad/{nivelSeguridad}/edit', [NivelSeguridadController::class, 'edit'])
            ->name('niveles-seguridad.edit');
        Route::put('niveles-seguridad/{nivelSeguridad}', [NivelSeguridadController::class, 'update'])
            ->name('niveles-seguridad.update');

        // Logs del sistema
        Route::get('admin/logs', [LogController::class, 'index'])->name('admin.logs.index');
        Route::get('admin/logs/descargar', [LogController::class, 'download'])->name('admin.logs.download');
        Route::post('admin/logs/limpiar', [LogController::class, 'clear'])->name('admin.logs.clear');

        // Copias de seguridad (Backups)
        Route::get('admin/backups', [BackupController::class, 'index'])->name('admin.backups.index');
        Route::post('admin/backups/crear', [BackupController::class, 'store'])->name('admin.backups.store');
        Route::get('admin/backups/descargar/{filename}', [BackupController::class, 'download'])->name('admin.backups.download');
        Route::post('admin/backups/restaurar/{filename}', [BackupController::class, 'restore'])->name('admin.backups.restore');
        Route::delete('admin/backups/eliminar/{filename}', [BackupController::class, 'destroy'])->name('admin.backups.destroy');
    });

    Route::middleware(['role:SUPERADMINISTRADOR,ADMINISTRADOR,TECNICO'])->group(function () {
        Route::resource('rutas', RutaController::class)
            ->parameters(['rutas' => 'ruta']);

        Route::post('api/rutas/estimar-trayecto', [RutaController::class, 'estimarTrayecto'])
            ->name('rutas.estimar-trayecto');

        Route::get('api/rutas/puntos-interes', [RutaController::class, 'buscarPuntosInteres'])
            ->name('rutas.puntos-interes');

        Route::post('rutas/{ruta}/cambiar-estado', [RutaController::class, 'cambiarEstado'])
            ->name('rutas.cambiar-estado');

        Route::post('rutas/{ruta}/tramos', [TramoController::class, 'store'])
            ->name('rutas.tramos.store');
        Route::patch('rutas/{ruta}/tramos/reordenar', [TramoController::class, 'reordenar'])
            ->name('rutas.tramos.reordenar');
        Route::put('rutas/{ruta}/tramos/{tramo}', [TramoController::class, 'update'])
            ->name('rutas.tramos.update');
        Route::delete('rutas/{ruta}/tramos/{tramo}', [TramoController::class, 'destroy'])
            ->name('rutas.tramos.destroy');

        Route::post('tramos/{tramo}/condiciones', [CondicionEntornoController::class, 'store'])
            ->name('tramos.condiciones.store');
        Route::delete('tramos/{tramo}/condiciones/{condicion}', [CondicionEntornoController::class, 'destroy'])
            ->name('tramos.condiciones.destroy');

        Route::post('tramos/{tramo}/evaluaciones', [EvaluacionRiesgoController::class, 'store'])
            ->name('tramos.evaluaciones.store');
        Route::delete('tramos/{tramo}/evaluaciones/{evaluacion}', [EvaluacionRiesgoController::class, 'destroy'])
            ->name('tramos.evaluaciones.destroy');

        Route::post('tramos/{tramo}/zonas', [ZonaRiesgoController::class, 'store'])
            ->name('tramos.zonas.store');
        Route::delete('tramos/{tramo}/zonas/{zona}', [ZonaRiesgoController::class, 'destroy'])
            ->name('tramos.zonas.destroy');

        Route::get('obstaculos', [ObstaculoController::class, 'index'])
            ->name('obstaculos.index');
        Route::patch('obstaculos/{obstaculo}/confirmar', [ObstaculoController::class, 'confirmar'])
            ->name('obstaculos.confirmar');
        Route::patch('obstaculos/{obstaculo}/resolver', [ObstaculoController::class, 'resolver'])
            ->name('obstaculos.resolver');
        Route::delete('obstaculos/{obstaculo}', [ObstaculoController::class, 'rechazar'])
            ->name('obstaculos.rechazar');

        Route::get('reportes-obstaculos', [ReporteObstaculoController::class, 'index'])
            ->name('reportes-obstaculos.index');
        Route::get('reportes-obstaculos/{reporte}', [ReporteObstaculoController::class, 'show'])
            ->name('reportes-obstaculos.show')
            ->where('reporte', '[0-9]+');
        Route::patch('reportes-obstaculos/{reporte}/validar', [ReporteObstaculoController::class, 'validar'])
            ->name('reportes-obstaculos.validar')
            ->where('reporte', '[0-9]+');
        Route::patch('reportes-obstaculos/{reporte}/descartar', [ReporteObstaculoController::class, 'descartar'])
            ->name('reportes-obstaculos.descartar')
            ->where('reporte', '[0-9]+');
        Route::patch('reportes-obstaculos/{reporte}/resolver', [ReporteObstaculoController::class, 'resolver'])
            ->name('reportes-obstaculos.resolver')
            ->where('reporte', '[0-9]+');
    });

    Route::middleware(['role:SUPERADMINISTRADOR,ADMINISTRADOR,TECNICO,VOLUNTARIO'])->group(function () {
        Route::post('tramos/{tramo}/obstaculos', [ObstaculoController::class, 'store'])
            ->name('tramos.obstaculos.store');

        Route::get('reportes-obstaculos/create', [ReporteObstaculoController::class, 'create'])
            ->name('reportes-obstaculos.create');
        Route::post('reportes-obstaculos', [ReporteObstaculoController::class, 'store'])
            ->name('reportes-obstaculos.store');
        Route::get('mis-reportes-obstaculos', [ReporteObstaculoController::class, 'misReportes'])
            ->name('reportes-obstaculos.mis-reportes');
        Route::get('mapa-monitoreo', [ReporteObstaculoController::class, 'mapaMonitoreo'])
            ->name('reportes-obstaculos.mapa-monitoreo');
    });
});


require __DIR__.'/auth.php';
