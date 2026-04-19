<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaNoVidenteController;
use App\Http\Controllers\TutorContactoController;
use App\Http\Controllers\AfiliacionController;


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
});


require __DIR__.'/auth.php';
