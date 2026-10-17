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
 * - D_norm(e) ∈ [0.0, 1.0]: Distancia normalizada mediante escala lineal sobre la cota máxima del grafo:
 *     D_norm(e) = D(e) / D_max
 * - R_norm(e) ∈ [0.0, 1.0]: Riesgo normalizado intrínseco del tramo peatonal.
 *
 * JUSTIFICACIÓN MATEMÁTICA DE LA NORMALIZACIÓN LINEAL (D / D_max):
 * La normalización Min-Max por arista: (D(e) - Dmin) / (Dmax - Dmin), al ser sumada a lo largo de una ruta:
 *   Σ D_norm(e) = [Σ D(e) - k * Dmin] / (Dmax - Dmin) = [D_total - k * Dmin] / (Dmax - Dmin)
 * introduce una distorsión no aditiva proporcional al número de aristas k. Dos rutas con la misma
 * distancia física total (por ejemplo, una arista de 200m frente a dos aristas de 100m con Dmin=100 y Dmax=200)
 * recibirían costos de distancia artificialmente dispares (1.0 vs 0.0), favoreciendo ilegítimamente la fragmentación.
 * La escala lineal D_norm(e) = D(e) / D_max preserva estrictamente la propiedad aditiva:
 *   Σ D_norm(e) = (1 / D_max) * Σ D(e) = D_total / D_max
 * garantizando comparabilidad uniforme, compatibilidad con la subestructura óptima de Dijkstra y con
 * la heurística local paso a paso de ACO.
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
     * Normaliza linealmente la distancia métrica de una arista al intervalo cerrado [0.0, 1.0].
     *
     * FÓRMULA LINEAL ADITIVA:
     * D_norm = D / D_max
     *
     * Protección contra división por cero:
     * Si D_max <= 0, retorna 0.0 de forma controlada.
     *
     * @param float $distance    Distancia de la arista en metros.
     * @param float $maxDistance Distancia máxima observada en el grafo (cota superior de normalización).
     * @return float Valor normalizado acotado en [0.0, 1.0].
     */
    public function calculateNormalizedDistance(float $distance, float $maxDistance): float
    {
        if ($maxDistance <= 0.0) {
            return 0.0;
        }

        $norm = $distance / $maxDistance;

        return min(1.0, max(0.0, $norm));
    }

    /**
     * Calcula el costo canónico normalizado C(e) de recorrer una arista.
     *
     * C(e) = wD * (D / D_max) + wR * R_norm
     *
     * @param Edge  $edge        Arista evaluada.
     * @param float $maxDistance Distancia máxima para normalización.
     * @return float Costo normalizado en [0.0, 1.0].
     */
    public function calculateEdgeCost(Edge $edge, float $maxDistance): float
    {
        $dNorm = $this->calculateNormalizedDistance($edge->getDistance(), $maxDistance);
        $rNorm = $edge->getRisk();

        return ($this->wDistance * $dNorm) + ($this->wRisk * $rNorm);
    }

    /**
     * Calcula las métricas agregadas de una secuencia de aristas (camino).
     *
     * NOTA METODOLÓGICA SOBRE EL RIESGO TOTAL:
     * La suma de riesgos (totalRisk) representa una agregación experimental temporal
     * para análisis sintético en esta fase, y NO constituye la definición definitiva
     * del modelo de riesgo integral de rutas del sistema IBC (la cual será articulada
     * posteriormente al integrar TramoRiskService).
     *
     * @param Edge[] $edges       Secuencia consecutiva de aristas que forman la ruta.
     * @param float  $maxDistance Cota superior para normalización de distancia.
     * @return array{totalDistance: float, totalRisk: float, totalCost: float}
     */
    public function calculatePathMetrics(array $edges, float $maxDistance): array
    {
        $totalDistance = 0.0;
        $totalRisk = 0.0;
        $totalCost = 0.0;

        foreach ($edges as $edge) {
            $totalDistance += $edge->getDistance();
            $totalRisk += $edge->getRisk();
            $totalCost += $this->calculateEdgeCost($edge, $maxDistance);
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
     * @param Graph      $graph       Grafo con la topología y distancias.
     * @param string[]   $path        Secuencia de nodos [origen, ..., destino].
     * @param float|null $maxDistance Cota superior opcional (se obtiene del grafo si es null).
     * @return float Costo total acumulado del camino.
     */
    public function calculatePathCost(Graph $graph, array $path, ?float $maxDistance = null): float
    {
        if (count($path) <= 1) {
            return 0.0;
        }

        $maxD = $maxDistance ?? $graph->getMaxDistance();
        $totalCost = 0.0;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $u = $path[$i];
            $v = $path[$i + 1];

            $edge = $graph->getEdge($u, $v);
            if ($edge !== null) {
                $totalCost += $this->calculateEdgeCost($edge, $maxD);
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
