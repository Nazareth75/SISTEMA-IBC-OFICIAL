<?php

namespace App\Services\Aco;

/**
 * Objeto de Transferencia de Datos (DTO) que encapsula el resultado de una ejecución ACO.
 *
 * Responsabilidad ingenieril:
 * Proporciona una estructura inmutable, tipada y exhaustiva con todas las métricas primarias
 * (M1 a M4) generadas durante la resolución algorítmica de una ruta peatonal.
 */
class ResultadoAco
{
    /**
     * Indica si el algoritmo logró encontrar una ruta válida desde el origen hasta el destino.
     * En caso de grafo desconectado o destino inalcanzable, toma el valor false sin provocar error fatal.
     */
    public bool $success;

    /**
     * Secuencia ordenada de identificadores de nodos que conforman el camino encontrado.
     * Ejemplo: ['ORIGEN', 'INTERSECCION_A', 'INTERSECCION_B', 'DESTINO'].
     *
     * @var string[]
     */
    public array $path;

    /**
     * Métrica M1: Costo total normalizado de la mejor solución encontrada bajo la función objetivo.
     * Corresponde a la suma de los costos canónicos C(e) de las aristas recorridas.
     */
    public float $totalCost;

    /**
     * Longitud física total del trayecto en metros (suma de las distancias de las aristas).
     */
    public float $totalDistance;

    /**
     * Nivel de riesgo total acumulado del trayecto.
     *
     * AVISO METODOLÓGICO:
     * La suma simple de riesgos de aristas constituye una agregación experimental temporal
     * para evaluación sintética en esta fase, y NO la definición definitiva del riesgo
     * integral de rutas del sistema IBC (la cual se articulará formalmente al conectar TramoRiskService).
     */
    public float $totalRisk;

    /**
     * Métrica M3: Tiempo de cómputo transcurrido durante la ejecución, expresado en milisegundos (ms >= 0.0).
     */
    public float $executionTimeMs;

    /**
     * Métrica M4a: Número de iteración en la que se descubrió por primera vez la mejor solución global.
     */
    public int $bestFoundIteration;

    /**
     * Métrica M4b: Número de iteración en la que se satisfizo el criterio formal de convergencia
     * (ausencia de mejora > epsilon durante patience iteraciones consecutivas).
     */
    public int $convergenceIteration;

    /**
     * Historial paso a paso del mejor costo global registrado al finalizar cada iteración.
     * Permite auditar matemáticamente que el mejor costo nunca empeora (monotonía no creciente).
     *
     * @var float[]
     */
    public array $bestCostHistory;

    /**
     * Constructor del resultado de ejecución.
     *
     * @param bool     $success              Indica éxito en encontrar camino.
     * @param string[] $path                 Lista secuencial de nodos.
     * @param float    $totalCost            Costo canónico total (M1).
     * @param float    $totalDistance        Distancia física total en metros.
     * @param float    $totalRisk            Riesgo total acumulado (agregación experimental temporal).
     * @param float    $executionTimeMs      Tiempo de ejecución en ms (M3).
     * @param int      $bestFoundIteration   Iteración del hallazgo óptimo.
     * @param int      $convergenceIteration Iteración de convergencia (M4).
     * @param float[]  $bestCostHistory      Trayectoria del costo a lo largo de las iteraciones.
     */
    public function __construct(
        bool $success,
        array $path = [],
        float $totalCost = 0.0,
        float $totalDistance = 0.0,
        float $totalRisk = 0.0,
        float $executionTimeMs = 0.0,
        int $bestFoundIteration = 0,
        int $convergenceIteration = 0,
        array $bestCostHistory = []
    ) {
        $this->success = $success;
        $this->path = $path;
        $this->totalCost = $totalCost;
        $this->totalDistance = $totalDistance;
        $this->totalRisk = $totalRisk;
        $this->executionTimeMs = max(0.0, $executionTimeMs);
        $this->bestFoundIteration = $bestFoundIteration;
        $this->convergenceIteration = $convergenceIteration;
        $this->bestCostHistory = $bestCostHistory;
    }
}
