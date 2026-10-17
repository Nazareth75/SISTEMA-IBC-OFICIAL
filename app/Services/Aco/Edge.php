<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Representación inmutable de una arista dirigida en el grafo de navegación.
 *
 * En el contexto del aporte ingenieril ACO (ACO-1 / ACO-2), una arista modela
 * la conexión transitable unidireccional entre dos nodos espaciales o topológicos
 * (por ejemplo, intersecciones viales o esquinas).
 *
 * Cada arista encapsula exclusivamente su topología (origen, destino) y sus
 * atributos físicos y de seguridad intrínsecos (distancia métrica y riesgo normalizado).
 * El costo de la arista NO se almacena aquí de forma estática, sino que es evaluado
 * dinámicamente por {@see CostCalculator} en función de los pesos experimentales (wD, wR).
 */
class Edge
{
    /**
     * Identificador unívoco de la arista en formato "origen->destino".
     */
    private string $id;

    /**
     * Nodo origen de la conexión dirigida.
     */
    private string $from;

    /**
     * Nodo destino de la conexión dirigida.
     */
    private string $to;

    /**
     * Longitud física de la arista (en metros o unidades métricas equivalentes).
     * Invariante obligatoria: distancia > 0.0 y finita.
     */
    private float $distance;

    /**
     * Nivel de riesgo intrínseco del segmento, normalizado en el intervalo [0.0, 1.0].
     * 0.0 representa riesgo mínimo / seguridad máxima; 1.0 representa riesgo crítico.
     * Invariante obligatoria: 0.0 <= riesgo <= 1.0 y finito.
     */
    private float $risk;

    /**
     * Crea una nueva instancia de arista con validación estricta de invariantes.
     *
     * @param string $from     Identificador del nodo origen (no vacío).
     * @param string $to       Identificador del nodo destino (no vacío, distinto de $from).
     * @param float  $distance Distancia métrica positiva (distancia > 0.0).
     * @param float  $risk     Índice de riesgo normalizado en el rango [0.0, 1.0].
     *
     * @throws InvalidArgumentException Si alguno de los parámetros viola las restricciones
     *                                  topológicas, numéricas o de rango permitido.
     */
    public function __construct(string $from, string $to, float $distance, float $risk)
    {
        $trimFrom = trim($from);
        $trimTo = trim($to);

        if ($trimFrom === '') {
            throw new InvalidArgumentException("El nodo origen de la arista no puede estar vacío.");
        }

        if ($trimTo === '') {
            throw new InvalidArgumentException("El nodo destino de la arista no puede estar vacío.");
        }

        if ($trimFrom === $trimTo) {
            throw new InvalidArgumentException(
                "Una arista normal no puede conectar un nodo consigo mismo (bucle propio '{$trimFrom}->{$trimTo}')."
            );
        }

        if (is_nan($distance) || is_infinite($distance)) {
            throw new InvalidArgumentException("La distancia de la arista debe ser un número real finito.");
        }

        if ($distance <= 0.0) {
            throw new InvalidArgumentException(
                "La distancia de una arista debe ser estrictamente positiva (> 0.0). Valor recibido: {$distance}"
            );
        }

        if (is_nan($risk) || is_infinite($risk)) {
            throw new InvalidArgumentException("El riesgo de la arista debe ser un número real finito.");
        }

        if ($risk < 0.0 || $risk > 1.0) {
            throw new InvalidArgumentException(
                "El riesgo de la arista debe ubicarse en el intervalo cerrado [0.0, 1.0]. Valor recibido: {$risk}"
            );
        }

        $this->from = $trimFrom;
        $this->to = $trimTo;
        $this->distance = $distance;
        $this->risk = $risk;
        $this->id = "{$trimFrom}->{$trimTo}";
    }

    /**
     * Retorna el identificador unívoco de la arista ("from->to").
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Retorna el identificador del nodo origen.
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Retorna el identificador del nodo destino.
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * Retorna la distancia física de la arista en metros.
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * Retorna el riesgo intrínseco de la arista en [0.0, 1.0].
     */
    public function getRisk(): float
    {
        return $this->risk;
    }
}
