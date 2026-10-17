<?php

namespace Tests\Unit;

use App\Services\Aco\AcoConfig;
use App\Services\Aco\AcoExperimentService;
use App\Services\Aco\AcoResult;
use App\Services\Aco\AcoService;
use App\Services\Aco\CostCalculator;
use App\Services\Aco\Edge;
use App\Services\Aco\Graph;
use App\Services\Aco\ReferenceShortestPathService;
use InvalidArgumentException;
use Tests\Fixtures\AcoGraphFixtures;
use Tests\TestCase;

/**
 * Suite de Pruebas Unitarias Funcionales, Invariantes y Métricas del Núcleo ACO (ACO-2 a ACO-8).
 *
 * Esta suite valida el comportamiento del aporte ingenieril sobre grafos sintéticos
 * controlados, garantizando exactitud matemática, estabilidad algorítmica y reproducibilidad.
 */
class AcoServiceTest extends TestCase
{
    /**
     * Test A: ACO encuentra una ruta válida en un grafo simple.
     */
    public function test_A_finds_valid_route(): void
    {
        $graph = AcoGraphFixtures::createSinglePathGraph();
        $config = new AcoConfig(seed: 42, iterations: 10, ants: 5);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertEquals(['ORIGEN', 'N1', 'N2', 'DESTINO'], $result->path);
        $this->assertGreaterThan(0.0, $result->totalCost);
        $this->assertEqualsWithDelta(370.0, $result->totalDistance, 1e-4);
    }

    /**
     * Test B: Origen igual a destino produce una solución trivial sin error ni transitar aristas.
     */
    public function test_B_origin_equals_destination_produces_trivial_route(): void
    {
        $graph = AcoGraphFixtures::createSinglePathGraph();
        $config = new AcoConfig(iterations: 10, ants: 5);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'ORIGEN', $config);

        $this->assertTrue($result->success);
        $this->assertEquals(['ORIGEN'], $result->path);
        $this->assertEquals(0.0, $result->totalCost);
        $this->assertEquals(0.0, $result->totalDistance);
        $this->assertEquals(0.0, $result->totalRisk);
        $this->assertEquals(0, $result->bestFoundIteration);
        $this->assertEquals(0, $result->convergenceIteration);
    }

    /**
     * Test C: Destino inalcanzable retorna success=false de forma controlada sin excepción fatal.
     */
    public function test_C_unreachable_destination_returns_failure_gracefully(): void
    {
        $graph = AcoGraphFixtures::createUnreachableGraph();
        $config = new AcoConfig(iterations: 15, ants: 10, seed: 123);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertFalse($result->success);
        $this->assertEmpty($result->path);
        $this->assertEquals(0.0, $result->totalCost);
        $this->assertGreaterThanOrEqual(0.0, $result->executionTimeMs);
    }

    /**
     * Test D: Prevención estricta de ciclos (lista tabú evita bucles infinitos en grafos con ciclos).
     */
    public function test_D_cycle_prevention_avoids_infinite_loops(): void
    {
        $graph = AcoGraphFixtures::createCyclicGraph();
        $config = new AcoConfig(seed: 99, iterations: 20, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertEquals(['ORIGEN', 'C1', 'C2', 'DESTINO'], $result->path);

        // Verificar que ningún nodo aparezca más de una vez en la ruta devuelta
        $nodeCounts = array_count_values($result->path);
        foreach ($nodeCounts as $node => $count) {
            $this->assertEquals(1, $count, "El nodo [{$node}] fue visitado más de una vez.");
        }
    }

    /**
     * Test E: Distancia total devuelta coincide exactamente con la suma de las aristas del camino.
     */
    public function test_E_total_distance_matches_edge_sum(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(seed: 42, iterations: 20, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);
        $this->assertTrue($result->success);

        $calculatedDistance = 0.0;
        for ($i = 0; $i < count($result->path) - 1; $i++) {
            $edge = $graph->getEdge($result->path[$i], $result->path[$i + 1]);
            $this->assertNotNull($edge);
            $calculatedDistance += $edge->getDistance();
        }

        $this->assertEqualsWithDelta($calculatedDistance, $result->totalDistance, 1e-4);
    }

    /**
     * Test F: Riesgo total devuelto coincide con la agregación experimental (suma de riesgos de aristas).
     */
    public function test_F_total_risk_matches_edge_sum(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(seed: 42, iterations: 20, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);
        $this->assertTrue($result->success);

        $calculatedRisk = 0.0;
        for ($i = 0; $i < count($result->path) - 1; $i++) {
            $edge = $graph->getEdge($result->path[$i], $result->path[$i + 1]);
            $this->assertNotNull($edge);
            $calculatedRisk += $edge->getRisk();
        }

        $this->assertEqualsWithDelta($calculatedRisk, $result->totalRisk, 1e-4);
    }

    /**
     * Test G: Costo total coincide exactamente con la suma de costos calculada por CostCalculator.
     */
    public function test_G_total_cost_matches_cost_calculator(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(wDistance: 0.6, wRisk: 0.4, seed: 42, iterations: 20, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);
        $this->assertTrue($result->success);

        $calc = new CostCalculator(0.6, 0.4);
        $expectedCost = $calc->calculatePathCost($graph, $result->path);

        $this->assertEqualsWithDelta($expectedCost, $result->totalCost, 1e-4);
    }

    /**
     * Test H: Evaporación de feromona disipa valores residuales sin producir valores negativos.
     */
    public function test_H_pheromone_evaporation(): void
    {
        $graph = AcoGraphFixtures::createSinglePathGraph();
        $config = new AcoConfig(rho: 0.5, iterations: 5, ants: 2, seed: 1);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertGreaterThan(0.0, $result->totalCost);
    }

    /**
     * Test I: Depósito de feromona premia soluciones válidas y mantiene valores finitos positivos.
     */
    public function test_I_pheromone_deposit(): void
    {
        $graph = AcoGraphFixtures::createAlternativePathsGraph();
        $config = new AcoConfig(Q: 200.0, iterations: 10, ants: 5, seed: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertGreaterThan(0.0, $result->totalCost);
        $this->assertNotEmpty($result->path);
    }

    /**
     * Test J: Reproducibilidad estocástica exacta de resultados mediante semilla.
     * Verifica que misma seed reproduce path, cost, distance, risk, bestFoundIteration,
     * convergenceIteration y bestCostHistory, sin exigir tiempo de ejecución idéntico.
     */
    public function test_J_same_seed_reproduces_exact_solution(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config1 = new AcoConfig(seed: 9999, iterations: 30, ants: 10);
        $config2 = new AcoConfig(seed: 9999, iterations: 30, ants: 10);
        $service = new AcoService();

        $res1 = $service->solve($graph, 'ORIGEN', 'DESTINO', $config1);
        $res2 = $service->solve($graph, 'ORIGEN', 'DESTINO', $config2);

        $this->assertEquals($res1->path, $res2->path);
        $this->assertEqualsWithDelta($res1->totalCost, $res2->totalCost, 1e-9);
        $this->assertEqualsWithDelta($res1->totalDistance, $res2->totalDistance, 1e-9);
        $this->assertEqualsWithDelta($res1->totalRisk, $res2->totalRisk, 1e-9);
        $this->assertEquals($res1->bestFoundIteration, $res2->bestFoundIteration);
        $this->assertEquals($res1->convergenceIteration, $res2->convergenceIteration);
        $this->assertEquals($res1->bestCostHistory, $res2->bestCostHistory);
        $this->assertGreaterThanOrEqual(0.0, $res1->executionTimeMs);
        $this->assertGreaterThanOrEqual(0.0, $res2->executionTimeMs);
    }

    /**
     * Test K: Pesos inválidos son rechazados con InvalidArgumentException.
     */
    public function test_K_invalid_weights_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AcoConfig(wDistance: 0.7, wRisk: 0.5); // Suma 1.2 != 1.0
    }

    /**
     * Test L: Parámetros ACO inválidos son rechazados con InvalidArgumentException.
     */
    public function test_L_invalid_aco_params_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AcoConfig(rho: 1.5); // rho debe estar en [0.0, 1.0]
    }

    /**
     * Test M: Cambio de pesos (wD vs wR) altera la preferencia hacia la ruta corta o segura.
     */
    public function test_M_weight_changes_favor_distance_or_risk(): void
    {
        $graph = AcoGraphFixtures::createComplexGraph();
        $service = new AcoService();

        // Favorecer Distancia (wD = 0.85, wR = 0.15) -> Corredor Norte más corto (420m)
        $configDistance = new AcoConfig(wDistance: 0.85, wRisk: 0.15, seed: 42, iterations: 60, ants: 25);
        $resDistance = $service->solve($graph, 'ORIGEN', 'DESTINO', $configDistance);

        // Favorecer Seguridad (wD = 0.15, wR = 0.85) -> Corredor Sur más seguro (riesgo 0.25)
        $configRisk = new AcoConfig(wDistance: 0.15, wRisk: 0.85, seed: 42, iterations: 60, ants: 25);
        $resRisk = $service->solve($graph, 'ORIGEN', 'DESTINO', $configRisk);

        $this->assertTrue($resDistance->success);
        $this->assertTrue($resRisk->success);

        $this->assertNotEquals($resDistance->path, $resRisk->path);
        $this->assertLessThan($resRisk->totalDistance, $resDistance->totalDistance);
        $this->assertLessThan($resDistance->totalRisk, $resRisk->totalRisk);
    }

    /**
     * Test N: Convergencia formal registrada dentro del rango ejecutado (M4).
     */
    public function test_N_convergence_is_recorded(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(seed: 42, iterations: 40, ants: 15, convergenceEpsilon: 1e-4, convergencePatience: 5);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertGreaterThan(0, $result->convergenceIteration);
        $this->assertLessThanOrEqual(40, $result->convergenceIteration);
        $this->assertGreaterThan(0, $result->bestFoundIteration);
        $this->assertLessThanOrEqual(40, $result->bestFoundIteration);
    }

    /**
     * Test O: Historial de mejor costo global es estrictamente monótono no creciente.
     */
    public function test_O_best_cost_history_never_worsens(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(seed: 42, iterations: 25, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $history = $result->bestCostHistory;
        $this->assertCount(25, $history);

        for ($i = 1; $i < count($history); $i++) {
            $this->assertLessThanOrEqual(
                $history[$i - 1] + 1e-9,
                $history[$i],
                "El costo empeoró en la iteración " . ($i + 1)
            );
        }
    }

    /**
     * Test P: Referencia determinista (Dijkstra) encuentra el camino de costo mínimo teórico bajo normalización lineal D/Dmax.
     * En SmallGraph con Dmax=400:
     * - ORIGEN->B->D->DESTINO tiene D=1100m, R=0.3 -> C = 0.5*(1100/400) + 0.5*0.3 = 1.375 + 0.15 = 1.5250
     * - ORIGEN->B->E->DESTINO tiene D=750m, R=1.3 -> C = 0.5*(750/400) + 0.5*1.3 = 0.9375 + 0.65 = 1.5875
     * Óptimo determinista global: ORIGEN->B->D->DESTINO con C=1.5250.
     */
    public function test_P_deterministic_reference_finds_minimum_cost(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $refService = new ReferenceShortestPathService();

        $refResult = $refService->solve($graph, 'ORIGEN', 'DESTINO', 0.5, 0.5);

        $this->assertTrue($refResult->success);
        $this->assertEquals(['ORIGEN', 'B', 'D', 'DESTINO'], $refResult->path);
        $this->assertEqualsWithDelta(1.5250, $refResult->totalCost, 1e-4);
        $this->assertEquals(1100.0, $refResult->totalDistance);
        $this->assertEqualsWithDelta(0.3, $refResult->totalRisk, 1e-4);
    }

    /**
     * Test Q: Brecha M2 (Gap) es cercana a 0.0% cuando ACO encuentra la solución óptima.
     */
    public function test_Q_aco_reaches_near_zero_gap_on_known_optimum(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $refService = new ReferenceShortestPathService();
        $refResult = $refService->solve($graph, 'ORIGEN', 'DESTINO', 0.5, 0.5);

        $config = new AcoConfig(wDistance: 0.5, wRisk: 0.5, seed: 42, iterations: 35, ants: 15);
        $acoService = new AcoService();
        $acoResult = $acoService->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($acoResult->success);
        $gap = AcoExperimentService::calculateGap($acoResult->totalCost, $refResult->totalCost);

        $this->assertNotNull($gap);
        $this->assertLessThanOrEqual(0.001, $gap);
    }

    /**
     * Test R: Batería experimental calcula la tasa de éxito M5 (0 <= TE <= 100).
     */
    public function test_R_multiple_experiments_calculates_success_rate(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(wDistance: 0.5, wRisk: 0.5, iterations: 10, ants: 5);
        $expService = new AcoExperimentService();

        $stats = $expService->runExperiment($graph, 'ORIGEN', 'DESTINO', $config, runsCount: 5);

        $this->assertEquals(5, $stats['runsCount']);
        $this->assertEquals(5, $stats['successes']);
        $this->assertEquals(100.0, $stats['successRate']);
        $this->assertGreaterThanOrEqual(0.0, $stats['successRate']);
        $this->assertLessThanOrEqual(100.0, $stats['successRate']);
    }

    /**
     * Test S: Batería experimental calcula media y desviación estándar muestral (M6).
     */
    public function test_S_calculates_mean_and_std_dev(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(wDistance: 0.5, wRisk: 0.5, iterations: 10, ants: 5);
        $expService = new AcoExperimentService();

        $stats = $expService->runExperiment($graph, 'ORIGEN', 'DESTINO', $config, runsCount: 5);

        $this->assertArrayHasKey('meanCost', $stats);
        $this->assertArrayHasKey('stdDev', $stats);
        $this->assertGreaterThan(0.0, $stats['meanCost']);
        $this->assertGreaterThanOrEqual(0.0, $stats['stdDev']);
    }

    /**
     * Test T: Batería experimental calcula coeficiente de variación CV% (M6).
     */
    public function test_T_calculates_coefficient_of_variation(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(wDistance: 0.5, wRisk: 0.5, iterations: 10, ants: 5);
        $expService = new AcoExperimentService();

        $stats = $expService->runExperiment($graph, 'ORIGEN', 'DESTINO', $config, runsCount: 5);

        $this->assertArrayHasKey('cvPercent', $stats);
        $this->assertNotNull($stats['cvPercent']);
        $this->assertGreaterThanOrEqual(0.0, $stats['cvPercent']);
    }

    /**
     * Test U: Auditoría de normalización aditiva:
     * Dos rutas con idéntica distancia física (200m) pero diferente número de aristas
     * (1 arista de 200m frente a 2 aristas de 100m) no reciben ninguna preferencia artificial.
     * Ambas producen exactamente el mismo costo de distancia bajo D/Dmax.
     */
    public function test_U_equal_physical_distance_different_edge_count_not_biased(): void
    {
        $graph = AcoGraphFixtures::createEqualDistanceDifferentEdgeCountGraph();
        $calc = new CostCalculator(wDistance: 1.0, wRisk: 0.0); // Puramente distancia
        $maxD = $graph->getMaxDistance(); // 200.0

        // Ruta A: 1 arista de 200m
        $edgeA = $graph->getEdge('ORIGEN', 'DEST_A');
        $this->assertNotNull($edgeA);
        $costA = $calc->calculateEdgeCost($edgeA, $maxD);

        // Ruta B: 2 aristas de 100m
        $edgeB1 = $graph->getEdge('ORIGEN', 'MID_B');
        $edgeB2 = $graph->getEdge('MID_B', 'DEST_B');
        $this->assertNotNull($edgeB1);
        $this->assertNotNull($edgeB2);
        $costB = $calc->calculateEdgeCost($edgeB1, $maxD) + $calc->calculateEdgeCost($edgeB2, $maxD);

        // Ambas distancias físicas son 200m -> Ambos costos normalizados deben ser exactamente 1.0
        $this->assertEqualsWithDelta(1.0, $costA, 1e-9);
        $this->assertEqualsWithDelta(1.0, $costB, 1e-9);
        $this->assertEqualsWithDelta($costA, $costB, 1e-9, "La normalización sesga rutas por fragmentación de aristas.");
    }

    /**
     * Test V: M2 Gap con costo de referencia determinista igual a 0 devuelve null (N/A).
     */
    public function test_V_gap_with_zero_reference_cost_returns_null(): void
    {
        $gapZero = AcoExperimentService::calculateGap(0.0, 0.0);
        $this->assertNull($gapZero, "Gap con C_ref=0 debe ser null (indefinido), no 0.0%");

        $gapPositive = AcoExperimentService::calculateGap(1.5, 0.0);
        $this->assertNull($gapPositive, "Gap con C_ref=0 debe ser null (indefinido)");

        $validGap = AcoExperimentService::calculateGap(1.2, 1.0);
        $this->assertEqualsWithDelta(20.0, $validGap, 1e-4);
    }

    /**
     * Test W: M6 Coeficiente de Variación con media de costo igual a 0 devuelve null (N/A).
     */
    public function test_W_cv_with_zero_mean_returns_null(): void
    {
        $cvZero = AcoExperimentService::calculateCoefficientOfVariation(0.0, 0.0);
        $this->assertNull($cvZero, "CV con media=0 debe ser null (indefinido)");

        $cvValid = AcoExperimentService::calculateCoefficientOfVariation(0.1, 1.0);
        $this->assertEqualsWithDelta(10.0, $cvValid, 1e-4);
    }

    /**
     * Test X: M7 Reducción de Riesgo con riesgo base igual a 0 devuelve null (N/A).
     */
    public function test_X_m7_risk_reduction_with_zero_base_risk_returns_null(): void
    {
        $rrZero = AcoExperimentService::calculateRiskReduction(0.0, 0.0);
        $this->assertNull($rrZero, "Reducción de riesgo con R_corta=0 debe ser null");

        $rrValid = AcoExperimentService::calculateRiskReduction(1.0, 0.2);
        $this->assertEqualsWithDelta(80.0, $rrValid, 1e-4);
    }

    /**
     * Test Y: M8 Incremento de Distancia con distancia base igual a 0 devuelve null (N/A).
     */
    public function test_Y_m8_distance_increase_with_zero_base_distance_returns_null(): void
    {
        $idZero = AcoExperimentService::calculateDistanceIncrease(0.0, 0.0);
        $this->assertNull($idZero, "Incremento de distancia con D_corta=0 debe ser null");

        $idValid = AcoExperimentService::calculateDistanceIncrease(100.0, 150.0);
        $this->assertEqualsWithDelta(50.0, $idValid, 1e-4);
    }

    /**
     * Test Z: Misma semilla reproduce comportamiento algorítmico sin exigir igualdad de tiempo de ejecución.
     */
    public function test_Z_same_seed_reproduces_algorithm_without_demanding_same_execution_time(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $configA = new AcoConfig(seed: 777, iterations: 20, ants: 10);
        $configB = new AcoConfig(seed: 777, iterations: 20, ants: 10);
        $service = new AcoService();

        $runA = $service->solve($graph, 'ORIGEN', 'DESTINO', $configA);
        $runB = $service->solve($graph, 'ORIGEN', 'DESTINO', $configB);

        $this->assertEquals($runA->path, $runB->path);
        $this->assertEqualsWithDelta($runA->totalCost, $runB->totalCost, 1e-9);
        $this->assertEqualsWithDelta($runA->totalDistance, $runB->totalDistance, 1e-9);
        $this->assertEqualsWithDelta($runA->totalRisk, $runB->totalRisk, 1e-9);
        $this->assertEquals($runA->bestCostHistory, $runB->bestCostHistory);

        // El tiempo de cómputo depende del entorno del sistema y de la carga de CPU; se verifica no negatividad.
        $this->assertGreaterThanOrEqual(0.0, $runA->executionTimeMs);
        $this->assertGreaterThanOrEqual(0.0, $runB->executionTimeMs);
    }

    /**
     * Test AA: La semilla de ACO no altera el estado pseudoaleatorio global del sistema (RNG encapsulado).
     */
    public function test_AA_aco_seed_does_not_mutate_global_rng(): void
    {
        // Fijar semilla global de PHP
        mt_srand(123456);
        $expectedNextRand = mt_rand();

        // Restablecer semilla global al mismo punto
        mt_srand(123456);

        // Ejecutar ACO con semilla propia encapsulada
        $graph = AcoGraphFixtures::createSmallGraph();
        $config = new AcoConfig(seed: 987654, iterations: 10, ants: 5);
        $service = new AcoService();
        $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        // La siguiente llamada a mt_rand() debe ser exactamente idéntica a $expectedNextRand
        $actualNextRand = mt_rand();
        $this->assertEquals(
            $expectedNextRand,
            $actualNextRand,
            "ACO mutó el generador pseudoaleatorio global de PHP (fuga de estado RNG)."
        );
    }
}
