<?php

namespace App\Events;

use App\Models\Obstaculo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de dominio disparado cuando un obstáculo experimenta una transición de estado de ciclo de vida.
 *
 * Responsabilidad ingenieril:
 * Notifica a los subsistemas interesados (en particular el motor de recomendación ACO)
 * que las condiciones de transitabilidad de uno o más tramos han cambiado,
 * permitiendo la invalidación y recálculo sin acoplar el modelo Obstaculo con ACO.
 */
class ObstaculoCambioEstado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $fecha;

    public function __construct(
        public Obstaculo $obstaculo,
        public string $estadoAnterior,
        public string $estadoNuevo,
        public ?int $codUsuario = null
    ) {
        $this->fecha = now()->format('Y-m-d H:i:s');
    }
}
