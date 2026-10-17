<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Servicio de Ruta Más Corta Determinista de Referencia (Dijkstra Ponderado).
 *
 * Responsabilidad ingenieril:
 * Proporciona el punto de comparación teórico y metodológico de referencia (Baseline)
 * para evaluar el desempeño, convergencia y exactitud del algoritmo estocástico ACO.
 *
 * AVISO METODOLÓGICO:
 * Este componente actúa como patrón de referencia experimental para contrastar
 * el aporte ingenieril y calcular la métrica M2 (Gap porcentual respecto al óptimo).
 * Utiliza EXACTAMENTE la misma función de costo canónica y los mismos pesos (wD, wR)
 * que el motor ACO, garantizando la validez científica y comparabilidad directa.
 */
class ReferenceShortestPathService
{
    /**
     * Resuelve el camino de costo mínimo global determinista mediante el algoritmo de Dijkstra.
     *
     * @param Graph  $graph      Grafo dirigido con la red de tramos.
     * @param string $startNode  Identificador del nodo origen.
     * @param string $targetNode Identificador del nodo destino.
     * @param float  $wDistance  Ponderación asignada a la distancia (>= 0.0).
     * @param float  $wRisk      Ponderación asignada al riesgo (>= 0.0).
     * @return AcoResult Resultado estructurado con la solución óptima determinista.
     *
     * @throws InvalidArgumentException Si los nodos son inválidos o no existen en el grafo.
     */
    public function solve(
        Graph $graph,
        string $startNode,
        string $targetNode,
        float $wDistance = 0.5,
        float $wRisk = 0.5
    ): AcoResult {
        $startTime = microtime(true);

        $trimStart = trim($startNode);
        $trimTarget = trim($targetNode);

        if ($trimStart === '') {
            throw new InvalidArgumentException("El nodo origen no puede estar vacío.");
        }

        if ($trimTarget === '') {
            throw new InvalidArgumentException("El nodo destino no puede estar vacío.");
        }

        if (!$graph->hasNode($trimStart)) {
            throw new InvalidArgumentException("El nodo origen '{$trimStart}' no existe en el grafo.");
        }

        if (!$graph->hasNode($trimTarget)) {
            throw new InvalidArgumentException("El nodo destino '{$trimTarget}' no existe en el grafo.");
        }

        // Instanciar el calculador de costo con los mismos pesos
        $costCalc = new CostCalculator($wDistance, $wRisk);
        $maxDistance = $graph->getMaxDistance();
        $minDistance = $graph->getMinDistance();

        // Caso trivial: Origen == Destino
        if ($trimStart === $trimTarget) {
            $execTime = (microtime(true) - $startTime) * 1000.0;
            return new AcoResult(
                success: true,
                path: [$trimStart],
                totalCost: 0.0,
                totalDistance: 0.0,
                totalRisk: 0.0,
                executionTimeMs: $execTime,
                bestFoundIteration: 0,
                convergenceIteration: 0
            );
        }

        $nodes = $graph->getNodes();
        $dist = [];
        $prev = [];
        $visited = [];

        foreach ($nodes as $node) {
            $dist[$node] = INF;
            $prev[$node] = null;
            $visited[$node] = false;
        }

        $dist[$trimStart] = 0.0;

        // Bucle de relajación de aristas de Dijkstra
        while (true) {
            $u = null;
            $minDist = INF;

            // Selección del nodo no visitado con menor distancia acumulada
            foreach ($nodes as $node) {
                if (!$visited[$node] && $dist[$node] < $minDist) {
                    $minDist = $dist[$node];
                    $u = $node;
                }
            }

            // Si no quedan nodos alcanzables o la distancia mínima es infinita, se detiene
            if ($u === null || $dist[$u] === INF) {
                break;
            }

            // Si se alcanza el destino, se concluye la exploración determinista
            if ($u === $trimTarget) {
                break;
            }

            $visited[$u] = true;

            // Relajación de aristas salientes
            foreach ($graph->getOutgoingEdges($u) as $edge) {
                $v = $edge->getTo();
                if ($visited[$v]) {
                    continue;
                }

                $edgeCost = $costCalc->calculateEdgeCost($edge, $maxDistance, $minDistance);
                $alt = $dist[$u] + $edgeCost;

                if ($alt < $dist[$v]) {
                    $dist[$v] = $alt;
                    $prev[$v] = ['from' => $u, 'edge' => $edge];
                }
            }
        }

        // Si el destino permanece con distancia infinita, es inalcanzable
        if ($dist[$trimTarget] === INF) {
            $execTime = (microtime(true) - $startTime) * 1000.0;
            return new AcoResult(
                success: false,
                executionTimeMs: $execTime
            );
        }

        // Reconstrucción del camino óptimo y de la secuencia de aristas
        $path = [$trimTarget];
        $edges = [];
        $curr = $trimTarget;

        while ($curr !== $trimStart && isset($prev[$curr])) {
            $p = $prev[$curr];
            array_unshift($edges, $p['edge']);
            $curr = $p['from'];
            array_unshift($path, $curr);
        }

        $metrics = $costCalc->calculatePathMetrics($edges, $maxDistance, $minDistance);
        $execTime = (microtime(true) - $startTime) * 1000.0;

        return new AcoResult(
            success: true,
            path: $path,
            totalCost: $metrics['totalCost'],
            totalDistance: $metrics['totalDistance'],
            totalRisk: $metrics['totalRisk'],
            executionTimeMs: $execTime,
            bestFoundIteration: 0,
            convergenceIteration: 0
        );
    }
}
