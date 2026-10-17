<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Estructura de Grafo Dirigido Ponderado para el Algoritmo de Colonia de Hormigas (ACO-1).
 *
 * Modela la red topológica urbana como un grafo dirigido G = (V, E), donde:
 * - V (vértices) representa intersecciones o puntos de decisión de navegación peatonal.
 * - E (aristas dirigidas) representa tramos transitables con sentido específico.
 *
 * Justificación de diseño:
 * La red urbana no es simétrica: el sentido del tráfico vehicular, obstáculos en una
 * sola acera, pendientes y obras determinan que transitar de A hacia B puede tener
 * condiciones físicas y de riesgo sustancialmente distintas a transitar de B hacia A.
 * Por ello, la estructura es explícitamente dirigida. Para modelar una calle bidireccional,
 * deben agregarse dos aristas independientes (A->B y B->A).
 */
class Grafo
{
    /**
     * Conjunto de identificadores de nodos registrados en el grafo.
     * Estructurado como mapa asociativo [id_nodo => true] para búsqueda O(1).
     *
     * @var array<string, bool>
     */
    private array $nodes = [];

    /**
     * Lista de adyacencia dirigida saliente: [id_nodo_origen => Arista[]].
     *
     * @var array<string, array<Arista>>
     */
    private array $adjacencyList = [];

    /**
     * Índice de aristas indexadas unívocamente por su clave compuesta ("origen->destino").
     *
     * @var array<string, Arista>
     */
    private array $edgesById = [];

    /**
     * Registra un nodo en la topología del grafo.
     * Si el nodo ya existe, la operación es idempotente y conserva sus aristas salientes.
     *
     * @param string $node Identificador no vacío del nodo.
     * @return self
     *
     * @throws InvalidArgumentException Si el identificador del nodo es una cadena vacía.
     */
    public function addNode(string $node): self
    {
        $trimNode = trim($node);
        if ($trimNode === '') {
            throw new InvalidArgumentException("El identificador del nodo no puede estar vacío.");
        }

        if (!isset($this->nodes[$trimNode])) {
            $this->nodes[$trimNode] = true;
            $this->adjacencyList[$trimNode] = [];
        }

        return $this;
    }

    /**
     * Agrega una arista dirigida al grafo.
     *
     * Registra automáticamente los nodos extremos (from y to) si aún no estuviesen presentes.
     * Detecta y rechaza explícitamente aristas duplicadas con el mismo origen y destino,
     * garantizando la unicidad y coherencia de las conexiones topológicas.
     *
     * @param Arista $edge Instancia válida de arista.
     * @return self
     *
     * @throws InvalidArgumentException Si ya existe una arista registrada con el mismo id.
     */
    public function addEdge(Arista $edge): self
    {
        $edgeId = $edge->getId();

        if (isset($this->edgesById[$edgeId])) {
            throw new InvalidArgumentException(
                "Arista duplicada detectada: ya existe una conexión '{$edgeId}' en el grafo."
            );
        }

        $this->addNode($edge->getFrom());
        $this->addNode($edge->getTo());

        $this->adjacencyList[$edge->getFrom()][] = $edge;
        $this->edgesById[$edgeId] = $edge;

        return $this;
    }

    /**
     * Verifica si un nodo existe dentro de la topología del grafo.
     *
     * @param string $node Identificador del nodo a consultar.
     */
    public function hasNode(string $node): bool
    {
        return isset($this->nodes[trim($node)]);
    }

    /**
     * Verifica si existe una arista dirigida directa entre dos nodos específicos.
     *
     * @param string $from Nodo origen.
     * @param string $to   Nodo destino.
     */
    public function hasEdge(string $from, string $to): bool
    {
        return isset($this->edgesById[trim($from) . '->' . trim($to)]);
    }

    /**
     * Retorna la lista de identificadores de todos los nodos registrados en el grafo.
     *
     * @return string[]
     */
    public function getNodes(): array
    {
        return array_keys($this->nodes);
    }

    /**
     * Retorna la lista de todas las aristas dirigidas registradas en el grafo.
     *
     * @return Arista[]
     */
    public function getEdges(): array
    {
        return array_values($this->edgesById);
    }

    /**
     * Retorna los nodos vecinos directamente alcanzables desde un nodo dado mediante aristas salientes.
     *
     * @param string $node Identificador del nodo origen.
     * @return string[] Identificadores de nodos vecinos alcanzables.
     */
    public function getNeighbors(string $node): array
    {
        $trimNode = trim($node);
        if (!isset($this->adjacencyList[$trimNode])) {
            return [];
        }

        $neighbors = [];
        foreach ($this->adjacencyList[$trimNode] as $edge) {
            $neighbors[] = $edge->getTo();
        }

        return $neighbors;
    }

    /**
     * Retorna todas las aristas dirigidas salientes desde un nodo específico.
     *
     * @param string $node Identificador del nodo origen.
     * @return Arista[]
     */
    public function getOutgoingEdges(string $node): array
    {
        return $this->adjacencyList[trim($node)] ?? [];
    }

    /**
     * Obtiene la arista dirigida entre dos nodos específicos, si existe.
     *
     * @param string $from Nodo origen.
     * @param string $to   Nodo destino.
     * @return Arista|null La instancia de Arista o null si no existe conexión directa.
     */
    public function getEdge(string $from, string $to): ?Arista
    {
        return $this->edgesById[trim($from) . '->' . trim($to)] ?? null;
    }

    /**
     * Obtiene la distancia métrica mínima entre todas las aristas del grafo.
     * Protegido: si el grafo no contiene aristas, retorna 0.0.
     */
    public function getMinDistance(): float
    {
        if (empty($this->edgesById)) {
            return 0.0;
        }

        $min = null;
        foreach ($this->edgesById as $edge) {
            if ($min === null || $edge->getDistance() < $min) {
                $min = $edge->getDistance();
            }
        }

        return $min ?? 0.0;
    }

    /**
     * Obtiene la distancia métrica máxima entre todas las aristas del grafo.
     * Protegido: si el grafo no contiene aristas o la distancia máxima no supera 0, retorna 1.0
     * para evitar posteriores divisiones por cero en normalizaciones.
     */
    public function getMaxDistance(): float
    {
        if (empty($this->edgesById)) {
            return 1.0;
        }

        $max = 0.0;
        foreach ($this->edgesById as $edge) {
            if ($edge->getDistance() > $max) {
                $max = $edge->getDistance();
            }
        }

        return $max > 0.0 ? $max : 1.0;
    }
}
