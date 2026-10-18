<?php

use App\Http\Controllers\MovilidadController;
use Illuminate\Support\Facades\Route;

Route::post('movilidad/login', [MovilidadController::class, 'login'])->middleware('throttle:5,1');
Route::prefix('movilidad')->middleware(['movilidad', 'throttle:60,1'])->group(function () {
    Route::get('sesion', [MovilidadController::class, 'sesion']);
    Route::get('rutas', [MovilidadController::class, 'index']);
    Route::get('rutas/2147483647', [MovilidadController::class, 'previaMerced']);
    Route::get('rutas/{ruta}', [MovilidadController::class, 'show']);
    Route::post('logout', [MovilidadController::class, 'logout']);
    Route::post('eventos', [MovilidadController::class, 'evento']);
});
