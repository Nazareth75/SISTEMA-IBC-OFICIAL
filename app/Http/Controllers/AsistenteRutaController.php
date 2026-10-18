<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Services\AsistenteRutaService;
use Illuminate\Http\JsonResponse;

class AsistenteRutaController extends Controller
{
    public function show(Ruta $ruta, AsistenteRutaService $asistente): JsonResponse
    {
        $ruta->load(['tramos.obstaculos']);
        $resultado = $asistente->obtener($ruta);
        return response()->json($resultado, $resultado['success'] ? 200 : 422);
    }
}
