<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaNoVidenteController;
use App\Http\Controllers\TutorContactoController;
use App\Http\Controllers\AfiliacionController;
use App\Http\Controllers\BeneficioController;
use App\Http\Controllers\AsignacionBeneficioController;


Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
});


require __DIR__.'/auth.php';
