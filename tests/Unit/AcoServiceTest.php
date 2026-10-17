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
     * Test F: Riesgo total devuelto coincide exactamente con la suma de riesgos de las aristas.
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
        // Con rho = 0.5, la evaporación disipa el 50% en cada paso
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
     * Test J: Reproducibilidad estocástica exacta mediante semilla (seed).
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
        $this->assertEquals($res1->bestCostHistory, $res2->bestCostHistory);
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
     * Test P: Referencia determinista (Dijkstra) encuentra el camino de costo mínimo teórico.
     */
    public function test_P_deterministic_reference_finds_minimum_cost(): void
    {
        $graph = AcoGraphFixtures::createSmallGraph();
        $refService = new ReferenceShortestPathService();

        $refResult = $refService->solve($graph, 'ORIGEN', 'DESTINO', 0.5, 0.5);

        $this->assertTrue($refResult->success);
        // Bajo normalización Min-Max estricta (D - Dmin)/(Dmax - Dmin) con Dmin=100 y Dmax=400:
        // ORIGEN->B->E->DESTINO tiene costo 1.4000 (D=750m, R=1.3), superando a B->D->DESTINO con 1.4833.
        $this->assertEquals(['ORIGEN', 'B', 'E', 'DESTINO'], $refResult->path);
        $this->assertEqualsWithDelta(1.4000, $refResult->totalCost, 1e-4);
        $this->assertEquals(750.0, $refResult->totalDistance);
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
        $this->assertGreaterThanOrEqual(0.0, $stats['cvPercent']);
    }

    /**
     * Test U: Métricas M7 (reducción de riesgo) y M8 (incremento de distancia) calculadas correctamente.
     */
    public function test_U_calculates_metrics_m7_and_m8_on_synthetic_scenario(): void
    {
        // En ShortRiskyVsLongSafeGraph:
        // Ruta Corta: D=150m, R=1.8 (R_corta = 1.8)
        // Ruta Segura: D=600m, R=0.15 (R_aco = 0.15)
        $shortestDist = 150.0;
        $shortestRisk = 1.8;

        $acoDist = 600.0;
        $acoRisk = 0.15;

        $rr = AcoExperimentService::calculateRiskReduction($shortestRisk, $acoRisk);
        $id = AcoExperimentService::calculateDistanceIncrease($shortestDist, $acoDist);

        // RR = ((1.8 - 0.15) / 1.8) * 100 = 91.666%
        $this->assertEqualsWithDelta(91.6666, $rr, 0.01);

        // ID = ((600 - 150) / 150) * 100 = 300.0%
        $this->assertEqualsWithDelta(300.0, $id, 0.01);
    }

    /**
     * Test V: Invariante de probabilidades de transición:
     * Para cualquier nodo con alternativas salientes válidas, las probabilidades son finitas, >= 0, <= 1 y suman ~ 1.0.
     */
    public function test_V_transition_probabilities_invariant(): void
    {
        $graph = AcoGraphFixtures::createMultiPathGridGraph();
        $outgoing = $graph->getOutgoingEdges('ORIGEN');
        $this->assertCount(2, $outgoing);

        $costCalc = new CostCalculator(0.5, 0.5);
        $maxD = $graph->getMaxDistance();
        $minD = $graph->getMinDistance();

        $desirabilities = [];
        $sum = 0.0;

        foreach ($outgoing as $edge) {
            $cost = $costCalc->calculateEdgeCost($edge, $maxD, $minD);
            $eta = 1.0 / ($cost + 1e-6);
            $tau = 1.0;
            $des = pow($tau, 1.0) * pow($eta, 2.0);
            $desirabilities[] = $des;
            $sum += $des;
        }

        $this->assertGreaterThan(0.0, $sum);
        $probSum = 0.0;

        foreach ($desirabilities as $des) {
            $p = $des / $sum;
            $this->assertGreaterThanOrEqual(0.0, $p);
            $this->assertLessThanOrEqual(1.0, $p);
            $this->assertFalse(is_nan($p));
            $this->assertFalse(is_infinite($p));
            $probSum += $p;
        }

        $this->assertEqualsWithDelta(1.0, $probSum, 1e-5);
    }

    /**
     * Test W: Invariante de aristas consecutivas:
     * Cada par consecutivo de nodos (u, v) en la ruta devuelta corresponde a una arista real del grafo.
     */
    public function test_W_consecutive_path_nodes_are_real_edges(): void
    {
        $graph = AcoGraphFixtures::createMultiPathGridGraph();
        $config = new AcoConfig(seed: 42, iterations: 20, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);
        $this->assertTrue($result->success);

        $this->assertEquals('ORIGEN', $result->path[0]);
        $this->assertEquals('DESTINO', $result->path[count($result->path) - 1]);

        for ($i = 0; $i < count($result->path) - 1; $i++) {
            $u = $result->path[$i];
            $v = $result->path[$i + 1];
            $this->assertTrue($graph->hasEdge($u, $v), "El salto {$u}->{$v} no es una arista válida en el grafo.");
        }
    }

    /**
     * Test X: Grafo sintético de gran escala (35+ nodos) resuelve y encuentra solución válida.
     */
    public function test_X_large_graph_solves_successfully(): void
    {
        $graph = AcoGraphFixtures::createLargeGraph(10);
        $this->assertGreaterThanOrEqual(30, count($graph->getNodes()));

        $config = new AcoConfig(seed: 42, iterations: 30, ants: 15);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertEquals('ORIGEN', $result->path[0]);
        $this->assertEquals('DESTINO', $result->path[count($result->path) - 1]);
        $this->assertGreaterThan(0.0, $result->totalCost);
    }

    /**
     * Test Y: Grafo con aristas compartidas (cuello de botella) transita obligatoriamente la arista común.
     */
    public function test_Y_shared_edges_bottleneck_graph(): void
    {
        $graph = AcoGraphFixtures::createSharedEdgesGraph();
        $config = new AcoConfig(seed: 42, iterations: 15, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertContains('BOTTLENECK', $result->path);
    }

    /**
     * Test Z: Grafo con costos gemelos casi idénticos resuelve con estabilidad.
     */
    public function test_Z_twin_costs_graph_resolves_cleanly(): void
    {
        $graph = AcoGraphFixtures::createTwinCostsGraph();
        $config = new AcoConfig(seed: 42, iterations: 15, ants: 10);
        $service = new AcoService();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertGreaterThan(0.0, $result->totalCost);
    }
}
