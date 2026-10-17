<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Núcleo del Algoritmo de Optimización por Colonia de Hormigas (ACO) para Navegación Peatonal.
 *
 * MÓDULOS DE INGENIERÍA IMPLEMENTADOS:
 * - ACO-3: Construcción de soluciones paso a paso por hormigas artificiales con memoria tabú.
 * - ACO-4: Regla de transición probabilística (ruleta ponderada con feromona y heurística).
 * - ACO-5: Dinámica de feromonas (evaporación uniforme y depósito inversamente proporcional al costo).
 * - ACO-6: Elitismo y preservación de la mejor solución global encontrada.
 * - ACO-7: Detección formal de convergencia y estabilidad de soluciones.
 *
 * FÓRMULAS MATEMÁTICAS CLAVE:
 * 1. Heurística local de visibilidad:
 *    eta(i,j) = 1 / (C(i,j) + epsilon_heur)
 *    Favorece tramos de menor costo inmediato sin permitir división por cero.
 *
 * 2. Regla de transición probabilística (Random Proportional Rule):
 *    P(i,j) = [tau(i,j)^alpha * eta(i,j)^beta] / [Sum_k (tau(i,k)^alpha * eta(i,k)^beta)]
 *    Para todos los vecinos k transitables no visitados previamente.
 *
 * 3. Evaporación global de feromona:
 *    tau(i,j) = max(tau_min, (1 - rho) * tau(i,j))
 *    Disipa feromona residual para evitar que decisiones pasadas dominen indefinidamente.
 *
 * 4. Depósito de feromona:
 *    Delta_tau = Q / max(epsilon, Costo_Ruta)
 *    Premia con mayor intensidad las rutas con menor costo integral multiobjetivo.
 */
class AcoService
{
    /**
     * Configuración por defecto utilizada en ausencia de parámetros específicos.
     */
    private AcoConfig $defaultConfig;

    /**
     * Epsilon matemático para proteger heurística y depósitos contra división por cero.
     */
    private const EPSILON_HEURISTIC = 1e-6;

    /**
     * Cota inferior mínima para feromonas para evitar underflow numérico a cero absoluto.
     */
    private const MIN_PHEROMONE = 1e-9;

    /**
     * Constructor del servicio ACO.
     *
     * @param AcoConfig|null $config Configuración base opcional.
     */
    public function __construct(?AcoConfig $config = null)
    {
        $this->defaultConfig = $config ?? new AcoConfig();
    }

    /**
     * Resuelve la ruta óptima/subóptima entre dos nodos del grafo mediante la colonia de hormigas.
     *
     * @param Graph          $graph          Grafo dirigido con la red de tramos y costos.
     * @param string         $startNode      Identificador del nodo origen.
     * @param string         $targetNode     Identificador del nodo destino.
     * @param AcoConfig|null $overrideConfig Configuración específica opcional para esta ejecución.
     * @return AcoResult Resultado estructurado con métricas completas de la solución.
     *
     * @throws InvalidArgumentException Si los identificadores de nodos son vacíos o inexistentes en el grafo.
     */
    public function solve(
        Graph $graph,
        string $startNode,
        string $targetNode,
        ?AcoConfig $overrideConfig = null
    ): AcoResult {
        $startTime = microtime(true);
        $config = $overrideConfig ?? $this->defaultConfig;

        $trimStart = trim($startNode);
        $trimTarget = trim($targetNode);

        if ($trimStart === '') {
            throw new InvalidArgumentException("El nodo origen de la búsqueda no puede estar vacío.");
        }

        if ($trimTarget === '') {
            throw new InvalidArgumentException("El nodo destino de la búsqueda no puede estar vacío.");
        }

        // Validación de existencia de nodos en el grafo
        if (!$graph->hasNode($trimStart)) {
            throw new InvalidArgumentException(
                "El nodo origen '{$trimStart}' no existe en el grafo proporcionado."
            );
        }

        if (!$graph->hasNode($trimTarget)) {
            throw new InvalidArgumentException(
                "El nodo destino '{$trimTarget}' no existe en el grafo proporcionado."
            );
        }

        // Semilla para reproducibilidad experimental estocástica
        if ($config->seed !== null) {
            mt_srand($config->seed);
        }

        // Caso trivial: Origen y destino son el mismo punto (costo cero, sin transitar aristas)
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
                convergenceIteration: 0,
                bestCostHistory: array_fill(0, $config->iterations, 0.0)
            );
        }

        $allEdges = $graph->getEdges();
        // Si el grafo no tiene aristas y origen != destino, el destino es inalcanzable de forma controlada
        if (empty($allEdges)) {
            $execTime = (microtime(true) - $startTime) * 1000.0;
            return new AcoResult(
                success: false,
                executionTimeMs: $execTime,
                bestCostHistory: array_fill(0, $config->iterations, 0.0)
            );
        }

        $maxDistance = $graph->getMaxDistance();
        $minDistance = $graph->getMinDistance();
        $costCalc = new CostCalculator($config->wDistance, $config->wRisk);

        // Inicialización homogénea de feromona tau en todas las aristas
        $pheromones = [];
        foreach ($allEdges as $edge) {
            $pheromones[$edge->getId()] = $config->initialPheromone;
        }

        $globalBestPath = [];
        $globalBestCost = INF;
        $globalBestDistance = 0.0;
        $globalBestRisk = 0.0;
        $bestFoundIteration = 0;
        $convergenceIteration = 0;
        $bestCostHistory = [];
        $patienceCounter = 0;
        $convergedRecorded = false;

        // Bucle principal de iteraciones de la colonia
        for ($it = 1; $it <= $config->iterations; $it++) {
            $iterationSolutions = [];

            // Construcción de rutas por cada hormiga de la población
            for ($a = 0; $a < $config->ants; $a++) {
                $currentNode = $trimStart;
                $antPath = [$trimStart];
                $antEdges = [];
                // Conjunto tabú de nodos visitados para estricta prevención de ciclos
                $visited = [$trimStart => true];
                $stuck = false;

                while ($currentNode !== $trimTarget) {
                    $outgoing = $graph->getOutgoingEdges($currentNode);
                    $candidates = [];

                    // Filtrado de vecinos no visitados para evitar ciclos cerrados
                    foreach ($outgoing as $edge) {
                        if (!isset($visited[$edge->getTo()])) {
                            $candidates[] = $edge;
                        }
                    }

                    // Si no existen alternativas válidas no visitadas, la hormiga se detiene de forma controlada
                    if (empty($candidates)) {
                        $stuck = true;
                        break;
                    }

                    // Evaluación de deseabilidad de cada tramo candidato
                    $desirabilities = [];
                    $totalDesirability = 0.0;

                    foreach ($candidates as $edge) {
                        $edgeCost = $costCalc->calculateEdgeCost($edge, $maxDistance, $minDistance);
                        // Heurística eta: inversa del costo para preferir tramos más eficientes y seguros
                        $eta = 1.0 / ($edgeCost + self::EPSILON_HEURISTIC);
                        $tau = $pheromones[$edge->getId()] ?? $config->initialPheromone;

                        // Deseabilidad combinada según pesos exponenciales alpha y beta
                        $des = pow($tau, $config->alpha) * pow($eta, $config->beta);

                        if (is_nan($des) || is_infinite($des) || $des < 0.0) {
                            $des = 0.0;
                        }

                        $desirabilities[] = $des;
                        $totalDesirability += $des;
                    }

                    // Si la deseabilidad total acumulada es nula, no es posible decidir probabilísticamente
                    if ($totalDesirability <= 0.0) {
                        $stuck = true;
                        break;
                    }

                    // Selección estocástica por ruleta proporcional (Random Proportional Rule)
                    $randFraction = mt_rand() / mt_getrandmax();
                    $randVal = $randFraction * $totalDesirability;
                    $accum = 0.0;
                    $chosenEdge = $candidates[count($candidates) - 1]; // Fallback al último candidato válido

                    foreach ($candidates as $idx => $edge) {
                        $accum += $desirabilities[$idx];
                        if ($accum >= $randVal) {
                            $chosenEdge = $edge;
                            break;
                        }
                    }

                    // Avance hacia el nodo seleccionado y actualización del conjunto tabú
                    $antEdges[] = $chosenEdge;
                    $currentNode = $chosenEdge->getTo();
                    $antPath[] = $currentNode;
                    $visited[$currentNode] = true;
                }

                // Si la hormiga alcanzó el destino sin quedar bloqueada, se registra como solución válida
                if (!$stuck && $currentNode === $trimTarget) {
                    $metrics = $costCalc->calculatePathMetrics($antEdges, $maxDistance, $minDistance);
                    $iterationSolutions[] = [
                        'path' => $antPath,
                        'edges' => $antEdges,
                        'cost' => $metrics['totalCost'],
                        'distance' => $metrics['totalDistance'],
                        'risk' => $metrics['totalRisk'],
                    ];
                }
            }

            // Evaporación global de feromona en toda la red
            // Evita que decisiones antiguas dominen indefinidamente y permite que nuevas soluciones emerjan
            foreach ($pheromones as $edgeId => $val) {
                $evaporated = (1.0 - $config->rho) * $val;
                $pheromones[$edgeId] = max(self::MIN_PHEROMONE, $evaporated);
            }

            // Procesamiento de soluciones encontradas en la iteración
            if (!empty($iterationSolutions)) {
                // Ordenamiento de soluciones por costo canónico ascendente
                usort($iterationSolutions, fn($s1, $s2) => $s1['cost'] <=> $s2['cost']);
                $iterBest = $iterationSolutions[0];

                // Evaluación de mejora con respecto al mejor histórico global
                if ($iterBest['cost'] < $globalBestCost) {
                    $improvement = ($globalBestCost === INF) ? INF : ($globalBestCost - $iterBest['cost']);

                    // Si la mejora supera el umbral epsilon de convergencia, se reinicia el contador de estancamiento
                    if ($improvement > $config->convergenceEpsilon) {
                        $patienceCounter = 0;
                        if (!$convergedRecorded) {
                            $convergenceIteration = $it;
                        }
                    } else {
                        $patienceCounter++;
                        if ($patienceCounter >= $config->convergencePatience && !$convergedRecorded) {
                            $convergenceIteration = $it;
                            $convergedRecorded = true;
                        }
                    }

                    // Actualización del óptimo global
                    $globalBestCost = $iterBest['cost'];
                    $globalBestPath = $iterBest['path'];
                    $globalBestDistance = $iterBest['distance'];
                    $globalBestRisk = $iterBest['risk'];
                    $bestFoundIteration = $it;
                } else {
                    $patienceCounter++;
                    if ($patienceCounter >= $config->convergencePatience && !$convergedRecorded) {
                        $convergenceIteration = $it;
                        $convergedRecorded = true;
                    }
                }

                // Depósito de feromona exclusivo en aristas de soluciones válidas
                // La cantidad depositada es inversamente proporcional al costo integral de la ruta
                foreach ($iterationSolutions as $sol) {
                    $deposit = $config->Q / max(self::EPSILON_HEURISTIC, $sol['cost']);
                    foreach ($sol['edges'] as $e) {
                        $currentTau = $pheromones[$e->getId()] ?? $config->initialPheromone;
                        $pheromones[$e->getId()] = $currentTau + $deposit;
                    }
                }
            } else {
                // Si ninguna hormiga construyó una ruta en esta iteración, se cuenta como estancamiento
                $patienceCounter++;
                if ($patienceCounter >= $config->convergencePatience && !$convergedRecorded) {
                    $convergenceIteration = $it;
                    $convergedRecorded = true;
                }
            }

            // El mejor costo histórico global nunca empeora entre iteraciones (garantía de monotonía)
            $bestCostHistory[] = ($globalBestCost === INF ? 0.0 : $globalBestCost);
        }

        $execTime = (microtime(true) - $startTime) * 1000.0;

        // Si no se encontró ningún camino factible hacia el destino
        if ($globalBestCost === INF) {
            return new AcoResult(
                success: false,
                executionTimeMs: $execTime,
                bestCostHistory: $bestCostHistory
            );
        }

        return new AcoResult(
            success: true,
            path: $globalBestPath,
            totalCost: $globalBestCost,
            totalDistance: $globalBestDistance,
            totalRisk: $globalBestRisk,
            executionTimeMs: $execTime,
            bestFoundIteration: $bestFoundIteration,
            convergenceIteration: $convergenceIteration > 0 ? $convergenceIteration : $bestFoundIteration,
            bestCostHistory: $bestCostHistory
        );
    }
}
