<?php

namespace Tests\Fixtures;

use App\Services\Aco\Arista;
use App\Services\Aco\Grafo;

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
class EscenariosGrafoAco
{
    /**
     * Escenario 1: Una única ruta válida (trayecto lineal sin bifurcaciones).
     * ORIGEN -> N1 -> N2 -> DESTINO
     */
    public static function createSinglePathGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'N1', 100.0, 0.2));
        $graph->addEdge(new Arista('N1', 'N2', 150.0, 0.3));
        $graph->addEdge(new Arista('N2', 'DESTINO', 120.0, 0.1));

        return $graph;
    }

    /**
     * Escenario 2: Varias rutas alternativas (grafo diamante con 2 opciones disjuntas).
     * Ruta 1: ORIGEN -> B1 -> DESTINO (200m, riesgo 0.4)
     * Ruta 2: ORIGEN -> B2 -> DESTINO (250m, riesgo 0.2)
     */
    public static function createAlternativePathsGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'B1', 100.0, 0.4));
        $graph->addEdge(new Arista('B1', 'DESTINO', 100.0, 0.4));

        $graph->addEdge(new Arista('ORIGEN', 'B2', 125.0, 0.2));
        $graph->addEdge(new Arista('B2', 'DESTINO', 125.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 3: Ruta corta pero riesgosa frente a ruta más larga y segura.
     * Corredor Corto/Riesgoso: ORIGEN -> R_CORTA -> DESTINO (Distancia 150m, Riesgo 0.90)
     * Corredor Largo/Seguro:   ORIGEN -> S1 -> S2 -> DESTINO (Distancia 600m, Riesgo 0.05)
     */
    public static function createShortRiskyVsLongSafeGraph(): Grafo
    {
        $graph = new Grafo();

        // Ruta Corta / Riesgo Alto (Distancia: 150m, Riesgo: 0.9)
        $graph->addEdge(new Arista('ORIGEN', 'R_CORTA', 75.0, 0.9));
        $graph->addEdge(new Arista('R_CORTA', 'DESTINO', 75.0, 0.9));

        // Ruta Larga / Riesgo Bajo (Distancia: 600m, Riesgo: 0.05)
        $graph->addEdge(new Arista('ORIGEN', 'S1', 200.0, 0.05));
        $graph->addEdge(new Arista('S1', 'S2', 200.0, 0.05));
        $graph->addEdge(new Arista('S2', 'DESTINO', 200.0, 0.05));

        return $graph;
    }

    /**
     * Escenario 4: Dos rutas con costos globales muy similares (prueba de sensibilidad y dispersión).
     * Ruta A: ORIGEN -> T1 -> DESTINO (200m, riesgo 0.30)
     * Ruta B: ORIGEN -> T2 -> DESTINO (202m, riesgo 0.29)
     */
    public static function createTwinCostsGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'T1', 100.0, 0.30));
        $graph->addEdge(new Arista('T1', 'DESTINO', 100.0, 0.30));

        $graph->addEdge(new Arista('ORIGEN', 'T2', 101.0, 0.29));
        $graph->addEdge(new Arista('T2', 'DESTINO', 101.0, 0.29));

        return $graph;
    }

    /**
     * Escenario 5: Ruta óptima claramente conocida y dominada en distancia y riesgo.
     * Ruta Óptima: ORIGEN -> OPT -> DESTINO (Distancia 100m, Riesgo 0.10)
     * Ruta Pésima: ORIGEN -> SUB -> DESTINO (Distancia 500m, Riesgo 0.80)
     */
    public static function createKnownOptimumGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'OPT', 50.0, 0.1));
        $graph->addEdge(new Arista('OPT', 'DESTINO', 50.0, 0.1));

        $graph->addEdge(new Arista('ORIGEN', 'SUB', 250.0, 0.8));
        $graph->addEdge(new Arista('SUB', 'DESTINO', 250.0, 0.8));

        return $graph;
    }

    /**
     * Escenario 6: Grafo con ciclos dirigidos (probar tabú y prevención de loops infinitos).
     * Ciclo: ORIGEN -> C1 -> C2 -> C1 (bucle dirigido entre C1 y C2).
     * Salida hacia destino: C2 -> DESTINO.
     */
    public static function createCyclicGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'C1', 100.0, 0.2));
        $graph->addEdge(new Arista('C1', 'C2', 100.0, 0.2));
        $graph->addEdge(new Arista('C2', 'C1', 100.0, 0.2)); // Ciclo de retorno a C1
        $graph->addEdge(new Arista('C2', 'DESTINO', 100.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 7: Grafo con nodos sin salida (dead-ends).
     * ORIGEN se bifurca hacia un callejón sin salida y hacia la salida válida.
     */
    public static function createDeadEndGraph(): Grafo
    {
        $graph = new Grafo();
        // Callejón sin salida
        $graph->addEdge(new Arista('ORIGEN', 'DEAD_1', 100.0, 0.1));
        $graph->addEdge(new Arista('DEAD_1', 'DEAD_2', 100.0, 0.1));

        // Ruta transitable hacia el destino
        $graph->addEdge(new Arista('ORIGEN', 'V1', 120.0, 0.2));
        $graph->addEdge(new Arista('V1', 'DESTINO', 120.0, 0.2));

        return $graph;
    }

    /**
     * Escenario 8: Destino inalcanzable (grafo desconectado).
     * Componente A: ORIGEN -> A1 -> A2 (sin arista hacia DESTINO).
     * Componente B: DESTINO (nodo aislado o alcanzable solo desde fuera).
     */
    public static function createUnreachableGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'A1', 100.0, 0.2));
        $graph->addEdge(new Arista('A1', 'A2', 150.0, 0.3));
        $graph->addNode('DESTINO'); // Nodo destino presente pero sin aristas entrantes

        return $graph;
    }

    /**
     * Escenario 9: Múltiples caminos válidos (cuadrícula / red reticular 3x3).
     */
    public static function createMultiPathGridGraph(): Grafo
    {
        $graph = new Grafo();
        // Red tipo cuadrícula desde (0,0) [ORIGEN] hasta (2,2) [DESTINO]
        $graph->addEdge(new Arista('ORIGEN', 'N01', 100.0, 0.2));
        $graph->addEdge(new Arista('ORIGEN', 'N10', 110.0, 0.15));

        $graph->addEdge(new Arista('N01', 'N02', 105.0, 0.25));
        $graph->addEdge(new Arista('N01', 'N11', 100.0, 0.20));

        $graph->addEdge(new Arista('N10', 'N11', 100.0, 0.18));
        $graph->addEdge(new Arista('N10', 'N20', 115.0, 0.10));

        $graph->addEdge(new Arista('N02', 'N12', 100.0, 0.30));
        $graph->addEdge(new Arista('N11', 'N12', 100.0, 0.22));
        $graph->addEdge(new Arista('N11', 'N21', 105.0, 0.15));

        $graph->addEdge(new Arista('N20', 'N21', 100.0, 0.12));

        $graph->addEdge(new Arista('N12', 'DESTINO', 100.0, 0.25));
        $graph->addEdge(new Arista('N21', 'DESTINO', 110.0, 0.15));

        return $graph;
    }

    /**
     * Escenario 10: Aristas compartidas entre diferentes soluciones (cuello de botella central).
     */
    public static function createSharedEdgesGraph(): Grafo
    {
        $graph = new Grafo();
        $graph->addEdge(new Arista('ORIGEN', 'PRE_BOTTLE', 80.0, 0.2));
        $graph->addEdge(new Arista('PRE_BOTTLE', 'BOTTLENECK', 150.0, 0.3)); // Arista compartida obligatoria
        $graph->addEdge(new Arista('BOTTLENECK', 'POST_A', 90.0, 0.1));
        $graph->addEdge(new Arista('BOTTLENECK', 'POST_B', 120.0, 0.4));
        $graph->addEdge(new Arista('POST_A', 'DESTINO', 70.0, 0.15));
        $graph->addEdge(new Arista('POST_B', 'DESTINO', 60.0, 0.35));

        return $graph;
    }

    /**
     * Escenario 11: Grafo pequeño controlado (6 nodos, 7 aristas).
     *
     * Ruta A (Corta / Riesgo Alto): ORIGEN -> C -> E -> DESTINO (Distancia 500m, Riesgo 2.6)
     * Ruta B (Larga / Riesgo Bajo): ORIGEN -> B -> D -> DESTINO (Distancia 1100m, Riesgo 0.3)
     * Ruta C (Intermedia):          ORIGEN -> B -> E -> DESTINO (Distancia 750m, Riesgo 1.3)
     * Dmax = 400.0.
     * Bajo D/Dmax con wD=0.5, wR=0.5:
     * - Ruta B (B-D-DESTINO): C = 0.5*(1100/400) + 0.5*0.3 = 1.375 + 0.15 = 1.5250
     * - Ruta C (B-E-DESTINO): C = 0.5*(750/400) + 0.5*1.3 = 0.9375 + 0.65 = 1.5875
     * - Ruta A (C-E-DESTINO): C = 0.5*(500/400) + 0.5*2.6 = 0.625 + 1.30 = 1.9250
     * Por tanto, la ruta de menor costo canónico es la Ruta B con C = 1.5250.
     */
    public static function createSmallGraph(): Grafo
    {
        $graph = new Grafo();

        $graph->addEdge(new Arista('ORIGEN', 'B', 400.0, 0.1));
        $graph->addEdge(new Arista('ORIGEN', 'C', 200.0, 0.9));

        $graph->addEdge(new Arista('B', 'D', 400.0, 0.1));
        $graph->addEdge(new Arista('B', 'E', 250.0, 0.4));

        $graph->addEdge(new Arista('C', 'E', 200.0, 0.9));

        $graph->addEdge(new Arista('D', 'DESTINO', 300.0, 0.1));
        $graph->addEdge(new Arista('E', 'DESTINO', 100.0, 0.8));

        return $graph;
    }

    /**
     * Escenario 12: Grafo complejo de benchmarking (17 nodos, 20 aristas).
     * Dispone de 3 corredores paralelos (Norte, Sur, Centro), interconexiones y callejones sin salida.
     */
    public static function createComplexGraph(): Grafo
    {
        $graph = new Grafo();

        // Corredor Norte: Corto pero muy riesgoso (Longitud 420m, Riesgo acumulado 3.4)
        $graph->addEdge(new Arista('ORIGEN', 'N1', 100.0, 0.85));
        $graph->addEdge(new Arista('N1', 'N2', 120.0, 0.90));
        $graph->addEdge(new Arista('N2', 'N3', 110.0, 0.80));
        $graph->addEdge(new Arista('N3', 'DESTINO', 90.0, 0.85));

        // Corredor Sur: Largo pero sumamente seguro (Longitud 1400m, Riesgo acumulado 0.25)
        $graph->addEdge(new Arista('ORIGEN', 'N4', 300.0, 0.05));
        $graph->addEdge(new Arista('N4', 'N5', 350.0, 0.05));
        $graph->addEdge(new Arista('N5', 'N6', 300.0, 0.05));
        $graph->addEdge(new Arista('N6', 'N7', 250.0, 0.05));
        $graph->addEdge(new Arista('N7', 'DESTINO', 200.0, 0.05));

        // Corredor Central: Reticular y balanceado
        $graph->addEdge(new Arista('ORIGEN', 'N8', 200.0, 0.25));
        $graph->addEdge(new Arista('N8', 'N9', 180.0, 0.30));
        $graph->addEdge(new Arista('N8', 'N10', 220.0, 0.20));
        $graph->addEdge(new Arista('N9', 'N11', 190.0, 0.30));
        $graph->addEdge(new Arista('N10', 'N11', 150.0, 0.25));
        $graph->addEdge(new Arista('N11', 'N12', 200.0, 0.30));
        $graph->addEdge(new Arista('N12', 'DESTINO', 180.0, 0.25));

        // Interconexiones entre corredores
        $graph->addEdge(new Arista('N9', 'N2', 150.0, 0.60));
        $graph->addEdge(new Arista('N10', 'N5', 160.0, 0.20));

        // Callejones sin salida
        $graph->addEdge(new Arista('N8', 'DEAD_END_1', 150.0, 0.50));
        $graph->addEdge(new Arista('N11', 'DEAD_END_2', 120.0, 0.70));
        $graph->addEdge(new Arista('N4', 'DEAD_END_3', 200.0, 0.10));

        return $graph;
    }

    /**
     * Escenario 13: Grafo sintético de gran escala (35 nodos estructurados en capas).
     */
    public static function createLargeGraph(int $layers = 10): Grafo
    {
        $graph = new Grafo();
        $graph->addNode('ORIGEN');
        $graph->addNode('DESTINO');

        // Conectar ORIGEN a la primera capa (L1_A, L1_B, L1_C)
        $graph->addEdge(new Arista('ORIGEN', 'L1_A', 100.0, 0.2));
        $graph->addEdge(new Arista('ORIGEN', 'L1_B', 150.0, 0.1));
        $graph->addEdge(new Arista('ORIGEN', 'L1_C', 80.0, 0.5));

        for ($l = 1; $l < $layers; $l++) {
            $next = $l + 1;
            // Capa L a L+1
            $graph->addEdge(new Arista("L{$l}_A", "L{$next}_A", 100.0, 0.2));
            $graph->addEdge(new Arista("L{$l}_A", "L{$next}_B", 120.0, 0.15));
            $graph->addEdge(new Arista("L{$l}_B", "L{$next}_B", 130.0, 0.1));
            $graph->addEdge(new Arista("L{$l}_B", "L{$next}_C", 110.0, 0.3));
            $graph->addEdge(new Arista("L{$l}_C", "L{$next}_C", 90.0, 0.6));
            $graph->addEdge(new Arista("L{$l}_C", "L{$next}_A", 140.0, 0.25));
        }

        // Conectar última capa hacia DESTINO
        $last = $layers;
        $graph->addEdge(new Arista("L{$last}_A", 'DESTINO', 100.0, 0.2));
        $graph->addEdge(new Arista("L{$last}_B", 'DESTINO', 110.0, 0.1));
        $graph->addEdge(new Arista("L{$last}_C", 'DESTINO', 90.0, 0.4));

        return $graph;
    }

    /**
     * Escenario 14 (Auditoría de Normalización Aditiva):
     * Dos rutas con idéntica distancia física total (200m) pero diferente número de aristas.
     * Ruta A: 1 arista de 200m.
     * Ruta B: 2 aristas de 100m.
     */
    public static function createEqualDistanceDifferentEdgeCountGraph(): Grafo
    {
        $graph = new Grafo();
        // Ruta A: 1 arista de 200m
        $graph->addEdge(new Arista('ORIGEN', 'DEST_A', 200.0, 0.1));

        // Ruta B: 2 aristas de 100m
        $graph->addEdge(new Arista('ORIGEN', 'MID_B', 100.0, 0.05));
        $graph->addEdge(new Arista('MID_B', 'DEST_B', 100.0, 0.05));

        return $graph;
    }
}
