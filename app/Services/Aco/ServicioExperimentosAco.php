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
 *       Gap(%) = [(C_ACO - C_ref) / C_ref] * 100.
 *       CONTRATO MATEMÁTICO: Si C_ref <= 0, la división es indefinida y retorna null (N/A).
 * - M3: Tiempo de ejecución computacional (ms >= 0.0).
 * - M4: Convergencia (bestFoundIteration y convergenceIteration).
 * - M5: Tasa de éxito: TE(%) = (ejecuciones_exitosas / ejecuciones_totales) * 100.
 * - M6: Estabilidad: Media, desviación estándar muestral y Coeficiente de Variación:
 *       CV(%) = (desviación / media) * 100.
 *       CONTRATO MATEMÁTICO: Si media <= 0, el CV es indefinido y retorna null (N/A).
 * - M7: Reducción de riesgo respecto a la ruta más corta:
 *       RR(%) = [(R_corta - R_ACO) / R_corta] * 100.
 *       CONTRATO MATEMÁTICO: Si R_corta <= 0, retorna null (N/A).
 * - M8: Incremento de distancia respecto a la ruta más corta:
 *       ID(%) = [(D_ACO - D_corta) / D_corta] * 100.
 *       CONTRATO MATEMÁTICO: Si D_corta <= 0, retorna null (N/A).
 */
class ServicioExperimentosAco
{
    /**
     * Motor de resolución estocástica ACO.
     */
    private ServicioAco $acoService;

    /**
     * Motor de referencia determinista (Dijkstra) para cálculo de Gap (M2).
     */
    private ServicioRutaReferencia $refService;

    /**
     * Constructor del servicio experimental.
     *
     * @param ServicioAco|null            $acoService Instancia del motor ACO.
     * @param ServicioRutaReferencia|null $refService Instancia del algoritmo determinista de referencia.
     */
    public function __construct(
        ?ServicioAco $acoService = null,
        ?ServicioRutaReferencia $refService = null
    ) {
        $this->acoService = $acoService ?? new ServicioAco();
        $this->refService = $refService ?? new ServicioRutaReferencia();
    }

    /**
     * Ejecuta una batería controlada de N repeticiones del algoritmo ACO sobre un grafo dado.
     *
     * @param Grafo            $graph      Grafo con la topología a evaluar.
     * @param string           $startNode  Nodo origen.
     * @param string           $targetNode Nodo destino.
     * @param ConfiguracionAco $config     Parámetros de configuración del experimento.
     * @param int              $runsCount  Número de ejecuciones repetidas (runsCount >= 1).
     * @return array<string, mixed> Resumen estadístico exhaustivo con métricas M1 a M6.
     *
     * @throws InvalidArgumentException Si runsCount < 1.
     */
    public function runExperiment(
        Grafo $graph,
        string $startNode,
        string $targetNode,
        ConfiguracionAco $config,
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
                $gap = self::calculateGap($res->totalCost, $refCost);
                if ($gap !== null) {
                    $gaps[] = $gap;
                }
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
                'cvPercent' => null,
                'avgExecutionTimeMs' => 0.0,
                'avgBestFoundIteration' => 0.0,
                'avgConvergenceIteration' => 0.0,
                'refCost' => $refCost,
                'avgGapPercent' => null,
                'bestGapPercent' => null,
                'worstGapPercent' => null,
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

        // Coeficiente de variación (M6): Si meanCost <= 0, retorna null (indefinido)
        $cvPercent = self::calculateCoefficientOfVariation($stdDev, $meanCost);

        // Métricas de tiempo y convergencia (M3 y M4)
        $avgTime = array_sum($times) / count($times);
        $avgBestFound = array_sum($bestFoundIters) / count($bestFoundIters);
        $avgConvergence = array_sum($convergences) / count($convergences);

        // Métricas de brecha (M2): Si refCost <= 0 o no hay gaps calculables, retorna null
        $avgGap = count($gaps) > 0 ? (array_sum($gaps) / count($gaps)) : null;
        $bestGap = count($gaps) > 0 ? min($gaps) : null;
        $worstGap = count($gaps) > 0 ? max($gaps) : null;

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
     * Rigor matemático:
     * Si C_ref <= 0.0, la métrica no está definida formalmente (denominador nulo),
     * por lo que retorna estrictamente null (N/A) en lugar de un cero artificial.
     *
     * @param float $acoCost Costo obtenido por ACO.
     * @param float $refCost Costo determinista de referencia.
     * @return float|null Gap porcentual (>= 0.0) o null si C_ref <= 0.
     */
    public static function calculateGap(float $acoCost, float $refCost): ?float
    {
        if ($refCost <= 0.0) {
            return null;
        }

        $gap = (($acoCost - $refCost) / $refCost) * 100.0;

        return max(0.0, $gap);
    }

    /**
     * Calcula la métrica M6: Coeficiente de Variación porcentual (CV%).
     *
     * Fórmula:
     * CV(%) = (stdDev / meanCost) * 100
     *
     * Rigor matemático:
     * Si meanCost <= 0.0, el CV es indefinido y retorna null (N/A).
     *
     * @param float $stdDev   Desviación estándar muestral.
     * @param float $meanCost Costo medio de las soluciones.
     * @return float|null Coeficiente de variación (%) o null si media <= 0.
     */
    public static function calculateCoefficientOfVariation(float $stdDev, float $meanCost): ?float
    {
        if ($meanCost <= 0.0) {
            return null;
        }

        return ($stdDev / $meanCost) * 100.0;
    }

    /**
     * Calcula la métrica M7: Reducción de Riesgo respecto a la ruta más corta (menor distancia).
     *
     * Fórmula:
     * RR(%) = [(R_corta - R_ACO) / R_corta] * 100
     *
     * Rigor matemático:
     * Si R_corta <= 0.0, el denominador es cero y retorna null (N/A).
     *
     * @param float $shortestPathRisk Riesgo total de la ruta de mínima distancia.
     * @param float $acoRisk          Riesgo total de la ruta seleccionada por ACO.
     * @return float|null Porcentaje de reducción de riesgo o null si R_corta <= 0.
     */
    public static function calculateRiskReduction(float $shortestPathRisk, float $acoRisk): ?float
    {
        if ($shortestPathRisk <= 0.0) {
            return null;
        }

        return (($shortestPathRisk - $acoRisk) / $shortestPathRisk) * 100.0;
    }

    /**
     * Calcula la métrica M8: Incremento de Distancia respecto a la ruta más corta.
     *
     * Fórmula:
     * ID(%) = [(D_ACO - D_corta) / D_corta] * 100
     *
     * Rigor matemático:
     * Si D_corta <= 0.0, el denominador es cero y retorna null (N/A).
     *
     * @param float $shortestPathDistance Distancia en metros de la ruta más corta.
     * @param float $acoDistance          Distancia en metros de la ruta elegida por ACO.
     * @return float|null Porcentaje de incremento de distancia o null si D_corta <= 0.
     */
    public static function calculateDistanceIncrease(float $shortestPathDistance, float $acoDistance): ?float
    {
        if ($shortestPathDistance <= 0.0) {
            return null;
        }

        return (($acoDistance - $shortestPathDistance) / $shortestPathDistance) * 100.0;
    }
}
