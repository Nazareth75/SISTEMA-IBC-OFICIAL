<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Servicio de Experimentación y Evaluación Estadística para el Aporte ACO (ACO-8).
 *
 * Responsabilidad ingenieril:
 * Conduce baterías de ejecuciones repetidas e independientes del motor ACO para
 * cuantificar su desempeño empírico mediante métricas formales de calidad (M1-M8).
 *
 * MÉTRICAS EVALUADAS (M1 - M8):
 * - M1: Costo de la mejor solución (C_ACO). Menor es mejor.
 * - M2: Brecha relativa respecto al óptimo determinista (Gap%):
 *       Gap(%) = [(C_ACO - C_ref) / C_ref] * 100 (con protección si C_ref = 0).
 * - M3: Tiempo de ejecución computacional (ms >= 0.0).
 * - M4: Convergencia (bestFoundIteration y convergenceIteration).
 * - M5: Tasa de éxito: TE(%) = (ejecuciones_exitosas / ejecuciones_totales) * 100.
 * - M6: Estabilidad: Media, desviación estándar muestral y Coeficiente de Variación:
 *       CV(%) = (desviación / media) * 100 (con protección si media = 0).
 * - M7: Reducción de riesgo respecto a la ruta más corta:
 *       RR(%) = [(R_corta - R_ACO) / R_corta] * 100 (con protección si R_corta = 0).
 * - M8: Incremento de distancia respecto a la ruta más corta:
 *       ID(%) = [(D_ACO - D_corta) / D_corta] * 100 (con protección si D_corta = 0).
 */
class AcoExperimentService
{
    /**
     * Motor de resolución estocástica ACO.
     */
    private AcoService $acoService;

    /**
     * Motor de referencia determinista (Dijkstra) para cálculo de Gap (M2).
     */
    private ReferenceShortestPathService $refService;

    /**
     * Constructor del servicio experimental.
     *
     * @param AcoService|null                   $acoService Instancia del motor ACO.
     * @param ReferenceShortestPathService|null $refService Instancia del algoritmo determinista de referencia.
     */
    public function __construct(
        ?AcoService $acoService = null,
        ?ReferenceShortestPathService $refService = null
    ) {
        $this->acoService = $acoService ?? new AcoService();
        $this->refService = $refService ?? new ReferenceShortestPathService();
    }

    /**
     * Ejecuta una batería controlada de N repeticiones del algoritmo ACO sobre un grafo dado.
     *
     * @param Graph     $graph      Grafo con la topología a evaluar.
     * @param string    $startNode  Nodo origen.
     * @param string    $targetNode Nodo destino.
     * @param AcoConfig $config     Parámetros de configuración del experimento.
     * @param int       $runsCount  Número de ejecuciones repetidas (runsCount >= 1).
     * @return array<string, mixed> Resumen estadístico exhaustivo con métricas M1 a M6.
     *
     * @throws InvalidArgumentException Si runsCount < 1.
     */
    public function runExperiment(
        Graph $graph,
        string $startNode,
        string $targetNode,
        AcoConfig $config,
        int $runsCount = 30
    ): array {
        if ($runsCount < 1) {
            throw new InvalidArgumentException(
                "El número de ejecuciones experimentales debe ser al menos 1. Valor recibido: {$runsCount}"
            );
        }

        // Ejecución previa de la referencia determinista sobre el mismo grafo y con los mismos pesos
        $refResult = $this->refService->solve($graph, $startNode, $targetNode, $config->wDistance, $config->wRisk);
        $refCost = $refResult->success ? $refResult->totalCost : 0.0;

        $results = [];
        $successes = 0;
        $costs = [];
        $distances = [];
        $risks = [];
        $times = [];
        $convergences = [];
        $bestFoundIters = [];
        $gaps = [];

        for ($r = 0; $r < $runsCount; $r++) {
            // Manejo determinista de semilla si config->seed está definida para asegurar reproducibilidad
            $runConfig = clone $config;
            if ($config->seed !== null) {
                $runConfig->seed = $config->seed + $r;
            }

            $res = $this->acoService->solve($graph, $startNode, $targetNode, $runConfig);
            $results[] = $res;

            if ($res->success) {
                $successes++;
                $costs[] = $res->totalCost;
                $distances[] = $res->totalDistance;
                $risks[] = $res->totalRisk;
                $times[] = $res->executionTimeMs;
                $convergences[] = $res->convergenceIteration;
                $bestFoundIters[] = $res->bestFoundIteration;

                // Métrica M2: Brecha respecto a la referencia determinista
                $gaps[] = self::calculateGap($res->totalCost, $refCost);
            }
        }

        // Métrica M5: Tasa de éxito
        $successRate = ($successes / $runsCount) * 100.0;

        if ($successes === 0) {
            return [
                'runsCount' => $runsCount,
                'successes' => 0,
                'successRate' => 0.0,
                'bestCost' => 0.0,
                'worstCost' => 0.0,
                'meanCost' => 0.0,
                'stdDev' => 0.0,
                'cvPercent' => 0.0,
                'avgExecutionTimeMs' => 0.0,
                'avgBestFoundIteration' => 0.0,
                'avgConvergenceIteration' => 0.0,
                'refCost' => $refCost,
                'avgGapPercent' => 0.0,
                'bestGapPercent' => 0.0,
                'worstGapPercent' => 0.0,
                'refResult' => $refResult,
                'results' => $results,
            ];
        }

        // Estadísticos de costo (M1 y M6)
        $bestCost = min($costs);
        $worstCost = max($costs);
        $meanCost = array_sum($costs) / count($costs);

        // Desviación estándar muestral (n - 1)
        $varianceSum = 0.0;
        foreach ($costs as $c) {
            $varianceSum += pow($c - $meanCost, 2);
        }
        $stdDev = count($costs) > 1 ? sqrt($varianceSum / (count($costs) - 1)) : 0.0;

        // Coeficiente de variación (M6): Protegido contra división por cero
        $cvPercent = $meanCost > 0.0 ? ($stdDev / $meanCost) * 100.0 : 0.0;

        // Métricas de tiempo y convergencia (M3 y M4)
        $avgTime = array_sum($times) / count($times);
        $avgBestFound = array_sum($bestFoundIters) / count($bestFoundIters);
        $avgConvergence = array_sum($convergences) / count($convergences);

        // Métricas de brecha (M2)
        $avgGap = count($gaps) > 0 ? (array_sum($gaps) / count($gaps)) : 0.0;
        $bestGap = count($gaps) > 0 ? min($gaps) : 0.0;
        $worstGap = count($gaps) > 0 ? max($gaps) : 0.0;

        return [
            'runsCount' => $runsCount,
            'successes' => $successes,
            'successRate' => $successRate,
            'bestCost' => $bestCost,
            'worstCost' => $worstCost,
            'meanCost' => $meanCost,
            'stdDev' => $stdDev,
            'cvPercent' => $cvPercent,
            'avgExecutionTimeMs' => $avgTime,
            'avgBestFoundIteration' => $avgBestFound,
            'avgConvergenceIteration' => $avgConvergence,
            'refCost' => $refCost,
            'avgGapPercent' => $avgGap,
            'bestGapPercent' => $bestGap,
            'worstGapPercent' => $worstGap,
            'refResult' => $refResult,
            'results' => $results,
        ];
    }

    /**
     * Calcula la métrica M2: Gap relativo porcentual respecto al costo de referencia.
     *
     * Fórmula:
     * Gap(%) = [(C_ACO - C_ref) / C_ref] * 100
     *
     * Protección contra división por cero:
     * Si C_ref es 0.0:
     * - Si C_ACO es 0.0, Gap es 0.0%.
     * - Si C_ACO > 0.0, retorna 0.0% de forma segura sin producir NaN ni división por cero.
     *
     * @param float $acoCost Costo obtenido por ACO.
     * @param float $refCost Costo determinista de referencia.
     * @return float Gap porcentual (>= 0.0 en condiciones óptimas).
     */
    public static function calculateGap(float $acoCost, float $refCost): float
    {
        if ($refCost <= 0.0) {
            return 0.0;
        }

        $gap = (($acoCost - $refCost) / $refCost) * 100.0;

        // Si por imprecisión de punto flotante acoCost es infinitesimalmente menor a refCost, acotar a 0.0
        return max(0.0, $gap);
    }

    /**
     * Calcula la métrica M7: Reducción de Riesgo respecto a la ruta más corta (menor distancia).
     *
     * Fórmula:
     * RR(%) = [(R_corta - R_ACO) / R_corta] * 100
     *
     * Mide cuánto riesgo se ahorra al transitar la ruta seleccionada por ACO en comparación
     * con la ruta más directa (menor distancia) que podría contener mayor exposición a peligros.
     *
     * @param float $shortestPathRisk Riesgo total de la ruta de mínima distancia.
     * @param float $acoRisk          Riesgo total de la ruta seleccionada por ACO.
     * @return float Porcentaje de reducción de riesgo.
     */
    public static function calculateRiskReduction(float $shortestPathRisk, float $acoRisk): float
    {
        if ($shortestPathRisk <= 0.0) {
            return 0.0;
        }

        return (($shortestPathRisk - $acoRisk) / $shortestPathRisk) * 100.0;
    }

    /**
     * Calcula la métrica M8: Incremento de Distancia respecto a la ruta más corta.
     *
     * Fórmula:
     * ID(%) = [(D_ACO - D_corta) / D_corta] * 100
     *
     * Mide el costo adicional en metros (desvío) que el peatón asume voluntariamente
     * a cambio de navegar por vías significativamente más seguras.
     *
     * @param float $shortestPathDistance Distancia en metros de la ruta más corta.
     * @param float $acoDistance          Distancia en metros de la ruta elegida por ACO.
     * @return float Porcentaje de incremento de distancia.
     */
    public static function calculateDistanceIncrease(float $shortestPathDistance, float $acoDistance): float
    {
        if ($shortestPathDistance <= 0.0) {
            return 0.0;
        }

        return (($acoDistance - $shortestPathDistance) / $shortestPathDistance) * 100.0;
    }
}
