<?php

namespace Tests\Unit;

use App\Services\Aco\Arista;
use App\Services\Aco\CalculadorCosto;
use App\Services\Aco\ConfiguracionAco;
use App\Services\Aco\Grafo;
use App\Services\Aco\ServicioAco;
use App\Services\Aco\ServicioExperimentosAco;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Suite de Pruebas de Robustez con Entradas Malignas, Inválidas y Casos Extremos.
 *
 * NOTA METODOLÓGICA:
 * Los datos evaluados en esta suite NO representan información real del sistema IBC.
 * Son entradas deliberadamente defectuosas diseñadas para intentar quebrar la implementación,
 * verificar la solidez de las invariantes y confirmar que ningún error se propaga en silencio
 * ni produce divisiones por cero, NaN o bucles infinitos.
 */
class RobustezAcoTest extends TestCase
{
    // ==========================================
    // 1. TOPOLOGÍA Y GRAFOS INVÁLIDOS
    // ==========================================

    public function test_empty_graph_throws_exception_on_solve(): void
    {
        $graph = new Grafo();
        $service = new ServicioAco();

        $this->expectException(InvalidArgumentException::class);
        $service->solve($graph, 'ORIGEN', 'DESTINO');
    }

    public function test_empty_edges_returns_failure_cleanly_when_nodes_exist(): void
    {
        $graph = new Grafo();
        $graph->addNode('ORIGEN');
        $graph->addNode('DESTINO');

        $service = new ServicioAco();
        $result = $service->solve($graph, 'ORIGEN', 'DESTINO');

        $this->assertFalse($result->success);
        $this->assertEmpty($result->path);
        $this->assertEquals(0.0, $result->totalCost);
    }

    public function test_non_existent_start_node_throws_exception(): void
    {
        $graph = new Grafo();
        $graph->addNode('DESTINO');
        $service = new ServicioAco();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("El nodo origen 'FANTASMA' no existe");
        $service->solve($graph, 'FANTASMA', 'DESTINO');
    }

    public function test_non_existent_target_node_throws_exception(): void
    {
        $graph = new Grafo();
        $graph->addNode('ORIGEN');
        $service = new ServicioAco();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("El nodo destino 'INEXISTENTE' no existe");
        $service->solve($graph, 'ORIGEN', 'INEXISTENTE');
    }

    public function test_empty_node_names_rejected_in_graph(): void
    {
        $graph = new Grafo();
        $this->expectException(InvalidArgumentException::class);
        $graph->addNode('   ');
    }

    public function test_duplicate_edges_in_graph_throws_exception(): void
    {
        $graph = new Grafo();
        $edge1 = new Arista('A', 'B', 100.0, 0.2);
        $edge2 = new Arista('A', 'B', 150.0, 0.4);

        $graph->addEdge($edge1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Arista duplicada detectada");
        $graph->addEdge($edge2);
    }

    // ==========================================
    // 2. ARISTAS CON DATOS MALIGNOS
    // ==========================================

    public function test_edge_with_empty_from_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('', 'B', 100.0, 0.2);
    }

    public function test_edge_with_empty_to_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('A', '   ', 100.0, 0.2);
    }

    public function test_edge_self_loop_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("no puede conectar un nodo consigo mismo");
        new Arista('MISMO_NODO', 'MISMO_NODO', 100.0, 0.2);
    }

    public function test_edge_negative_distance_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("estrictamente positiva");
        new Arista('A', 'B', -50.0, 0.2);
    }

    public function test_edge_zero_distance_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("estrictamente positiva");
        new Arista('A', 'B', 0.0, 0.2);
    }

    public function test_edge_negative_risk_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("intervalo cerrado [0.0, 1.0]");
        new Arista('A', 'B', 100.0, -0.1);
    }

    public function test_edge_risk_greater_than_one_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("intervalo cerrado [0.0, 1.0]");
        new Arista('A', 'B', 100.0, 1.05);
    }

    public function test_edge_nan_distance_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('A', 'B', NAN, 0.5);
    }

    public function test_edge_infinite_distance_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('A', 'B', INF, 0.5);
    }

    public function test_edge_nan_risk_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('A', 'B', 100.0, NAN);
    }

    public function test_edge_infinite_risk_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Arista('A', 'B', 100.0, INF);
    }

    // ==========================================
    // 3. COST CALCULATOR Y NORMALIZACIÓN
    // ==========================================

    public function test_negative_w_distance_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorCosto(-0.2, 1.2);
    }

    public function test_negative_w_risk_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorCosto(1.1, -0.1);
    }

    public function test_weights_sum_not_one_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorCosto(0.6, 0.6); // 1.2 != 1.0
    }

    public function test_nan_weights_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorCosto(NAN, 0.5);
    }

    public function test_infinite_weights_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorCosto(INF, 0.5);
    }

    public function test_normalizer_division_by_zero_protection_when_max_is_zero(): void
    {
        $calc = new CalculadorCosto(0.5, 0.5);
        $norm = $calc->calculateNormalizedDistance(100.0, 0.0);
        $this->assertEquals(0.0, $norm);
        $this->assertFalse(is_nan($norm));
        $this->assertFalse(is_infinite($norm));
    }

    // ==========================================
    // 4. CONFIGURACIÓN ACO INVÁLIDA (ConfiguracionAco)
    // ==========================================

    public function test_negative_alpha_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(alpha: -1.0);
    }

    public function test_negative_beta_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(beta: -0.5);
    }

    public function test_rho_negative_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(rho: -0.01);
    }

    public function test_rho_greater_than_one_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(rho: 1.01);
    }

    public function test_q_zero_or_negative_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(Q: 0.0);
    }

    public function test_zero_ants_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(ants: 0);
    }

    public function test_negative_ants_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(ants: -5);
    }

    public function test_zero_iterations_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(iterations: 0);
    }

    public function test_negative_iterations_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(iterations: -10);
    }

    public function test_zero_or_negative_initial_pheromone_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(initialPheromone: 0.0);
    }

    public function test_negative_convergence_epsilon_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(convergenceEpsilon: -0.001);
    }

    public function test_zero_or_negative_patience_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(convergencePatience: 0);
    }

    public function test_nan_in_config_floats_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(alpha: NAN);
    }

    public function test_inf_in_config_floats_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConfiguracionAco(Q: INF);
    }

    // ==========================================
    // 5. PROTECCIÓN CONTRA DIVISIÓN POR CERO EN MÉTRICAS
    // ==========================================

    public function test_gap_calculation_returns_null_when_ref_cost_is_zero(): void
    {
        $gap1 = ServicioExperimentosAco::calculateGap(0.0, 0.0);
        $this->assertNull($gap1, "Gap con C_ref=0 debe ser null (indefinido)");

        $gap2 = ServicioExperimentosAco::calculateGap(1.5, 0.0);
        $this->assertNull($gap2, "Gap con C_ref=0 debe ser null (indefinido)");
    }

    public function test_coefficient_of_variation_returns_null_when_mean_is_zero(): void
    {
        $cv = ServicioExperimentosAco::calculateCoefficientOfVariation(0.0, 0.0);
        $this->assertNull($cv, "CV con media=0 debe ser null (indefinido)");
    }

    public function test_risk_reduction_returns_null_when_shortest_risk_is_zero(): void
    {
        $rr = ServicioExperimentosAco::calculateRiskReduction(0.0, 0.0);
        $this->assertNull($rr, "Reducción de riesgo con R_corta=0 debe ser null (indefinido)");
    }

    public function test_distance_increase_returns_null_when_shortest_distance_is_zero(): void
    {
        $id = ServicioExperimentosAco::calculateDistanceIncrease(0.0, 0.0);
        $this->assertNull($id, "Incremento de distancia con D_corta=0 debe ser null (indefinido)");
    }

    public function test_experiment_service_rejects_zero_or_negative_runs_count(): void
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('A', 'B', 10.0, 0.1));
        $config = new ConfiguracionAco();
        $expService = new ServicioExperimentosAco();

        $this->expectException(InvalidArgumentException::class);
        $expService->runExperiment($graph, 'A', 'B', $config, runsCount: 0);
    }

    // ==========================================
    // 6. COMPORTAMIENTO ANTE ESCALAS EXTREMAS Y BLOQUEOS
    // ==========================================

    public function test_extremely_large_distances_do_not_overflow(): void
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'N1', 1e8, 0.1));
        $graph->addEdge(new Arista('N1', 'DESTINO', 2e8, 0.2));

        $config = new ConfiguracionAco(seed: 42, iterations: 5, ants: 3);
        $service = new ServicioAco();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertTrue($result->success);
        $this->assertEqualsWithDelta(3e8, $result->totalDistance, 1.0);
        $this->assertFalse(is_nan($result->totalCost));
        $this->assertFalse(is_infinite($result->totalCost));
    }

    public function test_all_ants_stuck_in_dead_ends_terminates_gracefully(): void
    {
        // Grafo donde desde ORIGEN sólo hay un callejón sin salida y no existe camino a DESTINO
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'DEAD_1', 100.0, 0.1));
        $graph->addEdge(new Arista('DEAD_1', 'DEAD_2', 100.0, 0.1));
        $graph->addNode('DESTINO');

        $config = new ConfiguracionAco(seed: 42, iterations: 10, ants: 5);
        $service = new ServicioAco();

        $result = $service->solve($graph, 'ORIGEN', 'DESTINO', $config);

        $this->assertFalse($result->success);
        $this->assertEmpty($result->path);
        $this->assertEquals(0.0, $result->totalCost);
    }
}
