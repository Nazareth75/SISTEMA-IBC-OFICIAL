<?php

namespace App\Services\Aco;

/**
 * Objeto de Transferencia de Datos (DTO) que consolida el diagnóstico de preparación ACO de una ruta.
 *
 * Responsabilidad ingenieril:
 * Encapsula la información topológica, métrica y de riesgo obtenida tras auditar
 * la ruta y sus segmentos físicos asociados, permitiendo a la interfaz y al backend
 * decidir transparentemente si el algoritmo ACO puede ser ejecutado.
 */
class AnalisisAcoRuta
{
    /**
     * Estado canónico principal de la ruta frente a ACO (ver EstadoAcoRuta).
     */
    public string $estado;

    /**
     * Etiqueta descriptiva para el usuario.
     */
    public string $etiquetaEstado;

    /**
     * Etiqueta corta para tablas y badges condensados.
     */
    public string $etiquetaCorta;

    /**
     * Clase CSS de Tailwind para pintar el badge de estado.
     */
    public string $colorBadge;

    /**
     * Color hexadecimal representativo.
     */
    public string $colorHex;

    /**
     * Icono FontAwesome asociado.
     */
    public string $iconoFa;

    /**
     * Indica si el grafo cumple simultáneamente con tener >= 2 caminos y riesgo calculable.
     */
    public bool $esOptimizable;

    /**
     * Cantidad de tramos físicos asociados a la ruta.
     */
    public int $cantidadTramos;

    /**
     * Cantidad de nodos de interconexión geográfica detectados mediante tolerancia espacial.
     */
    public int $cantidadNodos;

    /**
     * Cantidad de trayectorias acíclicas alternativas encontradas entre el origen y destino.
     */
    public int $cantidadAlternativas;

    /**
     * Indica si la topología corresponde a una cadena estrictamente lineal en serie (1 camino).
     */
    public bool $esLineal;

    /**
     * Indica si existe información suficiente de riesgo para ponderar la función objetivo canónica.
     */
    public bool $tieneRiesgoSuficiente;

    /**
     * Nivel consolidado de cobertura de datos en la ruta ('SIN_DATOS', 'PARCIAL', 'COMPLETO').
     */
    public string $coberturaRiesgo;

    /**
     * Lista de explicaciones humanas sobre por qué la ruta no puede ser optimizada aún.
     *
     * @var string[]
     */
    public array $motivosBloqueo = [];

    /**
     * Lista estructurada de verificaciones previas para ACO (Checklist de preparación).
     * Cada ítem contiene: ['clave' => string, 'titulo' => string, 'cumple' => bool, 'detalle' => string].
     *
     * @var array<int, array<string, mixed>>
     */
    public array $checklist = [];

    /**
     * Identificador del nodo inferido como inicio de la ruta.
     */
    public ?string $nodoOrigenId = null;

    /**
     * Identificador del nodo inferido como fin de la ruta.
     */
    public ?string $nodoDestinoId = null;

    /**
     * Catálogo de nodos indexados geográficamente.
     * Estructura: [ 'N0' => ['id' => 'N0', 'lat' => float, 'lng' => float, 'grado' => int], ... ]
     *
     * @var array<string, array<string, mixed>>
     */
    public array $nodos = [];

    /**
     * Lista de aristas topológicas que conforman el grafo.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $aristas = [];

    /**
     * Desglose completo de cada tramo con sus atributos reales para la tabla de la interfaz.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $tramosDetalle = [];

    /**
     * Constructor del DTO con valores predeterminados seguros.
     *
     * @param array<string, mixed> $datos
     */
    public function __construct(array $datos = [])
    {
        $this->estado                 = $datos['estado'] ?? EstadoAcoRuta::SIN_TRAMOS;
        $this->etiquetaEstado         = $datos['etiquetaEstado'] ?? EstadoAcoRuta::etiqueta($this->estado);
        $this->etiquetaCorta          = $datos['etiquetaCorta'] ?? EstadoAcoRuta::etiquetaCorta($this->estado);
        $this->colorBadge             = $datos['colorBadge'] ?? EstadoAcoRuta::claseColor($this->estado);
        $this->colorHex               = $datos['colorHex'] ?? EstadoAcoRuta::colorHex($this->estado);
        $this->iconoFa                = $datos['iconoFa'] ?? EstadoAcoRuta::iconoFa($this->estado);
        $this->esOptimizable          = (bool) ($datos['esOptimizable'] ?? false);
        $this->cantidadTramos         = (int) ($datos['cantidadTramos'] ?? 0);
        $this->cantidadNodos          = (int) ($datos['cantidadNodos'] ?? 0);
        $this->cantidadAlternativas   = (int) ($datos['cantidadAlternativas'] ?? 0);
        $this->esLineal               = (bool) ($datos['esLineal'] ?? false);
        $this->tieneRiesgoSuficiente  = (bool) ($datos['tieneRiesgoSuficiente'] ?? false);
        $this->coberturaRiesgo        = (string) ($datos['coberturaRiesgo'] ?? 'SIN_DATOS');
        $this->motivosBloqueo         = $datos['motivosBloqueo'] ?? [];
        $this->checklist              = $datos['checklist'] ?? [];
        $this->nodoOrigenId           = $datos['nodoOrigenId'] ?? null;
        $this->nodoDestinoId          = $datos['nodoDestinoId'] ?? null;
        $this->nodos                  = $datos['nodos'] ?? [];
        $this->aristas                = $datos['aristas'] ?? [];
        $this->tramosDetalle          = $datos['tramosDetalle'] ?? [];
    }

    /**
     * Determina si la ruta tiene tramos físicos registrados.
     */
    public function tieneTramos(): bool
    {
        return $this->cantidadTramos > 0;
    }

    /**
     * Determina si la ruta posee alternativas reales (al menos 2 caminos posibles).
     */
    public function tieneAlternativas(): bool
    {
        return $this->cantidadAlternativas >= 2;
    }
}
