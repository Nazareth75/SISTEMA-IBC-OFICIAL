<?php

namespace Tests\Fixtures;

use App\Services\Aco\Edge;
use App\Services\Aco\Graph;

/**
 * Generador de Grafos Sintéticos Controlados para Pruebas Unitarias del Motor ACO.
 *
 * NOTA METODOLÓGICA OBLIGATORIA:
 * Todos los grafos provistos en esta clase son exclusivamente sintéticos y han sido
 * diseñados como banco de pruebas de laboratorio para validar funcionalmente el aporte
 * ingenieril (ACO-2 a ACO-8).
 *
 * NO REPRESENTAN NI SIMULAN DATOS REALES DEL SISTEMA IBC.
 */
class AcoGraphFixtures
{
    /**
     * Escenario 1: Una única ruta válida (trayecto lineal sin bifurcaciones).
     * ORIGEN -> N1 -> N2 -> DESTINO
     */
    public static function createSinglePathGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'N1', 100.0, 0.2));
        $graph->addEdge(new Edge('N1', 'N2', 150.0, 0.3));
        $graph->addEdge(new Edge('N2', 'DESTINO', 120.0, 0.1));

        return $graph;
    }

    /**
     * Escenario 2: Varias rutas alternativas (grafo diamante con 2 opciones disjuntas).
     * Ruta 1: ORIGEN -> B1 -> DESTINO (200m, riesgo 0.4)
     * Ruta 2: ORIGEN -> B2 -> DESTINO (250m, riesgo 0.2)
     */
    public static function createAlternativePathsGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'B1', 100.0, 0.4));
        $graph->addEdge(new Edge('B1', 'DESTINO', 100.0, 0.4));

        $graph->addEdge(new Edge('ORIGEN', 'B2', 125.0, 0.2));
        $graph->addEdge(new Edge('B2', 'DESTINO', 125.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 3: Ruta corta pero riesgosa frente a ruta más larga y segura.
     * Corredor Corto/Riesgoso: ORIGEN -> R_CORTA -> DESTINO (Distancia 150m, Riesgo 0.90)
     * Corredor Largo/Seguro:   ORIGEN -> S1 -> S2 -> DESTINO (Distancia 600m, Riesgo 0.05)
     */
    public static function createShortRiskyVsLongSafeGraph(): Graph
    {
        $graph = new Graph();

        // Ruta Corta / Riesgo Alto (Distancia: 150m, Riesgo: 0.9)
        $graph->addEdge(new Edge('ORIGEN', 'R_CORTA', 75.0, 0.9));
        $graph->addEdge(new Edge('R_CORTA', 'DESTINO', 75.0, 0.9));

        // Ruta Larga / Riesgo Bajo (Distancia: 600m, Riesgo: 0.05)
        $graph->addEdge(new Edge('ORIGEN', 'S1', 200.0, 0.05));
        $graph->addEdge(new Edge('S1', 'S2', 200.0, 0.05));
        $graph->addEdge(new Edge('S2', 'DESTINO', 200.0, 0.05));

        return $graph;
    }

    /**
     * Escenario 4: Dos rutas con costos globales muy similares (prueba de sensibilidad y dispersión).
     * Ruta A: ORIGEN -> T1 -> DESTINO (200m, riesgo 0.30) -> C_norm ≈ 0.5 * (200/202) + 0.5 * 0.30 = 0.645
     * Ruta B: ORIGEN -> T2 -> DESTINO (202m, riesgo 0.29) -> C_norm ≈ 0.5 * (202/202) + 0.5 * 0.29 = 0.645
     */
    public static function createTwinCostsGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'T1', 100.0, 0.30));
        $graph->addEdge(new Edge('T1', 'DESTINO', 100.0, 0.30));

        $graph->addEdge(new Edge('ORIGEN', 'T2', 101.0, 0.29));
        $graph->addEdge(new Edge('T2', 'DESTINO', 101.0, 0.29));

        return $graph;
    }

    /**
     * Escenario 5: Ruta óptima claramente conocida y dominada en distancia y riesgo.
     * Ruta Óptima: ORIGEN -> OPT -> DESTINO (Distancia 100m, Riesgo 0.10)
     * Ruta Pésima: ORIGEN -> SUB -> DESTINO (Distancia 500m, Riesgo 0.80)
     */
    public static function createKnownOptimumGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'OPT', 50.0, 0.1));
        $graph->addEdge(new Edge('OPT', 'DESTINO', 50.0, 0.1));

        $graph->addEdge(new Edge('ORIGEN', 'SUB', 250.0, 0.8));
        $graph->addEdge(new Edge('SUB', 'DESTINO', 250.0, 0.8));

        return $graph;
    }

    /**
     * Escenario 6: Grafo con ciclos dirigidos (probar tabú y prevención de loops infinitos).
     * Ciclo: ORIGEN -> C1 -> C2 -> C1 (bucle dirigido entre C1 y C2).
     * Salida hacia destino: C2 -> DESTINO.
     */
    public static function createCyclicGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'C1', 100.0, 0.2));
        $graph->addEdge(new Edge('C1', 'C2', 100.0, 0.2));
        $graph->addEdge(new Edge('C2', 'C1', 100.0, 0.2)); // Ciclo de retorno a C1
        $graph->addEdge(new Edge('C2', 'DESTINO', 100.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 7: Grafo con nodos sin salida (dead-ends).
     * ORIGEN se bifurca hacia un callejón sin salida y hacia la salida válida.
     */
    public static function createDeadEndGraph(): Graph
    {
        $graph = new Graph();
        // Callejón sin salida
        $graph->addEdge(new Edge('ORIGEN', 'DEAD_1', 100.0, 0.1));
        $graph->addEdge(new Edge('DEAD_1', 'DEAD_2', 100.0, 0.1));

        // Ruta transitable hacia el destino
        $graph->addEdge(new Edge('ORIGEN', 'V1', 120.0, 0.2));
        $graph->addEdge(new Edge('V1', 'DESTINO', 120.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 8: Destino inalcanzable (grafo desconectado).
     * Componente A: ORIGEN -> A1 -> A2 (sin arista hacia DESTINO).
     * Componente B: DESTINO (nodo aislado o alcanzable solo desde fuera).
     */
    public static function createUnreachableGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'A1', 100.0, 0.2));
        $graph->addEdge(new Edge('A1', 'A2', 150.0, 0.3));
        $graph->addNode('DESTINO'); // Nodo destino presente pero sin aristas entrantes

        return $graph;
    }

    /**
     * Escenario 9: Múltiples caminos válidos (cuadrícula / red reticular 3x3).
     */
    public static function createMultiPathGridGraph(): Graph
    {
        $graph = new Graph();
        // Red tipo cuadrícula desde (0,0) [ORIGEN] hasta (2,2) [DESTINO]
        // Nodos: ORIGEN, N01, N02, N10, N11, N12, N20, N21, DESTINO
        $graph->addEdge(new Edge('ORIGEN', 'N01', 100.0, 0.2));
        $graph->addEdge(new Edge('ORIGEN', 'N10', 110.0, 0.15));

        $graph->addEdge(new Edge('N01', 'N02', 105.0, 0.25));
        $graph->addEdge(new Edge('N01', 'N11', 100.0, 0.20));

        $graph->addEdge(new Edge('N10', 'N11', 100.0, 0.18));
        $graph->addEdge(new Edge('N10', 'N20', 115.0, 0.10));

        $graph->addEdge(new Edge('N02', 'N12', 100.0, 0.30));
        $graph->addEdge(new Edge('N11', 'N12', 100.0, 0.22));
        $graph->addEdge(new Edge('N11', 'N21', 105.0, 0.15));

        $graph->addEdge(new Edge('N20', 'N21', 100.0, 0.12));

        $graph->addEdge(new Edge('N12', 'DESTINO', 100.0, 0.25));
        $graph->addEdge(new Edge('N21', 'DESTINO', 110.0, 0.15));

        return $graph;
    }

    /**
     * Escenario 10: Aristas compartidas entre diferentes soluciones (cuello de botella central).
     * O1 / O2 -> CUELLO -> D1 / DESTINO
     */
    public static function createSharedEdgesGraph(): Graph
    {
        $graph = new Graph();
        $graph->addEdge(new Edge('ORIGEN', 'PRE_BOTTLE', 80.0, 0.2));
        $graph->addEdge(new Edge('PRE_BOTTLE', 'BOTTLENECK', 150.0, 0.3)); // Arista compartida obligatoria
        $graph->addEdge(new Edge('BOTTLENECK', 'POST_A', 90.0, 0.1));
        $graph->addEdge(new Edge('BOTTLENECK', 'POST_B', 120.0, 0.4));
        $graph->addEdge(new Edge('POST_A', 'DESTINO', 70.0, 0.15));
        $graph->addEdge(new Edge('POST_B', 'DESTINO', 60.0, 0.35));

        return $graph;
    }

    /**
     * Escenario 11: Grafo pequeño controlado (6 nodos, 7 aristas).
     *
     * Ruta A (Corta / Riesgo Alto): ORIGEN -> C -> E -> DESTINO (Distancia 500m, Riesgo 2.6)
     * Ruta B (Larga / Riesgo Bajo): ORIGEN -> B -> D -> DESTINO (Distancia 1100m, Riesgo 0.3)
     * Ruta C (Intermedia):          ORIGEN -> B -> E -> DESTINO (Distancia 750m, Riesgo 1.3)
     */
    public static function createSmallGraph(): Graph
    {
        $graph = new Graph();

        $graph->addEdge(new Edge('ORIGEN', 'B', 400.0, 0.1));
        $graph->addEdge(new Edge('ORIGEN', 'C', 200.0, 0.9));

        $graph->addEdge(new Edge('B', 'D', 400.0, 0.1));
        $graph->addEdge(new Edge('B', 'E', 250.0, 0.4));

        $graph->addEdge(new Edge('C', 'E', 200.0, 0.9));

        $graph->addEdge(new Edge('D', 'DESTINO', 300.0, 0.1));
        $graph->addEdge(new Edge('E', 'DESTINO', 100.0, 0.8));

        return $graph;
    }

    /**
     * Escenario 12: Grafo complejo de benchmarking (17 nodos, 20 aristas).
     * Dispone de 3 corredores paralelos (Norte, Sur, Centro), interconexiones y callejones sin salida.
     */
    public static function createComplexGraph(): Graph
    {
        $graph = new Graph();

        // Corredor Norte: Corto pero muy riesgoso (Longitud 420m, Riesgo acumulado 3.4)
        $graph->addEdge(new Edge('ORIGEN', 'N1', 100.0, 0.85));
        $graph->addEdge(new Edge('N1', 'N2', 120.0, 0.90));
        $graph->addEdge(new Edge('N2', 'N3', 110.0, 0.80));
        $graph->addEdge(new Edge('N3', 'DESTINO', 90.0, 0.85));

        // Corredor Sur: Largo pero sumamente seguro (Longitud 1400m, Riesgo acumulado 0.25)
        $graph->addEdge(new Edge('ORIGEN', 'N4', 300.0, 0.05));
        $graph->addEdge(new Edge('N4', 'N5', 350.0, 0.05));
        $graph->addEdge(new Edge('N5', 'N6', 300.0, 0.05));
        $graph->addEdge(new Edge('N6', 'N7', 250.0, 0.05));
        $graph->addEdge(new Edge('N7', 'DESTINO', 200.0, 0.05));

        // Corredor Central: Reticular y balanceado
        $graph->addEdge(new Edge('ORIGEN', 'N8', 200.0, 0.25));
        $graph->addEdge(new Edge('N8', 'N9', 180.0, 0.30));
        $graph->addEdge(new Edge('N8', 'N10', 220.0, 0.20));
        $graph->addEdge(new Edge('N9', 'N11', 190.0, 0.30));
        $graph->addEdge(new Edge('N10', 'N11', 150.0, 0.25));
        $graph->addEdge(new Edge('N11', 'N12', 200.0, 0.30));
        $graph->addEdge(new Edge('N12', 'DESTINO', 180.0, 0.25));

        // Interconexiones entre corredores
        $graph->addEdge(new Edge('N9', 'N2', 150.0, 0.60));
        $graph->addEdge(new Edge('N10', 'N5', 160.0, 0.20));

        // Callejones sin salida
        $graph->addEdge(new Edge('N8', 'DEAD_END_1', 150.0, 0.50));
        $graph->addEdge(new Edge('N11', 'DEAD_END_2', 120.0, 0.70));
        $graph->addEdge(new Edge('N4', 'DEAD_END_3', 200.0, 0.10));

        return $graph;
    }

    /**
     * Escenario 13: Grafo sintético de gran escala (35 nodos estructurados en capas).
     *
     * @param int $layers Cantidad de capas intermedias (por defecto 10 capas x 3 nodos = 30 + origen + destino = 32+).
     */
    public static function createLargeGraph(int $layers = 10): Graph
    {
        $graph = new Graph();
        $graph->addNode('ORIGEN');
        $graph->addNode('DESTINO');

        // Conectar ORIGEN a la primera capa (L1_A, L1_B, L1_C)
        $graph->addEdge(new Edge('ORIGEN', 'L1_A', 100.0, 0.2));
        $graph->addEdge(new Edge('ORIGEN', 'L1_B', 150.0, 0.1));
        $graph->addEdge(new Edge('ORIGEN', 'L1_C', 80.0, 0.5));

        for ($l = 1; $l < $layers; $l++) {
            $next = $l + 1;
            // Capa L a L+1
            $graph->addEdge(new Edge("L{$l}_A", "L{$next}_A", 100.0, 0.2));
            $graph->addEdge(new Edge("L{$l}_A", "L{$next}_B", 120.0, 0.15));
            $graph->addEdge(new Edge("L{$l}_B", "L{$next}_B", 130.0, 0.1));
            $graph->addEdge(new Edge("L{$l}_B", "L{$next}_C", 110.0, 0.3));
            $graph->addEdge(new Edge("L{$l}_C", "L{$next}_C", 90.0, 0.6));
            $graph->addEdge(new Edge("L{$l}_C", "L{$next}_A", 140.0, 0.25));
        }

        // Conectar última capa hacia DESTINO
        $last = $layers;
        $graph->addEdge(new Edge("L{$last}_A", 'DESTINO', 100.0, 0.2));
        $graph->addEdge(new Edge("L{$last}_B", 'DESTINO', 110.0, 0.1));
        $graph->addEdge(new Edge("L{$last}_C", 'DESTINO', 90.0, 0.4));

        return $graph;
    }
}
