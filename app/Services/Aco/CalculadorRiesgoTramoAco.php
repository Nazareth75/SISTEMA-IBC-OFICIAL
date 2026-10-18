<?php

namespace App\Services\Aco;

use App\Models\CalleTramo;
use App\Services\TramoRiskService;
use InvalidArgumentException;

/**
 * Calculador y agregador multivariable de riesgo normalizado para aristas del algoritmo ACO.
 *
 * Responsabilidad ingenieril:
 * Transforma los 4 componentes cuantitativos de riesgo vial de un tramo
 * (evaluación técnica, condiciones del entorno, zonas de riesgo y obstáculos vigentes)
 * en un único índice escalar normalizado:
 *
 *   R_norm ∈ [0.0, 1.0]
 *
 * Modelo de agregación multiobjetivo:
 *   R_norm = w_E * R_evaluacion + w_C * R_condiciones + w_Z * R_zonas + w_O * R_obstaculos
 *
 * Restricciones canónicas:
 *   w_E + w_C + w_Z + w_O = 1.0  (con w_i >= 0.0)
 *
 * Distinción conceptual crítica (Fase 5B):
 * 1. SIN_DATOS ≠ RIESGO CERO: La ausencia de información (cobertura SIN_DATOS o valores nulos
 *    por falta de inspección) se rechaza explícitamente mediante InvalidArgumentException.
 *    No se inventa un cero artificial.
 * 2. Ausencia verificada de riesgo: Si un tramo fue evaluado/relevado técnicamente y cuenta con
 *    cero zonas de riesgo activas o cero obstáculos vigentes, el componente correspondiente
 *    es legítimamente 0.00.
 * 3. Obstáculos RESUELTOS y reportes DESCARTADOS: No incrementan el riesgo vigente del tramo.
 */
class CalculadorRiesgoTramoAco
{
    /**
     * Ponderación asignada a la evaluación técnica de riesgo vial (w_E).
     */
    private float $pesoEvaluacion;

    /**
     * Ponderación asignada a las condiciones del entorno peatonal (w_C).
     */
    private float $pesoCondiciones;

    /**
     * Ponderación asignada al impacto de zonas de riesgo concurrentes (w_Z).
     */
    private float $pesoZonas;

    /**
     * Ponderación asignada a la prioridad de obstáculos vigentes (w_O).
     */
    private float $pesoObstaculos;

    /**
     * Servicio evaluador de riesgo físico de tramos.
     */
    private TramoRiskService $riskService;

    /**
     * Tolerancia numérica para validar que la suma de ponderaciones sea exactamente 1.0.
     */
    public const TOLERANCIA_SUMA_PESOS = 1e-4;

    /**
     * Constructor del agregador de riesgo.
     *
     * @param float                 $pesoEvaluacion Ponderación de evaluación técnica (base 0.25).
     * @param float                 $pesoCondiciones Ponderación de condiciones del entorno (base 0.25).
     * @param float                 $pesoZonas Ponderación de zonas de riesgo (base 0.25).
     * @param float                 $pesoObstaculos Ponderación de obstáculos vigentes (base 0.25).
     * @param TramoRiskService|null $riskService Instancia del evaluador de perfil de tramo.
     *
     * @throws InvalidArgumentException Si algún peso es negativo, infinito, NaN o si la suma difiere de 1.0.
     */
    public function __construct(
        float $pesoEvaluacion = 0.25,
        float $pesoCondiciones = 0.25,
        float $pesoZonas = 0.25,
        float $pesoObstaculos = 0.25,
        ?TramoRiskService $riskService = null
    ) {
        $pesos = [
            'pesoEvaluacion'  => $pesoEvaluacion,
            'pesoCondiciones' => $pesoCondiciones,
            'pesoZonas'       => $pesoZonas,
            'pesoObstaculos'  => $pesoObstaculos,
        ];

        foreach ($pesos as $nombre => $valor) {
            if (is_nan($valor) || is_infinite($valor)) {
                throw new InvalidArgumentException("El peso '{$nombre}' debe ser un número finito válido.");
            }
            if ($valor < 0.0) {
                throw new InvalidArgumentException("El peso '{$nombre}' no puede ser negativo ({$valor}).");
            }
        }

        $sumaPesos = $pesoEvaluacion + $pesoCondiciones + $pesoZonas + $pesoObstaculos;
        if (abs($sumaPesos - 1.0) > self::TOLERANCIA_SUMA_PESOS) {
            throw new InvalidArgumentException(
                "La suma de los pesos de riesgo debe ser igual a 1.0 (suma actual: {$sumaPesos})."
            );
        }

        $this->pesoEvaluacion  = $pesoEvaluacion;
        $this->pesoCondiciones = $pesoCondiciones;
        $this->pesoZonas       = $pesoZonas;
        $this->pesoObstaculos  = $pesoObstaculos;
        $this->riskService     = $riskService ?? app(TramoRiskService::class);
    }

    /**
     * Calcula R_norm a partir de un modelo CalleTramo persistido.
     *
     * @throws InvalidArgumentException Si el tramo carece de datos o presenta inconsistencias.
     */
    public function calcular(CalleTramo $tramo): float
    {
        $perfil = $this->riskService->obtenerPerfil($tramo);
        return $this->calcularDesdePerfil($perfil);
    }

    /**
     * Calcula R_norm a partir de la estructura de perfil producida por TramoRiskService.
     *
     * @param array<string, mixed> $perfil Perfil cuantitativo del tramo.
     * @return float Valor escalar R_norm ∈ [0.0, 1.0].
     *
     * @throws InvalidArgumentException Si el tramo tiene cobertura SIN_DATOS o valores nulos no justificados.
     */
    public function calcularDesdePerfil(array $perfil): float
    {
        // 1. REGLA CRÍTICA: Rechazar explícitamente tramos sin datos de riesgo
        $cobertura = $perfil['cobertura_datos'] ?? TramoRiskService::COBERTURA_SIN_DATOS;
        if ($cobertura === TramoRiskService::COBERTURA_SIN_DATOS) {
            $nom = $perfil['nom_ctr'] ?? 'DESCONOCIDO';
            throw new InvalidArgumentException(
                "El tramo '{$nom}' carece de información de riesgo (cobertura SIN_DATOS). " .
                "No se permite asignar riesgo cero a la ausencia de datos."
            );
        }

        // 2. Dimensión 1: Evaluación Técnica
        if (($perfil['estado_evaluacion'] ?? 'NO_EVALUADO') === 'NO_EVALUADO' || !isset($perfil['riesgo_evaluacion'])) {
            $nom = $perfil['nom_ctr'] ?? 'DESCONOCIDO';
            throw new InvalidArgumentException(
                "El tramo '{$nom}' no posee evaluación técnica registrada (riesgo_evaluacion nulo)."
            );
        }
        $rEval = $perfil['riesgo_evaluacion'];

        // 3. Dimensión 2: Condiciones del Entorno
        if (!isset($perfil['riesgo_condiciones']) && ($perfil['condiciones_count'] ?? 0) === 0) {
            $nom = $perfil['nom_ctr'] ?? 'DESCONOCIDO';
            throw new InvalidArgumentException(
                "El tramo '{$nom}' no posee condiciones de entorno registradas (condiciones_count = 0)."
            );
        }
        $rCond = $perfil['riesgo_condiciones'] ?? 0.0;

        // 4. Dimensión 3: Zonas de Riesgo
        // Si existen zonas activas, se toma el máximo normalizado.
        // Si zonas_activas_count === 0, representa ausencia verificada de peligro zonal (0.0).
        if (isset($perfil['riesgo_zonas_max'])) {
            $rZonas = $perfil['riesgo_zonas_max'];
        } elseif (($perfil['zonas_activas_count'] ?? 0) === 0) {
            $rZonas = 0.0;
        } else {
            $nom = $perfil['nom_ctr'] ?? 'DESCONOCIDO';
            throw new InvalidArgumentException(
                "El riesgo de zonas para el tramo '{$nom}' es nulo pero posee zonas activas no tipadas."
            );
        }

        // 5. Dimensión 4: Obstáculos Vigentes
        // - Si no existen obstáculos vigentes (activos ni reportados), el riesgo es legítimamente 0.0.
        // - Si existen reportes vigentes con prioridad tipada, se toma la prioridad normalizada [0.25, 1.0].
        // - Si existen obstáculos activos creados sin reporte asociado, se aplica penalización de prioridad intermedia (0.50).
        // - Obstáculos RESUELTOS y reportes DESCARTADOS ya fueron excluidos por TramoRiskService.
        $activosCount    = (int) ($perfil['obstaculos_activos_count'] ?? 0);
        $reportadosCount = (int) ($perfil['obstaculos_reportados_count'] ?? 0);

        if ($activosCount === 0 && $reportadosCount === 0) {
            $rObstaculos = 0.0;
        } elseif (isset($perfil['prioridad_normalizada'])) {
            $rObstaculos = $perfil['prioridad_normalizada'];
        } else {
            // Obstáculo(s) vigentes detectados pero sin reporte ciudadano con prioridad formal
            $rObstaculos = 0.50;
        }

        return $this->calcularDesdeComponentes((float) $rEval, (float) $rCond, (float) $rZonas, (float) $rObstaculos);
    }

    /**
     * Calcula la agregación escalar ponderada a partir de los 4 componentes numéricos.
     *
     * @param float $rEvaluacion  Riesgo de evaluación técnica ∈ [0.0, 1.0].
     * @param float $rCondiciones Riesgo promedio de condiciones ∈ [0.0, 1.0].
     * @param float $rZonas       Riesgo máximo de zonas activas ∈ [0.0, 1.0].
     * @param float $rObstaculos  Riesgo/prioridad de obstáculos vigentes ∈ [0.0, 1.0].
     * @return float R_norm ∈ [0.0, 1.0].
     *
     * @throws InvalidArgumentException Si algún componente es negativo, mayor a 1.0, NaN o infinito.
     */
    public function calcularDesdeComponentes(
        float $rEvaluacion,
        float $rCondiciones,
        float $rZonas,
        float $rObstaculos
    ): float {
        $componentes = [
            'rEvaluacion'  => $rEvaluacion,
            'rCondiciones' => $rCondiciones,
            'rZonas'       => $rZonas,
            'rObstaculos'  => $rObstaculos,
        ];

        foreach ($componentes as $nombre => $val) {
            if (is_nan($val) || is_infinite($val)) {
                throw new InvalidArgumentException("El componente de riesgo '{$nombre}' debe ser un número finito.");
            }
            if ($val < 0.0 || $val > 1.0) {
                throw new InvalidArgumentException(
                    "El componente de riesgo '{$nombre}' ({$val}) debe estar acotado estrictamente en [0.0, 1.0]."
                );
            }
        }

        $rNorm = ($this->pesoEvaluacion * $rEvaluacion) +
                 ($this->pesoCondiciones * $rCondiciones) +
                 ($this->pesoZonas * $rZonas) +
                 ($this->pesoObstaculos * $rObstaculos);

        // Protección contra desbordamiento o imprecisión de punto flotante
        $rNormClamped = max(0.0, min(1.0, $rNorm));

        return round($rNormClamped, 4);
    }

    /**
     * Retorna los pesos de ponderación configurados actualmente.
     *
     * @return array{w_evaluacion: float, w_condiciones: float, w_zonas: float, w_obstaculos: float}
     */
    public function obtenerPesos(): array
    {
        return [
            'w_evaluacion'  => $this->pesoEvaluacion,
            'w_condiciones' => $this->pesoCondiciones,
            'w_zonas'       => $this->pesoZonas,
            'w_obstaculos'  => $this->pesoObstaculos,
        ];
    }
}
