<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Calculador de la Función de Costo Canónica Normalizada para el Aporte ACO (ACO-2).
 *
 * Responsabilidad ingenieril:
 * Transforma los atributos heterogéneos de cada segmento vial (distancia física en metros
 * y nivel de riesgo de seguridad) en un costo escalar unidimensional comparable y normalizado.
 *
 * FÓRMULA CANÓNICA IMPLEMENTADA:
 * C(e) = wD * D_norm(e) + wR * R_norm(e)
 *
 * Donde:
 * - wD: Peso relativo asignado a la distancia métrica (wD >= 0).
 * - wR: Peso relativo asignado a la seguridad / riesgo (wR >= 0).
 * - Restricción obligatoria de ponderación: wD + wR = 1.0 (evaluada con tolerancia numérica).
 * - D_norm(e) ∈ [0.0, 1.0]: Distancia normalizada mediante escala Min-Max o escala relativa acotada.
 * - R_norm(e) ∈ [0.0, 1.0]: Riesgo normalizado intrínseco del tramo peatonal.
 *
 * Justificación de la normalización:
 * Evita la inconmensurabilidad de variables (sumar directamente metros [e.g. 500m] con riesgos [0..1]),
 * lo que distorsionaría por completo la toma de decisiones probabilística de las hormigas.
 */
class CostCalculator
{
    /**
     * Peso ponderado asignado al componente de distancia.
     */
    private float $wDistance;

    /**
     * Peso ponderado asignado al componente de riesgo.
     */
    private float $wRisk;

    /**
     * Tolerancia de precisión para validación de punto flotante en suma de pesos.
     */
    private const FLOAT_EPSILON = 1e-4;

    /**
     * Constructor del evaluador de costo.
     *
     * @param float $wDistance Ponderación para distancia (>= 0.0).
     * @param float $wRisk     Ponderación para riesgo (>= 0.0).
     *
     * @throws InvalidArgumentException Si los pesos son negativos, no finitos, o si su suma difiere de 1.0.
     */
    public function __construct(float $wDistance = 0.5, float $wRisk = 0.5)
    {
        if (is_nan($wDistance) || is_infinite($wDistance)) {
            throw new InvalidArgumentException("El peso wDistance debe ser un valor numérico real finito.");
        }

        if (is_nan($wRisk) || is_infinite($wRisk)) {
            throw new InvalidArgumentException("El peso wRisk debe ser un valor numérico real finito.");
        }

        if ($wDistance < 0.0 || $wRisk < 0.0) {
            throw new InvalidArgumentException(
                "Los pesos no pueden ser negativos. Valores recibidos: wDistance={$wDistance}, wRisk={$wRisk}"
            );
        }

        $sum = $wDistance + $wRisk;
        if (abs($sum - 1.0) > self::FLOAT_EPSILON) {
            throw new InvalidArgumentException(
                "La suma de ponderaciones (wDistance + wRisk) debe ser exactamente 1.0 (tolerancia " . self::FLOAT_EPSILON . "). " .
                "Suma recibida: {$sum} (wDistance={$wDistance}, wRisk={$wRisk})"
            );
        }

        $this->wDistance = $wDistance;
        $this->wRisk = $wRisk;
    }

    /**
     * Normaliza la distancia métrica de una arista al intervalo cerrado [0.0, 1.0].
     *
     * ESTRATEGIA DE NORMALIZACIÓN:
     * Cuando se suministran cotas de grafo ($minDistance y $maxDistance):
     * D_norm = (D - Dmin) / (Dmax - Dmin)
     *
     * Protección contra división por cero:
     * Si Dmax == Dmin (o Dmax <= Dmin), todas las aristas poseen la misma distancia,
     * por lo que el componente distancia no discrimina rutas y retorna 0.0 de forma controlada.
     *
     * @param float $distance    Distancia de la arista en metros.
     * @param float $maxDistance Distancia máxima observada en el grafo.
     * @param float $minDistance Distancia mínima observada en el grafo (por defecto 0.0).
     * @return float Valor normalizado acotado en [0.0, 1.0].
     */
    public function calculateNormalizedDistance(float $distance, float $maxDistance, float $minDistance = 0.0): float
    {
        if ($maxDistance <= $minDistance || $maxDistance <= 0.0) {
            return 0.0;
        }

        // Si minDistance es 0, equivale a normalización relativa D / Dmax.
        // Si minDistance > 0, aplica Min-Max estricto.
        $norm = ($distance - $minDistance) / ($maxDistance - $minDistance);

        return min(1.0, max(0.0, $norm));
    }

    /**
     * Calcula el costo canónico normalizado C(e) de recorrer una arista.
     *
     * Combina distancia normalizada y riesgo normalizado empleando los pesos wD y wR.
     * Garantiza formalmente que C(e) ∈ [0.0, 1.0].
     *
     * @param Edge  $edge        Arista evaluada.
     * @param float $maxDistance Distancia máxima para normalización.
     * @param float $minDistance Distancia mínima para normalización (por defecto 0.0).
     * @return float Costo normalizado en [0.0, 1.0].
     */
    public function calculateEdgeCost(Edge $edge, float $maxDistance, float $minDistance = 0.0): float
    {
        $dNorm = $this->calculateNormalizedDistance($edge->getDistance(), $maxDistance, $minDistance);
        $rNorm = $edge->getRisk();

        return ($this->wDistance * $dNorm) + ($this->wRisk * $rNorm);
    }

    /**
     * Calcula las métricas agregadas de una secuencia de aristas (camino).
     *
     * @param Edge[] $edges       Secuencia consecutiva de aristas que forman la ruta.
     * @param float  $maxDistance Cota superior para normalización de distancia.
     * @param float  $minDistance Cota inferior para normalización de distancia.
     * @return array{totalDistance: float, totalRisk: float, totalCost: float}
     */
    public function calculatePathMetrics(array $edges, float $maxDistance, float $minDistance = 0.0): array
    {
        $totalDistance = 0.0;
        $totalRisk = 0.0;
        $totalCost = 0.0;

        foreach ($edges as $edge) {
            $totalDistance += $edge->getDistance();
            $totalRisk += $edge->getRisk();
            $totalCost += $this->calculateEdgeCost($edge, $maxDistance, $minDistance);
        }

        return [
            'totalDistance' => $totalDistance,
            'totalRisk' => $totalRisk,
            'totalCost' => $totalCost,
        ];
    }

    /**
     * Calcula el costo total de un camino dado como secuencia de nombres de nodos.
     *
     * @param Graph         $graph       Grafo con la topología y distancias.
     * @param string[]      $path        Secuencia de nodos [origen, ..., destino].
     * @param float|null    $maxDistance Cota superior opcional (se obtiene del grafo si es null).
     * @param float|null    $minDistance Cota inferior opcional (se obtiene del grafo si es null).
     * @return float Costo total acumulado del camino.
     */
    public function calculatePathCost(
        Graph $graph,
        array $path,
        ?float $maxDistance = null,
        ?float $minDistance = null
    ): float {
        if (count($path) <= 1) {
            return 0.0;
        }

        $maxD = $maxDistance ?? $graph->getMaxDistance();
        $minD = $minDistance ?? $graph->getMinDistance();
        $totalCost = 0.0;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $u = $path[$i];
            $v = $path[$i + 1];

            $edge = $graph->getEdge($u, $v);
            if ($edge !== null) {
                $totalCost += $this->calculateEdgeCost($edge, $maxD, $minD);
            }
        }

        return $totalCost;
    }

    /**
     * Retorna el peso ponderado asignado a la distancia (wD).
     */
    public function getWDistance(): float
    {
        return $this->wDistance;
    }

    /**
     * Retorna el peso ponderado asignado al riesgo (wR).
     */
    public function getWRisk(): float
    {
        return $this->wRisk;
    }
}
