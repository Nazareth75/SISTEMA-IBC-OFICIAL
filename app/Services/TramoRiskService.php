<?php

namespace App\Services;

use App\Models\CalleTramo;

class TramoRiskService
{
    /**
     * Constantes de Cobertura de Datos.
     * SIN_DATOS = ninguna de las 4 fuentes posee registros.
     * PARCIAL   = existen registros en algunas fuentes.
     * COMPLETO  = existen registros en las 4 fuentes consideradas.
     * 
     * NOTA IMPORTANTE: COMPLETO NO significa "tramo totalmente seguro" ni "inspección exhaustiva".
     * Expresa únicamente la disponibilidad de registros cargados en las fuentes consultadas.
     */
    public const COBERTURA_SIN_DATOS = 'SIN_DATOS';
    public const COBERTURA_PARCIAL   = 'PARCIAL';
    public const COBERTURA_COMPLETO  = 'COMPLETO';

    // Alias mantenidos por compatibilidad técnica interna
    public const COMPLETITUD_SIN_DATOS = self::COBERTURA_SIN_DATOS;
    public const COMPLETITUD_PARCIAL   = self::COBERTURA_PARCIAL;
    public const COMPLETITUD_COMPLETO  = self::COBERTURA_COMPLETO;

    /**
     * Mapa de normalización para condiciones del entorno.
     * BUENO    -> 0.00
     * REGULAR  -> 0.50
     * MALO     -> 1.00
     */
    public const MAPA_NIVEL_CONDICION = [
        'BUENO'   => 0.00,
        'REGULAR' => 0.50,
        'MALO'    => 1.00,
    ];

    /**
     * Estados de obstáculos considerados vigentes (afectan perfil actual).
     */
    public const ESTADOS_OBSTACULO_VIGENTES = ['ACTIVO', 'REPORTADO'];

    /**
     * Estados de reportes considerados vigentes (afectan prioridad máxima actual).
     */
    public const ESTADOS_REPORTE_VIGENTES = ['REPORTADO', 'ACTIVO'];

    /**
     * Calcula y devuelve el perfil cuantitativo normalizado de riesgo de un tramo.
     * 
     * NOTA: Este método NO calcula un 'riesgo_final', NO aplica ponderaciones arbitrarias
     * ni persiste puntuaciones en la base de datos.
     *
     * @param CalleTramo $tramo
     * @return array
     */
    public function obtenerPerfil(CalleTramo $tramo): array
    {
        // 1. RIESGO DE EVALUACIÓN TÉCNICA
        $ultimaEval = $tramo->evaluaciones()
            ->with('nivelSeguridad')
            ->whereNull('fbj_eri')
            ->latest('fee_eri')
            ->first();

        $riesgoEvaluacion = null;
        $estadoEvaluacion = 'NO_EVALUADO';
        $nivelSeguridadNombre = null;

        if ($ultimaEval && $ultimaEval->nivelSeguridad) {
            $nivNse = (int) $ultimaEval->nivelSeguridad->niv_nse;
            // Formula: riesgo_evaluacion = (5 - niv_nse) / 4
            $riesgoEvaluacion = round((5 - $nivNse) / 4, 2);
            $estadoEvaluacion = 'EVALUADO';
            $nivelSeguridadNombre = $ultimaEval->nivelSeguridad->nom_nse;
        }

        // 2. CONDICIONES DEL ENTORNO
        $condiciones = $tramo->condiciones()
            ->whereNull('fbj_cen')
            ->get();

        $riesgoCondiciones = null;
        $condicionesDetalle = [];

        if ($condiciones->isNotEmpty()) {
            $sumaCondiciones = 0.0;
            foreach ($condiciones as $cond) {
                $val = self::MAPA_NIVEL_CONDICION[$cond->niv_cen] ?? 0.50;
                $sumaCondiciones += $val;

                $condicionesDetalle[] = [
                    'id'                 => $cond->cod_condiciones_entorno,
                    'tipo'               => $cond->tip_cen,
                    'descripcion'        => $cond->des_cen,
                    'nivel'              => $cond->niv_cen,
                    'riesgo_normalizado' => $val,
                ];
            }
            $riesgoCondiciones = round($sumaCondiciones / $condiciones->count(), 2);
        }

        // 3. ZONAS DE RIESGO ACTIVAS
        $zonasActivas = $tramo->zonas()
            ->with('nivelSeguridad')
            ->whereNull('fbj_zri')
            ->where('act_zri', true)
            ->get();

        $riesgoZonasMax = null;
        $zonasDetalle = [];

        if ($zonasActivas->isNotEmpty()) {
            $maxRiesgoZona = null;
            foreach ($zonasActivas as $zona) {
                if ($zona->nivelSeguridad) {
                    $nivZri = (int) $zona->nivelSeguridad->niv_nse;
                    $rZona = (5 - $nivZri) / 4;

                    if ($maxRiesgoZona === null || $rZona > $maxRiesgoZona) {
                        $maxRiesgoZona = $rZona;
                    }

                    $zonasDetalle[] = [
                        'id'                 => $zona->cod_zonas_riesgo,
                        'nombre'             => $zona->nom_zri,
                        'nivel'              => $zona->nivelSeguridad->nom_nse,
                        'riesgo_normalizado' => round($rZona, 2),
                    ];
                }
            }

            if ($maxRiesgoZona !== null) {
                $riesgoZonasMax = round($maxRiesgoZona, 2);
            }
        }

        // 4. OBSTÁCULOS Y REPORTES VIGENTES
        // Considerar únicamente obstáculos en estados ACTIVO y REPORTADO.
        // Excluye obstáculos en estado RESUELTO o con soft delete (fbj_obs).
        $obstaculosVigentes = $tramo->obstaculos()
            ->with(['reportes' => function ($q) {
                $q->whereIn('estado', self::ESTADOS_REPORTE_VIGENTES);
            }])
            ->whereNull('fbj_obs')
            ->whereIn('est_obs', self::ESTADOS_OBSTACULO_VIGENTES)
            ->get();

        $obstaculosActivosCount    = $obstaculosVigentes->where('est_obs', 'ACTIVO')->count();
        $obstaculosReportadosCount = $obstaculosVigentes->where('est_obs', 'REPORTADO')->count();
        $tiposObstaculos           = $obstaculosVigentes->pluck('tip_obs')->unique()->values()->all();

        $tieneReportesAsociados = false;
        $prioridadMaxima        = null;

        foreach ($obstaculosVigentes as $obs) {
            // Filtrar reportes asociados en estado REPORTADO o ACTIVO (excluye DESCARTADO y RESUELTO)
            $reportesVigentes = $obs->reportes->filter(
                fn ($r) => in_array($r->estado, self::ESTADOS_REPORTE_VIGENTES)
            );

            if ($reportesVigentes->isNotEmpty()) {
                $tieneReportesAsociados = true;
                foreach ($reportesVigentes as $rep) {
                    if ($rep->prioridad !== null && $rep->prioridad >= 1 && $rep->prioridad <= 4) {
                        $prio = (int) $rep->prioridad;
                        if ($prioridadMaxima === null || $prio > $prioridadMaxima) {
                            $prioridadMaxima = $prio;
                        }
                    }
                }
            }
        }

        $prioridadNormalizada = null;
        if ($prioridadMaxima !== null) {
            $prioridadNormalizada = round($prioridadMaxima / 4, 2);
        }

        // 5. COBERTURA DE DATOS (4 Dimensiones)
        $dimensionesConDatos = 0;
        $totalDimensiones    = 4;

        if ($ultimaEval !== null) {
            $dimensionesConDatos++;
        }
        if ($condiciones->isNotEmpty()) {
            $dimensionesConDatos++;
        }
        if ($tramo->zonas()->whereNull('fbj_zri')->exists()) {
            $dimensionesConDatos++;
        }
        if ($obstaculosVigentes->isNotEmpty()) {
            $dimensionesConDatos++;
        }

        if ($dimensionesConDatos === 0) {
            $cobertura = self::COBERTURA_SIN_DATOS;
        } elseif ($dimensionesConDatos === $totalDimensiones) {
            $cobertura = self::COBERTURA_COMPLETO;
        } else {
            $cobertura = self::COBERTURA_PARCIAL;
        }

        return [
            'tramo_id'                   => $tramo->getKey(),
            'nom_ctr'                    => $tramo->nom_ctr,
            'longitud'                   => $tramo->lon_ctr !== null ? (float) $tramo->lon_ctr : null,
            'tipo'                       => $tramo->tip_ctr,

            'riesgo_evaluacion'          => $riesgoEvaluacion,
            'estado_evaluacion'          => $estadoEvaluacion,
            'nivel_seguridad_nombre'     => $nivelSeguridadNombre,

            'riesgo_condiciones'         => $riesgoCondiciones,
            'condiciones_count'          => $condiciones->count(),
            'condiciones_detalle'        => $condicionesDetalle,

            'riesgo_zonas_max'           => $riesgoZonasMax,
            'zonas_activas_count'        => $zonasActivas->count(),
            'zonas_detalle'              => $zonasDetalle,

            'obstaculos_activos_count'   => $obstaculosActivosCount,
            'obstaculos_reportados_count' => $obstaculosReportadosCount,
            'tipos_obstaculos'           => $tiposObstaculos,
            'tiene_reportes_asociados'   => $tieneReportesAsociados,
            'prioridad_maxima'           => $prioridadMaxima,
            'prioridad_normalizada'      => $prioridadNormalizada,

            'cobertura_datos'            => $cobertura,
            'completitud_datos'          => $cobertura, // alias de compatibilidad técnica
        ];
    }

    /**
     * Alias de obtenerPerfil para compatibilidad de nomenclatura.
     */
    public function calcularPerfil(CalleTramo $tramo): array
    {
        return $this->obtenerPerfil($tramo);
    }
}
