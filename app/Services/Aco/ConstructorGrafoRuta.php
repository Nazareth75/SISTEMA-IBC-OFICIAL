<?php

namespace App\Services\Aco;

use App\Models\CalleTramo;
use App\Models\Ruta;
use App\Services\TramoRiskService;

/**
 * Servicio encargado de transformar los tramos físicos de una Ruta en una estructura de grafo topológico analizable para ACO.
 *
 * Responsabilidad ingenieril:
 * 1. Carga los tramos asociados en relación N:M respetando la tabla pivote ruta_calles_tramos.
 * 2. Extrae las geometrías viales (geo_ctr) e indexa los puntos extremos mediante tolerancia geográfica espacial.
 * 3. Construye la lista de adyacencia del grafo dirigido/indireccionado.
 * 4. Analiza la topología: conectividad, detección de bifurcaciones y conteo de caminos acíclicos posibles.
 * 5. Consulta el perfil de riesgo cuantitativo a través de TramoRiskService sin alterar sus datos.
 * 6. Determina formalmente el estado de preparación de la ruta frente a ACO (EstadoAcoRuta).
 */
class ConstructorGrafoRuta
{
    /**
     * Tolerancia provisional de asociación topológica en metros para agrupar puntos extremos en un mismo nodo topológico.
     *
     * Nota técnica:
     * Corresponde a una tolerancia provisional de asociación topológica (8.0 m),
     * parametrizable y pendiente de validación con datos de campo.
     * En entornos urbanos peatonales permite enlazar segmentos concurrentes en esquinas sin fusionar esquinas distintas.
     */
    public const TOLERANCIA_METROS = 8.0;
    private float $toleranciaMetros = self::TOLERANCIA_METROS;

    /**
     * Radio medio volumétrico de la Tierra en metros para el cálculo geodésico Haversine.
     */
    private const RADIO_TIERRA_METROS = 6371000.0;

    /**
     * Límite preventivo de exploración en búsqueda en profundidad (DFS) para evitar explosión combinatoria.
     */
    public const LIMITE_EXPLORACION_CAMINOS = 50;

    /**
     * Dependencia del servicio evaluador de riesgo vial.
     */
    private TramoRiskService $riskService;

    public function __construct(?TramoRiskService $riskService = null)
    {
        $this->riskService = $riskService ?? app(TramoRiskService::class);
    }

    /**
     * Analiza exhaustivamente una ruta y produce un informe tipado de preparación para ACO.
     */
    public function analizar(Ruta $ruta): AnalisisAcoRuta
    {
        // OSM puede representar extremos distintos de un cruce a menos de 8 m.
        // Una tolerancia explícita preserva esos nodos sin cambiar la base histórica.
        $tolerancia = data_get($ruta->geo_rut, 'tolerancia_topologica_m', self::TOLERANCIA_METROS);
        $this->toleranciaMetros = is_numeric($tolerancia) && $tolerancia > 0 && $tolerancia <= self::TOLERANCIA_METROS
            ? (float) $tolerancia : self::TOLERANCIA_METROS;
        // 1. Cargar tramos ordenados desde la relación N:M
        $tramos = $ruta->tramos;

        if ($tramos->isEmpty()) {
            return $this->construirAnalisisSinTramos($ruta);
        }

        // 2. Extraer geometrías y mapear coordenadas extremas a nodos espaciales
        $nodos = [];
        $aristas = [];
        $adj = [];
        $tramosDetalle = [];
        $tramosConGeoValida = 0;
        $totalLongitud = 0.0;

        $dimensionesTotales = 0;
        $tramosConRiesgoMedible = 0;
        $conteoEstadosCobertura = [
            TramoRiskService::COBERTURA_COMPLETO  => 0,
            TramoRiskService::COBERTURA_PARCIAL   => 0,
            TramoRiskService::COBERTURA_SIN_DATOS => 0,
        ];

        foreach ($tramos as $tramo) {
            $geo = $tramo->geo_ctr;
            $esValido = CalleTramo::esGeoValido($geo);

            $nodoIni = null;
            $nodoFin = null;
            $longitud = (float) ($tramo->lon_ctr ?? 0.0);

            if ($esValido && is_array($geo)) {
                $tramosConGeoValida++;
                $pInicial = $geo[0];
                $pFinal = end($geo);

                $nodoIni = $this->encontrarOCrearNodo((float) $pInicial['lat'], (float) $pInicial['lng'], $nodos);
                $nodoFin = $this->encontrarOCrearNodo((float) $pFinal['lat'], (float) $pFinal['lng'], $nodos);

                if ($longitud <= 0.0) {
                    $longitud = CalleTramo::calcularLongitudGeodesica($geo);
                }

                // Registrar arista en el grafo
                $aristaId = 'E' . count($aristas);
                $aristas[] = [
                    'id'          => $aristaId,
                    'tramo_id'    => $tramo->cod_calles_tramos,
                    'nombre'      => $tramo->nom_ctr,
                    'tipo'        => $tramo->tip_ctr,
                    'origen'      => $nodoIni,
                    'destino'     => $nodoFin,
                    'longitud'    => $longitud,
                    'geo'         => $geo,
                ];

                // Lista de adyacencia dirigida hacia adelante
                $adj[$nodoIni][] = [
                    'v'         => $nodoFin,
                    'arista_id' => $aristaId,
                    'tramo_id'  => $tramo->cod_calles_tramos,
                    'longitud'  => $longitud,
                ];

                // Incrementar grado de los nodos
                $nodos[$nodoIni]['grado'] = ($nodos[$nodoIni]['grado'] ?? 0) + 1;
                $nodos[$nodoFin]['grado'] = ($nodos[$nodoFin]['grado'] ?? 0) + 1;
            }

            $totalLongitud += $longitud;

            // 3. Consultar perfil de riesgo del tramo sin mutar datos
            $perfil = $this->riskService->obtenerPerfil($tramo);
            $cob = $perfil['cobertura_datos'] ?? TramoRiskService::COBERTURA_SIN_DATOS;
            if (isset($conteoEstadosCobertura[$cob])) {
                $conteoEstadosCobertura[$cob]++;
            }

            // Un tramo tiene riesgo medible si posee evaluación técnica o condiciones o zonas u obstáculos
            $tieneDatoSeguridad = ($cob !== TramoRiskService::COBERTURA_SIN_DATOS);
            if ($tieneDatoSeguridad) {
                $tramosConRiesgoMedible++;
            }

            // Desglose formateado para la tabla de la interfaz
            $tramosDetalle[] = [
                'orden'               => $tramo->ord_ctr ?? ($tramo->pivot->ord_rct ?? null),
                'id'                  => $tramo->cod_calles_tramos,
                'nombre'              => $tramo->nom_ctr,
                'tipo'                => $tramo->tip_ctr,
                'longitud'            => $longitud,
                'nodo_inicial'        => $nodoIni ?? 'SIN GEO',
                'nodo_final'          => $nodoFin ?? 'SIN GEO',
                'estado_evaluacion'   => $perfil['estado_evaluacion'] ?? 'NO_EVALUADO',
                'nivel_seguridad'     => $perfil['nivel_seguridad_nombre'] ?? 'SIN EVALUAR',
                'condiciones_count'   => $perfil['condiciones_count'] ?? 0,
                'zonas_activas_count' => $perfil['zonas_activas_count'] ?? 0,
                'obstaculos_count'    => ($perfil['obstaculos_activos_count'] ?? 0) + ($perfil['obstaculos_reportados_count'] ?? 0),
                'cobertura_datos'     => $cob,
                'geo'                 => $geo,
                'es_geo_valido'       => $esValido,
            ];
        }

        // Si ningún tramo tiene geometría válida
        if ($tramosConGeoValida === 0) {
            return $this->construirAnalisisSinGeometria($ruta, count($tramos), $tramosDetalle);
        }

        // 4. Determinar nodo origen y nodo destino
        [$nodoOrigenId, $nodoDestinoId] = $this->inferirNodosOrigenYDestino($ruta, $nodos, $aristas);

        // 5. Conteo de trayectorias acíclicas entre origen y destino
        $caminos = 0;
        if ($nodoOrigenId && $nodoDestinoId && isset($nodos[$nodoOrigenId], $nodos[$nodoDestinoId])) {
            $caminos = $this->contarCaminosAciclicos($nodoOrigenId, $nodoDestinoId, $adj, self::LIMITE_EXPLORACION_CAMINOS);
        }

        // 6. Evaluar cobertura general de riesgo
        $totalTramos = count($tramos);
        $coberturaGeneral = TramoRiskService::COBERTURA_SIN_DATOS;
        if ($conteoEstadosCobertura[TramoRiskService::COBERTURA_COMPLETO] === $totalTramos) {
            $coberturaGeneral = TramoRiskService::COBERTURA_COMPLETO;
        } elseif ($tramosConRiesgoMedible > 0) {
            $coberturaGeneral = TramoRiskService::COBERTURA_PARCIAL;
        }

        $tieneRiesgoSuficiente = ($coberturaGeneral === TramoRiskService::COBERTURA_COMPLETO)
            || ($tramosConRiesgoMedible === $totalTramos && $totalTramos > 0);

        // 7. Diagnosticar estado canónico y motivos de bloqueo
        $esLineal = ($caminos === 1);
        $motivosBloqueo = [];
        $estado = EstadoAcoRuta::LISTO_PARA_OPTIMIZAR;
        $esOptimizable = false;

        if ($caminos === 0) {
            $estado = EstadoAcoRuta::DESCONECTADO;
            $motivosBloqueo[] = "El grafo no tiene conectividad continua entre el nodo de origen ({$nodoOrigenId}) y el nodo de destino ({$nodoDestinoId}).";
        } elseif ($caminos === 1) {
            $estado = EstadoAcoRuta::GRAFO_LINEAL;
            $motivosBloqueo[] = "La ruta posee una trayectoria lineal única (1 solo camino posible). ACO requiere dos o más alternativas para explorar y optimizar.";
            if (!$tieneRiesgoSuficiente) {
                $motivosBloqueo[] = "Los tramos aún no cuentan con información cuantitativa de riesgo vial (cobertura {$coberturaGeneral}).";
            }
        } elseif (!$tieneRiesgoSuficiente) {
            $estado = EstadoAcoRuta::RIESGO_INSUFICIENTE;
            $motivosBloqueo[] = "Existen {$caminos} caminos posibles, pero los tramos aún no cuentan con información suficiente de riesgo vial para ponderar la función de costo canónica (cobertura {$coberturaGeneral}).";
        } else {
            $estado = EstadoAcoRuta::LISTO_PARA_OPTIMIZAR;
            $esOptimizable = true;
        }

        // 8. Construir checklist de preparación para ACO
        $checklist = [
            [
                'clave'   => 'ruta_registrada',
                'titulo'  => 'Ruta registrada en el sistema',
                'cumple'  => true,
                'detalle' => "Código {$ruta->ide_rut} ({$ruta->nom_rut}).",
            ],
            [
                'clave'   => 'geometria_disponible',
                'titulo'  => 'Geometría vial disponible',
                'cumple'  => ($tramosConGeoValida === $totalTramos),
                'detalle' => "{$tramosConGeoValida} de {$totalTramos} tramos poseen coordenadas válidas.",
            ],
            [
                'clave'   => 'tramos_registrados',
                'titulo'  => 'Tramos físicos asociados',
                'cumple'  => ($totalTramos > 0),
                'detalle' => "{$totalTramos} tramos registrados en la ruta.",
            ],
            [
                'clave'   => 'grafo_conectado',
                'titulo'  => 'Conectividad entre origen y destino',
                'cumple'  => ($caminos > 0),
                'detalle' => $caminos > 0 ? "Conexión continua detectada desde {$nodoOrigenId} hasta {$nodoDestinoId}." : "Grafo desconectado o sin camino viable.",
            ],
            [
                'clave'   => 'caminos_alternativos',
                'titulo'  => 'Dos o más caminos posibles (bifurcaciones)',
                'cumple'  => ($caminos >= 2),
                'detalle' => "{$caminos} " . ($caminos === 1 ? 'camino detectado (trayectoria lineal única).' : 'caminos detectados.'),
            ],
            [
                'clave'   => 'riesgo_calculable',
                'titulo'  => 'Información cuantitativa de riesgo vial',
                'cumple'  => $tieneRiesgoSuficiente,
                'detalle' => "Cobertura de datos: {$coberturaGeneral} ({$tramosConRiesgoMedible}/{$totalTramos} tramos con registros).",
            ],
        ];

        return new AnalisisAcoRuta([
            'estado'                => $estado,
            'esOptimizable'         => $esOptimizable,
            'cantidadTramos'        => $totalTramos,
            'cantidadNodos'         => count($nodos),
            'cantidadAlternativas'  => $caminos,
            'esLineal'              => $esLineal,
            'tieneRiesgoSuficiente' => $tieneRiesgoSuficiente,
            'coberturaRiesgo'       => $coberturaGeneral,
            'motivosBloqueo'        => $motivosBloqueo,
            'checklist'             => $checklist,
            'nodoOrigenId'          => $nodoOrigenId,
            'nodoDestinoId'         => $nodoDestinoId,
            'nodos'                 => $nodos,
            'aristas'               => $aristas,
            'tramosDetalle'         => $tramosDetalle,
        ]);
    }

    /**
     * Construye una instancia ejecutable de Grafo para ACO cuando la ruta es optimizable.
     * Calcula rigurosamente R_norm para cada tramo mediante CalculadorRiesgoTramoAco.
     * En caso de riesgo no medible o cobertura SIN_DATOS, retorna null para proteger la integridad.
     *
     * @param Ruta                          $ruta              Ruta a procesar.
     * @param array<int, float>             $riesgosSinteticos Riesgos opcionales para pruebas sintéticas.
     * @param CalculadorRiesgoTramoAco|null $calculadorRiesgo  Calculador de riesgo opcional.
     * @return Grafo|null Instancia de Grafo lista para ACO o null si la ruta no es procesable.
     */
    public function construirGrafo(
        Ruta $ruta,
        array $riesgosSinteticos = [],
        ?CalculadorRiesgoTramoAco $calculadorRiesgo = null
    ): ?Grafo {
        $analisis = $this->analizar($ruta);

        if (!$analisis->tieneTramos() || empty($analisis->aristas)) {
            return null;
        }

        $calc = $calculadorRiesgo ?? new CalculadorRiesgoTramoAco(riskService: $this->riskService);
        $tramosKeyed = $ruta->tramos->keyBy('cod_calles_tramos');

        $grafo = new Grafo();

        foreach ($analisis->aristas as $arista) {
            $tramoId = $arista['tramo_id'];
            $longitud = (float) $arista['longitud'];

            // Usar riesgo provisto sintéticamente para tests si existe
            if (isset($riesgosSinteticos[$tramoId])) {
                $riesgo = (float) $riesgosSinteticos[$tramoId];
            } else {
                // Obtener modelo y calcular R_norm formal
                $tramoModel = $tramosKeyed[$tramoId] ?? CalleTramo::find($tramoId);
                if (!$tramoModel) {
                    return null;
                }

                try {
                    $riesgo = $calc->calcular($tramoModel);
                } catch (\InvalidArgumentException $e) {
                    // Si el tramo carece de datos de riesgo o tiene SIN_DATOS, no inventar valores
                    return null;
                }
            }

            $grafo->agregarArista(new Arista(
                (string) $arista['origen'],
                (string) $arista['destino'],
                $longitud,
                (float) $riesgo
            ));
        }

        return $grafo;
    }

    // ─── Helpers Topológicos y Espaciales ────────────────────────

    /**
     * Agrupa una coordenada geográfica dentro de un nodo existente si cae dentro de la tolerancia,
     * o crea un nuevo nodo espacial en caso contrario.
     *
     * @param array<string, array<string, mixed>> $nodos
     */
    public function encontrarOCrearNodo(float $lat, float $lng, array &$nodos): string
    {
        foreach ($nodos as $id => $nodo) {
            $distancia = $this->haversineMetros($lat, $lng, (float) $nodo['lat'], (float) $nodo['lng']);
            if ($distancia <= $this->toleranciaMetros) {
                return $id;
            }
        }

        $nuevoId = 'N' . count($nodos);
        $nodos[$nuevoId] = [
            'id'    => $nuevoId,
            'lat'   => $lat,
            'lng'   => $lng,
            'grado' => 0,
        ];

        return $nuevoId;
    }

    /**
     * Cuenta la cantidad de caminos acíclicos dirigidos entre el nodo origen y el nodo destino mediante DFS.
     *
     * @param array<string, array<int, array<string, mixed>>> $adj
     */
    public function contarCaminosAciclicos(string $origen, string $destino, array $adj, int $limite = self::LIMITE_EXPLORACION_CAMINOS): int
    {
        $caminos = 0;
        $visitados = [];

        $dfs = function (string $actual) use (&$dfs, $destino, $adj, &$visitados, &$caminos, $limite): void {
            if ($actual === $destino) {
                $caminos++;
                return;
            }

            if ($caminos >= $limite) {
                return;
            }

            $visitados[$actual] = true;

            foreach ($adj[$actual] ?? [] as $conexion) {
                $vecino = $conexion['v'];
                if (!isset($visitados[$vecino])) {
                    $dfs($vecino);
                }
            }

            unset($visitados[$actual]); // Backtracking para explorar ramas alternativas
        };

        $dfs($origen);

        return $caminos;
    }

    /**
     * Infiere el nodo inicial y el nodo final del recorrido a partir de la geometría de la ruta o sus aristas.
     *
     * @param array<string, array<string, mixed>> $nodos
     * @param array<int, array<string, mixed>>    $aristas
     * @return array{0: ?string, 1: ?string}
     */
    public function inferirNodosOrigenYDestino(Ruta $ruta, array $nodos, array $aristas): array
    {
        if (empty($nodos) || empty($aristas)) {
            return [null, null];
        }

        // Estrategia 1: Coordenadas formales de origen/destino en geo_rut
        $geoRuta = is_array($ruta->geo_rut) ? $ruta->geo_rut : null;
        $origenCoords = $geoRuta['origen_coords'] ?? null;
        $destinoCoords = $geoRuta['destino_coords'] ?? null;

        $nodoOrigenId = null;
        $nodoDestinoId = null;

        if (is_array($origenCoords) && isset($origenCoords['lat'], $origenCoords['lng'])) {
            $nodoOrigenId = $this->encontrarNodoMasCercano((float) $origenCoords['lat'], (float) $origenCoords['lng'], $nodos);
        }

        if (is_array($destinoCoords) && isset($destinoCoords['lat'], $destinoCoords['lng'])) {
            $nodoDestinoId = $this->encontrarNodoMasCercano((float) $destinoCoords['lat'], (float) $destinoCoords['lng'], $nodos);
        }

        // Estrategia 2: Si no coinciden o no existen, usar el inicio de la primera arista y el fin de la última
        if (!$nodoOrigenId) {
            $nodoOrigenId = $aristas[0]['origen'] ?? null;
        }

        if (!$nodoDestinoId) {
            $ultimaArista = end($aristas);
            $nodoDestinoId = $ultimaArista['destino'] ?? null;
        }

        return [$nodoOrigenId, $nodoDestinoId];
    }

    /**
     * Encuentra el nodo del grafo más cercano a un punto de referencia dado.
     *
     * @param array<string, array<string, mixed>> $nodos
     */
    public function encontrarNodoMasCercano(float $lat, float $lng, array $nodos, float $radioMaximoMetros = 300.0): ?string
    {
        $mejorNodo = null;
        $menorDistancia = $radioMaximoMetros;

        foreach ($nodos as $id => $nodo) {
            $dist = $this->haversineMetros($lat, $lng, (float) $nodo['lat'], (float) $nodo['lng']);
            if ($dist <= $menorDistancia) {
                $menorDistancia = $dist;
                $mejorNodo = $id;
            }
        }

        return $mejorNodo;
    }

    /**
     * Calcula la distancia ortodrómica en metros entre dos puntos mediante la fórmula de Haversine.
     */
    public function haversineMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::RADIO_TIERRA_METROS * $c;
    }

    // ─── Estados Iniciales y Fallbacks ───────────────────────────

    private function construirAnalisisSinTramos(Ruta $ruta): AnalisisAcoRuta
    {
        return new AnalisisAcoRuta([
            'estado'                => EstadoAcoRuta::SIN_TRAMOS,
            'esOptimizable'         => false,
            'cantidadTramos'        => 0,
            'cantidadNodos'         => 0,
            'cantidadAlternativas'  => 0,
            'esLineal'              => false,
            'tieneRiesgoSuficiente' => false,
            'coberturaRiesgo'       => TramoRiskService::COBERTURA_SIN_DATOS,
            'motivosBloqueo'        => [
                'La ruta todavía no tiene tramos físicos registrados en el sistema.',
            ],
            'checklist'             => [
                [
                    'clave'   => 'ruta_registrada',
                    'titulo'  => 'Ruta registrada en el sistema',
                    'cumple'  => true,
                    'detalle' => "Código {$ruta->ide_rut} ({$ruta->nom_rut}).",
                ],
                [
                    'clave'   => 'geometria_disponible',
                    'titulo'  => 'Geometría vial disponible',
                    'cumple'  => false,
                    'detalle' => 'Sin segmentos asociados.',
                ],
                [
                    'clave'   => 'tramos_registrados',
                    'titulo'  => 'Tramos físicos asociados',
                    'cumple'  => false,
                    'detalle' => '0 tramos registrados.',
                ],
                [
                    'clave'   => 'caminos_alternativos',
                    'titulo'  => 'Dos o más caminos posibles (bifurcaciones)',
                    'cumple'  => false,
                    'detalle' => 'Sin tramos para evaluar alternativas.',
                ],
                [
                    'clave'   => 'riesgo_calculable',
                    'titulo'  => 'Información cuantitativa de riesgo vial',
                    'cumple'  => false,
                    'detalle' => 'Sin tramos para evaluar riesgo.',
                ],
            ],
        ]);
    }

    private function construirAnalisisSinGeometria(Ruta $ruta, int $totalTramos, array $tramosDetalle): AnalisisAcoRuta
    {
        return new AnalisisAcoRuta([
            'estado'                => EstadoAcoRuta::SIN_GEOMETRIA,
            'esOptimizable'         => false,
            'cantidadTramos'        => $totalTramos,
            'cantidadNodos'         => 0,
            'cantidadAlternativas'  => 0,
            'esLineal'              => false,
            'tieneRiesgoSuficiente' => false,
            'coberturaRiesgo'       => TramoRiskService::COBERTURA_SIN_DATOS,
            'motivosBloqueo'        => [
                "Los {$totalTramos} tramos asociados a la ruta no poseen coordenadas geográficas válidas (geo_ctr).",
            ],
            'tramosDetalle'         => $tramosDetalle,
            'checklist'             => [
                [
                    'clave'   => 'ruta_registrada',
                    'titulo'  => 'Ruta registrada en el sistema',
                    'cumple'  => true,
                    'detalle' => "Código {$ruta->ide_rut}.",
                ],
                [
                    'clave'   => 'geometria_disponible',
                    'titulo'  => 'Geometría vial disponible',
                    'cumple'  => false,
                    'detalle' => 'Coordenadas geo_ctr vacías o insuficientes.',
                ],
                [
                    'clave'   => 'tramos_registrados',
                    'titulo'  => 'Tramos físicos asociados',
                    'cumple'  => true,
                    'detalle' => "{$totalTramos} tramos registrados.",
                ],
                [
                    'clave'   => 'caminos_alternativos',
                    'titulo'  => 'Dos o más caminos posibles (bifurcaciones)',
                    'cumple'  => false,
                    'detalle' => 'Sin geometría no es posible conectar el grafo.',
                ],
                [
                    'clave'   => 'riesgo_calculable',
                    'titulo'  => 'Información cuantitativa de riesgo vial',
                    'cumple'  => false,
                    'detalle' => 'Pendiente de geometría.',
                ],
            ],
        ]);
    }
}
