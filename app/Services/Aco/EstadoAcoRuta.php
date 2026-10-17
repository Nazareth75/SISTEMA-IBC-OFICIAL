<?php

namespace App\Services\Aco;

/**
 * Catálogo canónico de estados de preparación de una ruta para optimización ACO.
 *
 * Responsabilidad ingenieril:
 * Clasifica objetiva y determinísticamente si una ruta urbana posee las condiciones
 * topológicas y de información cuantitativa de seguridad vial indispensables para que
 * el algoritmo de colonia de hormigas (ACO) pueda ejecutar una optimización multiobjetivo.
 */
class EstadoAcoRuta
{
    /**
     * La ruta está registrada en el sistema pero no tiene ningún tramo físico asociado.
     */
    public const SIN_TRAMOS = 'SIN_TRAMOS';

    /**
     * Los tramos están asociados pero carecen de coordenadas geo_ctr válidas o continuas.
     */
    public const SIN_GEOMETRIA = 'SIN_GEOMETRIA';

    /**
     * Existe una única trayectoria continua entre origen y destino (grafo lineal / serie).
     * No hay bifurcaciones ni caminos alternativos entre los cuales elegir.
     */
    public const GRAFO_LINEAL = 'GRAFO_LINEAL';

    /**
     * Existen tramos pero no hay conectividad continua entre el nodo origen y el nodo destino.
     */
    public const DESCONECTADO = 'DESCONECTADO';

    /**
     * La estructura topológica existe, pero los tramos carecen de datos suficientes en las
     * cuatro fuentes de seguridad (evaluaciones, condiciones, zonas, obstáculos) para computar R_norm.
     */
    public const RIESGO_INSUFICIENTE = 'RIESGO_INSUFICIENTE';

    /**
     * La ruta cumple con todas las condiciones: grafo conexo, al menos 2 alternativas reales
     * y datos de riesgo cuantitativos utilizables para ponderar la función de costo canónica.
     */
    public const LISTO_PARA_OPTIMIZAR = 'LISTO_PARA_OPTIMIZAR';

    /**
     * Obtiene la etiqueta textual para presentación en la interfaz.
     */
    public static function etiqueta(string $estado): string
    {
        return match ($estado) {
            self::SIN_TRAMOS           => 'SIN TRAMOS',
            self::SIN_GEOMETRIA        => 'SIN GEOMETRÍA',
            self::GRAFO_LINEAL         => 'NO OPTIMIZABLE (TRAYECTORIA ÚNICA)',
            self::DESCONECTADO         => 'NO OPTIMIZABLE (DESCONECTADO)',
            self::RIESGO_INSUFICIENTE  => 'RIESGO INSUFICIENTE',
            self::LISTO_PARA_OPTIMIZAR => 'LISTO PARA OPTIMIZAR',
            default                    => 'ESTADO DESCONOCIDO',
        };
    }

    /**
     * Etiqueta corta para tablas y vistas condensadas.
     */
    public static function etiquetaCorta(string $estado): string
    {
        return match ($estado) {
            self::SIN_TRAMOS           => 'Sin tramos',
            self::SIN_GEOMETRIA        => 'Sin geometría',
            self::GRAFO_LINEAL         => 'Trayectoria única',
            self::DESCONECTADO         => 'Desconectado',
            self::RIESGO_INSUFICIENTE  => 'Riesgo insuficiente',
            self::LISTO_PARA_OPTIMIZAR => 'Listo para optimizar',
            default                    => 'No disponible',
        };
    }

    /**
     * Clase CSS de Tailwind para insignia / badge.
     */
    public static function claseColor(string $estado): string
    {
        return match ($estado) {
            self::SIN_TRAMOS           => 'bg-gray-100 text-gray-700 border-gray-300',
            self::SIN_GEOMETRIA        => 'bg-rose-100 text-rose-800 border-rose-300',
            self::GRAFO_LINEAL         => 'bg-amber-100 text-amber-800 border-amber-300',
            self::DESCONECTADO         => 'bg-red-100 text-red-800 border-red-300',
            self::RIESGO_INSUFICIENTE  => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            self::LISTO_PARA_OPTIMIZAR => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default                    => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }

    /**
     * Color hexadecimal representativo.
     */
    public static function colorHex(string $estado): string
    {
        return match ($estado) {
            self::SIN_TRAMOS           => '#6b7280',
            self::SIN_GEOMETRIA        => '#e11d48',
            self::GRAFO_LINEAL         => '#d97706',
            self::DESCONECTADO         => '#dc2626',
            self::RIESGO_INSUFICIENTE  => '#ca8a04',
            self::LISTO_PARA_OPTIMIZAR => '#16a34a',
            default                    => '#9ca3af',
        };
    }

    /**
     * Icono FontAwesome representativo.
     */
    public static function iconoFa(string $estado): string
    {
        return match ($estado) {
            self::SIN_TRAMOS           => 'fas fa-ban',
            self::SIN_GEOMETRIA        => 'fas fa-draw-polygon',
            self::GRAFO_LINEAL         => 'fas fa-arrow-right',
            self::DESCONECTADO         => 'fas fa-unlink',
            self::RIESGO_INSUFICIENTE  => 'fas fa-shield-alt',
            self::LISTO_PARA_OPTIMIZAR => 'fas fa-check-circle',
            default                    => 'fas fa-question-circle',
        };
    }
}
