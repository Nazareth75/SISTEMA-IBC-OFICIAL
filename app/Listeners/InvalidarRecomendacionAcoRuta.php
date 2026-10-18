<?php

namespace App\Listeners;

use App\Events\ObstaculoCambioEstado;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Escucha la transición de estado de cualquier obstáculo vial e invalida
 * de forma reactiva la recomendación ACO de todas las rutas peatonales asociadas al tramo.
 *
 * Responsabilidad ingenieril:
 * Asegura desacoplamiento de eventos de dominio según la arquitectura orientada a eventos.
 */
class InvalidarRecomendacionAcoRuta
{
    public function __construct(
        private ServicioEstadoRecomendacionAco $servicioEstado
    ) {}

    /**
     * Procesa el evento de mutación de obstáculo.
     */
    public function handle(ObstaculoCambioEstado $event): void
    {
        $obstaculo = $event->obstaculo;
        $tramo     = $obstaculo->tramo;

        if (!$tramo) {
            return;
        }

        // Obtener todas las rutas vinculadas a través de la relación Many-to-Many
        $rutas = $tramo->rutas;

        // Soporte fallback si existiera relación legacy
        if ($rutas->isEmpty() && $tramo->ruta) {
            $rutas = collect([$tramo->ruta]);
        }

        foreach ($rutas as $ruta) {
            $this->servicioEstado->marcarDesactualizada($ruta, $event);
            Log::info("Recomendación ACO de ruta {$ruta->ide_rut} marcada como desactualizada debido al obstáculo {$obstaculo->cod_obstaculos} ({$event->estadoAnterior} -> {$event->estadoNuevo}).");
        }
    }
}
