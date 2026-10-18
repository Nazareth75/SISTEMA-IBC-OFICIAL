<?php

namespace App\Services\Aco;

use App\Models\CalleTramo;
use App\Models\Ruta;
use InvalidArgumentException;

/**
 * Servicio integral de Recomendación Dinámica de Rutas Peatonales mediante ACO.
 *
 * Responsabilidad ingenieril:
 * Conecta la capa de datos viales y de riesgo físico con el motor metaheurístico ACO:
 *
 *   Ruta / Tramos
 *         ↓
 *   ConstructorGrafoRuta
 *         ↓
 *   CalculadorRiesgoTramoAco (R_norm ∈ [0,1])
 *         ↓
 *   Grafo Dirigido con Costos C(e) = wD * D_norm + wR * R_norm
 *         ↓
 *   ServicioAco (Hormigas artificiales)
 *         ↓
 *   ServicioRutaReferencia (Dijkstra canónico determinista)
 *         ↓
 *   ResultadoRecomendacionAco (DTO enriquecido para la interfaz administrativa y Leaflet)
 *
 * Principio metodológico:
 * La función de costo C(e) = wD * D_norm + wR * R_norm se mantiene exactamente idéntica
 * tanto para ACO como para el algoritmo determinista de referencia, garantizando la validez
 * del cálculo de la métrica M2 (Gap).
 */
class ServicioRecomendacionRutaAco
{
    private ConstructorGrafoRuta $constructorGrafo;
    private ServicioAco $servicioAco;
    private ServicioRutaReferencia $servicioReferencia;
    private CalculadorRiesgoTramoAco $calculadorRiesgo;

    public function __construct(
        ?ConstructorGrafoRuta $constructorGrafo = null,
        ?ServicioAco $servicioAco = null,
        ?ServicioRutaReferencia $servicioReferencia = null,
        ?CalculadorRiesgoTramoAco $calculadorRiesgo = null
    ) {
        $this->constructorGrafo   = $constructorGrafo ?? app(ConstructorGrafoRuta::class);
        $this->servicioAco        = $servicioAco ?? app(ServicioAco::class);
        $this->servicioReferencia = $servicioReferencia ?? app(ServicioRutaReferencia::class);
        $this->calculadorRiesgo   = $calculadorRiesgo ?? app(CalculadorRiesgoTramoAco::class);
    }

    /**
     * Genera la recomendación de ruta óptima/subóptima evaluando topología y riesgo vial.
     *
     * @param Ruta                          $ruta             Ruta registrada en el sistema.
     * @param ConfiguracionAco|null         $config           Parámetros metaheurísticos opcionales.
     * @param CalculadorRiesgoTramoAco|null $calculadorRiesgo Ponderador de riesgo opcional.
     * @return ResultadoRecomendacionAco DTO con la recomendación y métricas comparativas.
     */
    public function recomendar(
        Ruta $ruta,
        ?ConfiguracionAco $config = null,
        ?CalculadorRiesgoTramoAco $calculadorRiesgo = null
    ): ResultadoRecomendacionAco {
        $calcRiesgo = $calculadorRiesgo ?? $this->calculadorRiesgo;
        $cfg = $config ?? ($ruta->ide_rut === 'DEMO-RUT-LP-OSM'
            ? new ConfiguracionAco(...config('movilidad.aco_demo_la_paz')) : new ConfiguracionAco());

        // 1. Análisis topológico preliminar
        $analisis = $this->constructorGrafo->analizar($ruta);
        if (!$analisis->esOptimizable) {
            $motivo = !empty($analisis->motivosBloqueo)
                ? implode(' ', $analisis->motivosBloqueo)
                : 'La ruta no cumple las condiciones topológicas o de cobertura para optimización.';

            return new ResultadoRecomendacionAco(
                success: false,
                mensajeError: $motivo,
                rutaId: $ruta->cod_rutas,
                rutaCodigo: $ruta->ide_rut,
                rutaNombre: $ruta->nom_rut,
                cantidadAlternativas: $analisis->cantidadAlternativas
            );
        }

        // 2. Construcción del grafo formal con cálculo de R_norm
        $grafo = $this->constructorGrafo->construirGrafo($ruta, [], $calcRiesgo);
        if (!$grafo) {
            return new ResultadoRecomendacionAco(
                success: false,
                mensajeError: 'No fue posible estructurar el grafo dirigido con los tramos asociados.',
                rutaId: $ruta->cod_rutas,
                rutaCodigo: $ruta->ide_rut,
                rutaNombre: $ruta->nom_rut,
                cantidadAlternativas: $analisis->cantidadAlternativas
            );
        }

        $nodoOrigen = $analisis->nodoOrigenId;
        $nodoDestino = $analisis->nodoDestinoId;

        if (!$nodoOrigen || !$nodoDestino || !$grafo->hasNode($nodoOrigen) || !$grafo->hasNode($nodoDestino)) {
            return new ResultadoRecomendacionAco(
                success: false,
                mensajeError: "Nodos extremos origen ({$nodoOrigen}) o destino ({$nodoDestino}) no encontrados en el grafo.",
                rutaId: $ruta->cod_rutas,
                rutaCodigo: $ruta->ide_rut,
                rutaNombre: $ruta->nom_rut,
                cantidadAlternativas: $analisis->cantidadAlternativas
            );
        }

        // 3. Ejecución del motor ACO
        $resAco = $this->servicioAco->solve($grafo, $nodoOrigen, $nodoDestino, $cfg);
        if (!$resAco->success) {
            return new ResultadoRecomendacionAco(
                success: false,
                mensajeError: 'El algoritmo ACO no logró converger hacia una trayectoria factible.',
                rutaId: $ruta->cod_rutas,
                rutaCodigo: $ruta->ide_rut,
                rutaNombre: $ruta->nom_rut,
                tiempoEjecucionMs: $resAco->executionTimeMs,
                cantidadAlternativas: $analisis->cantidadAlternativas
            );
        }

        // 4. Ejecución del algoritmo determinista de referencia con la MISMA función de costo
        $wD = $cfg->wDistance;
        $wR = $cfg->wRisk;
        $refDijkstra = $this->servicioReferencia->solve($grafo, $nodoOrigen, $nodoDestino, $wD, $wR);

        // M2: Gap respecto al óptimo determinista
        $gap = ServicioExperimentosAco::calculateGap($resAco->totalCost, $refDijkstra->totalCost);

        // 5. Cálculo independiente de la ruta de menor distancia física (wD=1.0, wR=0.0)
        // Se utiliza estrictamente como línea base para las métricas de compromiso M7 y M8
        $solMasCorta = $this->servicioReferencia->solve($grafo, $nodoOrigen, $nodoDestino, 1.0, 0.0);
        $distanciaCaminoCorto = $solMasCorta->totalDistance;
        $riesgoCaminoCorto = $solMasCorta->totalRisk;

        // M7: Reducción de riesgo peatonal (%)
        $m7 = ServicioExperimentosAco::calculateRiskReduction($riesgoCaminoCorto, $resAco->totalRisk);

        // M8: Incremento de distancia física (%)
        $m8 = ServicioExperimentosAco::calculateDistanceIncrease($distanciaCaminoCorto, $resAco->totalDistance);

        // 6. Mapeo de aristas a tramos físicos y polilíneas cartográficas
        $aristasRuta = $analisis->aristas;
        $tramosKeyed = $ruta->tramos->keyBy('cod_calles_tramos');

        $aristaIndex = [];
        foreach ($aristasRuta as $arista) {
            $clave = $arista['origen'] . '->' . $arista['destino'];
            $aristaIndex[$clave] = $arista;
        }

        // Extracción de tramos y geometría de la ruta recomendada por ACO
        $tramosSeleccionadosIds = [];
        $tramosSeleccionadosNombres = [];
        $geometriaRecomendada = [];

        for ($i = 0; $i < count($resAco->path) - 1; $i++) {
            $u = $resAco->path[$i];
            $v = $resAco->path[$i + 1];
            $clave = "{$u}->{$v}";
            if (isset($aristaIndex[$clave])) {
                $aInfo = $aristaIndex[$clave];
                $tramosSeleccionadosIds[] = $aInfo['tramo_id'];
                $tramosSeleccionadosNombres[] = $aInfo['nombre'];
                $geometriaRecomendada[] = $aInfo['geo'];
            }
        }

        // Extracción de geometría del camino más corto
        $geometriaMasCorta = [];
        $caminoCortoTramosIds = [];
        for ($i = 0; $i < count($solMasCorta->path) - 1; $i++) {
            $u = $solMasCorta->path[$i];
            $v = $solMasCorta->path[$i + 1];
            $clave = "{$u}->{$v}";
            if (isset($aristaIndex[$clave])) {
                $aInfo = $aristaIndex[$clave];
                $caminoCortoTramosIds[] = $aInfo['tramo_id'];
                $geometriaMasCorta[] = $aInfo['geo'];
            }
        }

        // Geometrías de alternativas restantes para capas neutras en Leaflet
        $geometriaAlternativas = [];
        foreach ($aristasRuta as $arista) {
            $geometriaAlternativas[] = $arista['geo'];
        }

        // 7. Censo de obstáculos en los tramos de la ruta para renderizado de marcadores
        $obstaculosActivosCount = 0;
        $obstaculosResueltosCount = 0;
        $obstaculosDetalle = [];

        foreach ($ruta->tramos as $tramo) {
            foreach ($tramo->obstaculos as $obs) {
                $esActivo = in_array($obs->est_obs, ['ACTIVO', 'REPORTADO']);
                if ($esActivo) {
                    $obstaculosActivosCount++;
                } elseif ($obs->est_obs === 'RESUELTO') {
                    $obstaculosResueltosCount++;
                }

                // Extraer coordenadas representativas del tramo o del primer reporte
                $geoTramo = $tramo->geo_ctr;
                $lat = null;
                $lng = null;
                if (!empty($obs->reportes) && $obs->reportes->isNotEmpty()) {
                    $primerRep = $obs->reportes->first();
                    $lat = $primerRep->latitud;
                    $lng = $primerRep->longitud;
                }
                if ($lat === null && is_array($geoTramo) && count($geoTramo) > 0) {
                    $lat = $geoTramo[0]['lat'] ?? null;
                    $lng = $geoTramo[0]['lng'] ?? null;
                }

                $obstaculosDetalle[] = [
                    'id'          => $obs->cod_obstaculos,
                    'tramo_id'    => $tramo->cod_calles_tramos,
                    'tramo_nom'   => $tramo->nom_ctr,
                    'tipo'        => $obs->tip_obs,
                    'descripcion' => $obs->des_obs,
                    'estado'      => $obs->est_obs,
                    'es_activo'   => $esActivo,
                    'lat'         => $lat,
                    'lng'         => $lng,
                ];
            }
        }

        // 8. Generación de motivo de recomendación y nivel cualitativo de riesgo
        $motivo = $this->generarExplicacionRecomendacion(
            $resAco->path,
            $solMasCorta->path,
            $resAco->totalDistance,
            $distanciaCaminoCorto,
            $m7,
            $obstaculosActivosCount
        );

        $promedioRiesgoArista = count($tramosSeleccionadosIds) > 0
            ? $resAco->totalRisk / count($tramosSeleccionadosIds)
            : 0.0;

        $nivelRiesgo = match (true) {
            $promedioRiesgoArista < 0.15 => 'BAJO',
            $promedioRiesgoArista < 0.40 => 'MEDIO',
            $promedioRiesgoArista < 0.70 => 'ALTO',
            default                      => 'CRÍTICO',
        };

        // Resumen de Métricas M1 - M8
        $metricas = [
            'M1' => [
                'clave'       => 'M1_costo_mejor_ruta',
                'nombre'      => 'Costo de la mejor ruta',
                'valor'       => round($resAco->totalCost, 4),
                'unidad'      => 'adimensional',
                'descripcion' => 'Costo multiobjetivo ponderado normalizado.',
            ],
            'M2' => [
                'clave'       => 'M2_gap_referencia',
                'nombre'      => 'Gap vs Referencia determinista',
                'valor'       => $gap !== null ? round($gap, 2) : null,
                'unidad'      => '%',
                'descripcion' => 'Brecha porcentual respecto al óptimo determinista de Dijkstra.',
            ],
            'M3' => [
                'clave'       => 'M3_tiempo_ejecucion',
                'nombre'      => 'Tiempo de ejecución',
                'valor'       => round($resAco->executionTimeMs, 2),
                'unidad'      => 'ms',
                'descripcion' => 'Tiempo de cómputo del motor ACO.',
            ],
            'M4' => [
                'clave'       => 'M4_convergencia',
                'nombre'      => 'Iteración de hallazgo y convergencia',
                'valor'       => "Iteración {$resAco->bestFoundIteration} (convergencia: {$resAco->convergenceIteration})",
                'unidad'      => 'iteraciones',
                'descripcion' => 'Punto de estabilización de la colonia.',
            ],
            'M5' => [
                'clave'       => 'M5_tasa_exito',
                'nombre'      => 'Tasa de éxito',
                'valor'       => null,
                'unidad'      => '%',
                'descripcion' => 'No evaluada: requiere un conjunto de ejecuciones experimentales.',
            ],
            'M6' => [
                'clave'       => 'M6_estabilidad',
                'nombre'      => 'Estabilidad del costo',
                'valor'       => null,
                'unidad'      => 'costo',
                'descripcion' => 'No evaluada: requiere media, desviación y CV de varias ejecuciones.',
            ],
            'M7' => [
                'clave'       => 'M7_reduccion_riesgo',
                'nombre'      => 'Reducción de riesgo vs ruta más corta',
                'valor'       => $m7 !== null ? round($m7, 1) : null,
                'unidad'      => '%',
                'descripcion' => 'Porcentaje de mitigación de peligro peatonal.',
            ],
            'M8' => [
                'clave'       => 'M8_incremento_distancia',
                'nombre'      => 'Incremento de distancia vs ruta más corta',
                'valor'       => $m8 !== null ? round($m8, 1) : null,
                'unidad'      => '%',
                'descripcion' => 'Distancia adicional requerida para evitar riesgos.',
            ],
        ];

        return new ResultadoRecomendacionAco(
            success: true,
            rutaId: $ruta->cod_rutas,
            rutaCodigo: $ruta->ide_rut,
            rutaNombre: $ruta->nom_rut,
            nodosSeleccionados: $resAco->path,
            tramosSeleccionadosIds: $tramosSeleccionadosIds,
            tramosSeleccionadosNombres: $tramosSeleccionadosNombres,
            distanciaTotal: $resAco->totalDistance,
            riesgoTotal: $resAco->totalRisk,
            costoTotal: $resAco->totalCost,
            tiempoEjecucionMs: $resAco->executionTimeMs,
            bestFoundIteration: $resAco->bestFoundIteration,
            convergenceIteration: $resAco->convergenceIteration,
            costoReferencia: $refDijkstra->totalCost,
            gap: $gap,
            caminoMenorDistanciaNodos: $solMasCorta->path,
            caminoMenorDistanciaTramosIds: $caminoCortoTramosIds,
            distanciaCaminoCorto: $distanciaCaminoCorto,
            riesgoCaminoCorto: $riesgoCaminoCorto,
            m7: $m7,
            m8: $m8,
            cantidadAlternativas: $analisis->cantidadAlternativas,
            motivoRecomendacion: $motivo,
            nivelRiesgoEstimado: $nivelRiesgo,
            obstaculosActivosCount: $obstaculosActivosCount,
            obstaculosResueltosCount: $obstaculosResueltosCount,
            obstaculosDetalle: $obstaculosDetalle,
            geometriaRecomendada: $geometriaRecomendada,
            geometriaMasCorta: $geometriaMasCorta,
            geometriaAlternativas: $geometriaAlternativas,
            bestCostHistory: $resAco->bestCostHistory,
            metricas: $metricas,
            feromonasTramos: array_map(fn ($arista) => [
                'tramo_id' => $arista['tramo_id'], 'geo' => $arista['geo'],
                'tau' => $resAco->pheromones[$arista['origen'].'->'.$arista['destino']] ?? null,
            ], $aristasRuta),
            parametrosEjecucion: ['alpha'=>$cfg->alpha,'beta'=>$cfg->beta,'rho'=>$cfg->rho,'Q'=>$cfg->Q,
                'hormigas'=>$cfg->ants,'iteraciones'=>$cfg->iterations,'semilla'=>$cfg->seed,
                'peso_distancia'=>$cfg->wDistance,'peso_riesgo'=>$cfg->wRisk],
            caminosCandidatos: (new CaminosVisualesAco())->generar($analisis,$grafo,$resAco,$cfg)
        );
    }

    /**
     * Construye una explicación clara y accesible para el usuario administrador.
     */
    private function generarExplicacionRecomendacion(
        array $pathAco,
        array $pathCorto,
        float $distanciaAco,
        float $distanciaCorta,
        ?float $m7,
        int $obstaculosActivos
    ): string {
        $mismoCamino = ($pathAco === $pathCorto);

        if ($mismoCamino) {
            return "La ruta recomendada coincide con el camino de menor distancia al no presentar riesgos significativos en sus tramos.";
        }

        $distanciaExtra = round($distanciaAco - $distanciaCorta, 1);

        if ($m7 !== null && $m7 > 0.0) {
            $m7Redondeado = round($m7, 1);
            return "Evita los tramos con mayor riesgo (reducción de riesgo del {$m7Redondeado}%) " .
                   "aunque implica una distancia adicional ({$distanciaExtra} m más) respecto a la ruta más corta.";
        }

        return "Equilibra la distancia de recorrido ({$distanciaAco} m) con la seguridad peatonal mitigando tramos críticos.";
    }
}
