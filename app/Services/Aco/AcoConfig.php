<?php

namespace App\Services\Aco;

use InvalidArgumentException;

/**
 * Parámetros de Configuración y Control para el Algoritmo de Colonia de Hormigas (ACO).
 *
 * Responsabilidad ingenieril:
 * Encapsula, valida y documenta los hiperparámetros estocásticos y matemáticos que rigen
 * el comportamiento exploratorio y de explotación del algoritmo ACO (ACO-3 a ACO-7),
 * así como los coeficientes de ponderación de la función objetivo multiobjetivo.
 *
 * DISTINCIÓN METODOLÓGICA CLAVE:
 * - alpha y beta: Parámetros intrínsecos de ACO que modulan la regla de transición probabilística
 *   (balance entre feromona acumulada y visibilidad heurística local).
 * - wDistance y wRisk: Pesos de la función objetivo de optimización que definen el balance
 *   de preferencias entre minimizar la longitud del trayecto y maximizar la seguridad peatonal.
 */
class AcoConfig
{
    /**
     * Coeficiente de influencia de la feromona (alpha >= 0.0).
     * Modula la importancia del aprendizaje colectivo y la concentración de trayectorias pasadas.
     * Si alpha = 0, el algoritmo actúa puramente guiado por la heurística local (modo voraz estocástico).
     */
    public float $alpha;

    /**
     * Coeficiente de influencia de la visibilidad heurística (beta >= 0.0).
     * Modula la atracción hacia tramos de bajo costo inmediato (1 / costo).
     * Si beta = 0, el algoritmo actúa puramente guiado por la feromona previa sin considerar el costo inmediato.
     */
    public float $beta;

    /**
     * Tasa de evaporación global de feromona (0.0 <= rho <= 1.0).
     * Representa la fracción de feromona disipada en cada iteración.
     * Previene la saturación prematura en óptimos locales y permite explorar nuevas alternativas.
     */
    public float $rho;

    /**
     * Factor de escala / recompensa de feromona (Q > 0.0).
     * Constante utilizada en el depósito de feromona: Delta_tau = Q / costo_solución.
     */
    public float $Q;

    /**
     * Cantidad de hormigas artificiales por iteración (ants >= 1).
     * Cada hormiga construye una solución independiente desde el origen hasta el destino.
     */
    public int $ants;

    /**
     * Número máximo de iteraciones globales de la colonia (iterations >= 1).
     */
    public int $iterations;

    /**
     * Nivel de feromona inicial asignado a todas las aristas (initialPheromone > 0.0).
     * Proporciona la base de exploración neutra y homogénea en la iteración inicial.
     */
    public float $initialPheromone;

    /**
     * Umbral mínimo de mejora relativa para considerar progreso (convergenceEpsilon >= 0.0).
     * Si la mejora del mejor costo global es inferior a este umbral, se incrementa el contador de paciencia.
     */
    public float $convergenceEpsilon;

    /**
     * Número de iteraciones consecutivas sin mejora significativa requeridas para registrar convergencia (patience >= 1).
     */
    public int $convergencePatience;

    /**
     * Semilla pseudo-aleatoria para garantizar reproducibilidad experimental (opcional).
     * Si es null, el algoritmo opera con aleatoriedad estándar de la plataforma.
     */
    public ?int $seed;

    /**
     * Ponderación de distancia en la función de costo (0.0 <= wDistance <= 1.0).
     */
    public float $wDistance;

    /**
     * Ponderación de riesgo en la función de costo (0.0 <= wRisk <= 1.0).
     */
    public float $wRisk;

    /**
     * Tolerancia de punto flotante para verificar la condición wDistance + wRisk == 1.0.
     */
    private const FLOAT_EPSILON = 1e-4;

    /**
     * Constructor con validación defensiva exhaustiva.
     *
     * @param float    $alpha               Exponente de feromona (>= 0.0).
     * @param float    $beta                Exponente de heurística (>= 0.0).
     * @param float    $rho                 Tasa de evaporación en [0.0, 1.0].
     * @param float    $Q                   Constante de depósito (> 0.0).
     * @param int      $ants                Población de hormigas por iteración (>= 1).
     * @param int      $iterations          Iteraciones máximas (>= 1).
     * @param float    $initialPheromone    Feromona inicial homogénea (> 0.0).
     * @param float    $convergenceEpsilon  Tolerancia de mejora para convergencia (>= 0.0).
     * @param int      $convergencePatience Iteraciones de estancamiento para declarar convergencia (>= 1).
     * @param int|null $seed                Semilla para reproducibilidad (opcional).
     * @param float    $wDistance           Ponderación para distancia (>= 0.0).
     * @param float    $wRisk               Ponderación para riesgo (>= 0.0).
     *
     * @throws InvalidArgumentException Si algún parámetro no cumple sus restricciones matemáticas.
     */
    public function __construct(
        float $alpha = 1.0,
        float $beta = 2.0,
        float $rho = 0.1,
        float $Q = 100.0,
        int $ants = 10,
        int $iterations = 50,
        float $initialPheromone = 1.0,
        float $convergenceEpsilon = 1e-4,
        int $convergencePatience = 10,
        ?int $seed = null,
        float $wDistance = 0.5,
        float $wRisk = 0.5
    ) {
        if (is_nan($alpha) || is_infinite($alpha) || $alpha < 0.0) {
            throw new InvalidArgumentException(
                "El exponente alpha debe ser un número real finito >= 0.0. Valor recibido: {$alpha}"
            );
        }

        if (is_nan($beta) || is_infinite($beta) || $beta < 0.0) {
            throw new InvalidArgumentException(
                "El exponente beta debe ser un número real finito >= 0.0. Valor recibido: {$beta}"
            );
        }

        if (is_nan($rho) || is_infinite($rho) || $rho < 0.0 || $rho > 1.0) {
            throw new InvalidArgumentException(
                "La tasa de evaporación rho debe ser un valor real finito en el rango [0.0, 1.0]. Valor recibido: {$rho}"
            );
        }

        if (is_nan($Q) || is_infinite($Q) || $Q <= 0.0) {
            throw new InvalidArgumentException(
                "La constante de depósito Q debe ser un número real finito estrictamente positivo (> 0.0). Valor recibido: {$Q}"
            );
        }

        if ($ants <= 0) {
            throw new InvalidArgumentException(
                "El número de hormigas debe ser un entero positivo (ants >= 1). Valor recibido: {$ants}"
            );
        }

        if ($iterations <= 0) {
            throw new InvalidArgumentException(
                "El número de iteraciones debe ser un entero positivo (iterations >= 1). Valor recibido: {$iterations}"
            );
        }

        if (is_nan($initialPheromone) || is_infinite($initialPheromone) || $initialPheromone <= 0.0) {
            throw new InvalidArgumentException(
                "La feromona inicial debe ser un número real finito estrictamente positivo (> 0.0). Valor recibido: {$initialPheromone}"
            );
        }

        if (is_nan($convergenceEpsilon) || is_infinite($convergenceEpsilon) || $convergenceEpsilon < 0.0) {
            throw new InvalidArgumentException(
                "El umbral convergenceEpsilon debe ser un número real finito >= 0.0. Valor recibido: {$convergenceEpsilon}"
            );
        }

        if ($convergencePatience <= 0) {
            throw new InvalidArgumentException(
                "La paciencia de convergencia convergencePatience debe ser un entero >= 1. Valor recibido: {$convergencePatience}"
            );
        }

        if (is_nan($wDistance) || is_infinite($wDistance) || $wDistance < 0.0) {
            throw new InvalidArgumentException("El peso wDistance debe ser un número real finito >= 0.0.");
        }

        if (is_nan($wRisk) || is_infinite($wRisk) || $wRisk < 0.0) {
            throw new InvalidArgumentException("El peso wRisk debe ser un número real finito >= 0.0.");
        }

        $sum = $wDistance + $wRisk;
        if (abs($sum - 1.0) > self::FLOAT_EPSILON) {
            throw new InvalidArgumentException(
                "La suma de ponderaciones wDistance y wRisk debe ser 1.0 (tolerancia " . self::FLOAT_EPSILON . "). Recibido: {$sum}"
            );
        }

        $this->alpha = $alpha;
        $this->beta = $beta;
        $this->rho = $rho;
        $this->Q = $Q;
        $this->ants = $ants;
        $this->iterations = $iterations;
        $this->initialPheromone = $initialPheromone;
        $this->convergenceEpsilon = $convergenceEpsilon;
        $this->convergencePatience = $convergencePatience;
        $this->seed = $seed;
        $this->wDistance = $wDistance;
        $this->wRisk = $wRisk;
    }
}
