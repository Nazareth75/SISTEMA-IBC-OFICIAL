<?php

namespace App\Services\Aco;

/**
 * Objeto de Transferencia de Datos (DTO) que encapsula el resultado enriquecido de la recomendación ACO.
 *
 * Responsabilidad ingenieril:
 * Empaqueta la solución optimizada por la colonia de hormigas junto con su contraste
 * contra la línea base determinista (Dijkstra), las métricas M1 a M8, el desglose
 * espacial para renderizado cartográfico en Leaflet y la justificación explicativa para el administrador.
 */
class ResultadoRecomendacionAco
{
    public function __construct(
        public bool $success = false,
        public ?string $mensajeError = null,
        public ?int $rutaId = null,
        public ?string $rutaCodigo = null,
        public ?string $rutaNombre = null,
        public array $nodosSeleccionados = [],
        public array $tramosSeleccionadosIds = [],
        public array $tramosSeleccionadosNombres = [],
        public float $distanciaTotal = 0.0,
        public float $riesgoTotal = 0.0,
        public float $costoTotal = 0.0,
        public float $tiempoEjecucionMs = 0.0,
        public int $bestFoundIteration = 0,
        public int $convergenceIteration = 0,
        public float $costoReferencia = 0.0,
        public ?float $gap = null,
        public array $caminoMenorDistanciaNodos = [],
        public array $caminoMenorDistanciaTramosIds = [],
        public float $distanciaCaminoCorto = 0.0,
        public float $riesgoCaminoCorto = 0.0,
        public ?float $m7 = null, // Reducción de riesgo (%)
        public ?float $m8 = null, // Incremento de distancia (%)
        public int $cantidadAlternativas = 0,
        public string $motivoRecomendacion = '',
        public string $nivelRiesgoEstimado = 'BAJO',
        public int $obstaculosActivosCount = 0,
        public int $obstaculosResueltosCount = 0,
        public array $obstaculosDetalle = [],
        public ?string $fechaCalculo = null,
        public array $geometriaRecomendada = [],
        public array $geometriaMasCorta = [],
        public array $geometriaAlternativas = [],
        public array $bestCostHistory = [],
        public array $metricas = []
    ) {
        $this->fechaCalculo = $this->fechaCalculo ?? now()->format('Y-m-d H:i:s');
    }

    /**
     * Serializa el DTO a un arreglo estructurado apto para respuestas JSON de actualización inmediata.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success'                        => $this->success,
            'mensaje_error'                  => $this->mensajeError,
            'ruta_id'                        => $this->rutaId,
            'ruta_codigo'                    => $this->rutaCodigo,
            'ruta_nombre'                    => $this->rutaNombre,
            'nodos_seleccionados'            => $this->nodosSeleccionados,
            'tramos_seleccionados_ids'       => $this->tramosSeleccionadosIds,
            'tramos_seleccionados_nombres'   => $this->tramosSeleccionadosNombres,
            'distancia_total'                => round($this->distanciaTotal, 1),
            'riesgo_total'                   => round($this->riesgoTotal, 4),
            'costo_total'                    => round($this->costoTotal, 4),
            'tiempo_ejecucion_ms'            => round($this->tiempoEjecucionMs, 2),
            'best_found_iteration'           => $this->bestFoundIteration,
            'convergence_iteration'          => $this->convergenceIteration,
            'costo_referencia'               => round($this->costoReferencia, 4),
            'gap'                            => $this->gap !== null ? round($this->gap, 2) : null,
            'camino_menor_distancia_nodos'   => $this->caminoMenorDistanciaNodos,
            'distancia_camino_corto'         => round($this->distanciaCaminoCorto, 1),
            'riesgo_camino_corto'            => round($this->riesgoCaminoCorto, 4),
            'm7_reduccion_riesgo'            => $this->m7 !== null ? round($this->m7, 1) : null,
            'm8_incremento_distancia'        => $this->m8 !== null ? round($this->m8, 1) : null,
            'cantidad_alternativas'          => $this->cantidadAlternativas,
            'motivo_recomendacion'           => $this->motivoRecomendacion,
            'nivel_riesgo_estimado'          => $this->nivelRiesgoEstimado,
            'obstaculos_activos_count'       => $this->obstaculosActivosCount,
            'obstaculos_resueltos_count'     => $this->obstaculosResueltosCount,
            'obstaculos_detalle'             => $this->obstaculosDetalle,
            'fecha_calculo'                  => $this->fechaCalculo,
            'geometria_recomendada'          => $this->geometriaRecomendada,
            'geometria_mas_corta'            => $this->geometriaMasCorta,
            'geometria_alternativas'         => $this->geometriaAlternativas,
            'best_cost_history'              => $this->bestCostHistory,
            'metricas'                       => $this->metricas,
        ];
    }
}
