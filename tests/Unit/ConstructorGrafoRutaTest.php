<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use App\Models\EvaluacionRiesgo;
use App\Models\NivelSeguridad;
use App\Models\Ruta;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\EstadoAcoRuta;
use App\Services\Aco\Grafo;
use App\Services\Aco\ServicioAco;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConstructorGrafoRutaTest extends TestCase
{
    use DatabaseTransactions;

    private ConstructorGrafoRuta $constructor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->constructor = new ConstructorGrafoRuta();
    }

    private function crearRuta(string $codigo = 'RUT-SYN-001', string $nombre = 'RUTA SINTETICA'): Ruta
    {
        return Ruta::create([
            'ide_rut'              => $codigo,
            'nom_rut'              => $nombre,
            'ori_rut'              => 'PUNTO ORIGEN',
            'dst_rut'              => 'PUNTO DESTINO',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
            'geo_rut'              => [
                'origen_coords'   => ['lat' => -16.5000, 'lng' => -68.1300],
                'destino_coords'  => ['lat' => -16.5050, 'lng' => -68.1350],
                'trayecto_coords' => [
                    ['lat' => -16.5000, 'lng' => -68.1300],
                    ['lat' => -16.5050, 'lng' => -68.1350],
                ],
            ],
        ]);
    }

    /**
     * 1. Ruta sin tramos devuelve estado SIN_TRAMOS.
     */
    public function test_ruta_sin_tramos_devuelve_estado_sin_tramos(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-1', 'RUTA SIN TRAMOS');

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(EstadoAcoRuta::SIN_TRAMOS, $analisis->estado);
        $this->assertFalse($analisis->esOptimizable);
        $this->assertSame(0, $analisis->cantidadTramos);
        $this->assertSame(0, $analisis->cantidadNodos);
        $this->assertSame(0, $analisis->cantidadAlternativas);
        $this->assertFalse($analisis->tieneTramos());
        $this->assertNotEmpty($analisis->motivosBloqueo);
    }

    /**
     * 2. Ruta con un solo tramo devuelve GRAFO_LINEAL.
     */
    public function test_ruta_con_un_solo_tramo_devuelve_grafo_lineal(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-2', 'RUTA CON UN TRAMO');

        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO UNICO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);

        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(EstadoAcoRuta::GRAFO_LINEAL, $analisis->estado);
        $this->assertTrue($analisis->esLineal);
        $this->assertSame(1, $analisis->cantidadTramos);
        $this->assertSame(2, $analisis->cantidadNodos);
        $this->assertSame(1, $analisis->cantidadAlternativas);
        $this->assertFalse($analisis->esOptimizable);
    }

    /**
     * 3. Cadena lineal de varios tramos en serie detecta GRAFO_LINEAL y 1 alternativa.
     */
    public function test_cadena_lineal_de_varios_tramos_detecta_grafo_lineal(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-3', 'CADENA LINEAL');

        // Tres tramos encadenados: N0 -> N1 -> N2 -> N3
        $t1 = CalleTramo::create([
            'nom_ctr' => 'SEGMENTO A',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 120.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);
        $t2 = CalleTramo::create([
            'nom_ctr' => 'SEGMENTO B',
            'tip_ctr' => 'CRUCE',
            'lon_ctr' => 80.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310], // Mismo punto exacto de N1
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);
        $t3 = CalleTramo::create([
            'nom_ctr' => 'SEGMENTO C',
            'tip_ctr' => 'CALLE',
            'lon_ctr' => 150.0,
            'geo_ctr' => [
                ['lat' => -16.5020, 'lng' => -68.1320], // Mismo punto exacto de N2
                ['lat' => -16.5030, 'lng' => -68.1330],
            ],
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($t2->getKey(), ['ord_rct' => 2]);
        $ruta->tramos()->attach($t3->getKey(), ['ord_rct' => 3]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(EstadoAcoRuta::GRAFO_LINEAL, $analisis->estado);
        $this->assertSame(3, $analisis->cantidadTramos);
        $this->assertSame(4, $analisis->cantidadNodos);
        $this->assertSame(1, $analisis->cantidadAlternativas);
        $this->assertTrue($analisis->esLineal);
        $this->assertFalse($analisis->esOptimizable);
    }

    /**
     * 4. Ruta con bifurcación (2 caminos que se separan y vuelven a unirse) detecta 2 alternativas.
     */
    public function test_ruta_con_bifurcacion_detecta_dos_caminos_alternativos(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-4', 'RUTA CON BIFURCACION');

        // N0 -> N1 (Tramo Inicial)
        $tInicio = CalleTramo::create([
            'nom_ctr' => 'TRAMO TRONCAL INICIAL',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310], // N1
            ],
        ]);

        // Rama A: N1 -> N2a -> N3
        $tRamaA = CalleTramo::create([
            'nom_ctr' => 'RAMA A (DIRECTA PERO PELIGROSA)',
            'tip_ctr' => 'CALLE',
            'lon_ctr' => 200.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310], // N1
                ['lat' => -16.5030, 'lng' => -68.1330], // N3
            ],
        ]);

        // Rama B: N1 -> N2b -> N3
        $tRamaB1 = CalleTramo::create([
            'nom_ctr' => 'RAMA B1 (DESVIO SEGURO)',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 120.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310], // N1
                ['lat' => -16.5015, 'lng' => -68.1325], // N2b
            ],
        ]);
        $tRamaB2 = CalleTramo::create([
            'nom_ctr' => 'RAMA B2 (RECONEXION)',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 130.0,
            'geo_ctr' => [
                ['lat' => -16.5015, 'lng' => -68.1325], // N2b
                ['lat' => -16.5030, 'lng' => -68.1330], // N3
            ],
        ]);

        // N3 -> N4 (Tramo Final)
        $tFinal = CalleTramo::create([
            'nom_ctr' => 'TRAMO TRONCAL FINAL',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 90.0,
            'geo_ctr' => [
                ['lat' => -16.5030, 'lng' => -68.1330], // N3
                ['lat' => -16.5040, 'lng' => -68.1340], // N4
            ],
        ]);

        $ruta->tramos()->attach($tInicio->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($tRamaA->getKey(), ['ord_rct' => 2]);
        $ruta->tramos()->attach($tRamaB1->getKey(), ['ord_rct' => 3]);
        $ruta->tramos()->attach($tRamaB2->getKey(), ['ord_rct' => 4]);
        $ruta->tramos()->attach($tFinal->getKey(), ['ord_rct' => 5]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(5, $analisis->cantidadTramos);
        $this->assertSame(5, $analisis->cantidadNodos);
        $this->assertSame(2, $analisis->cantidadAlternativas); // Exactamente 2 caminos: Troncal->RamaA->Final y Troncal->B1->B2->Final
        $this->assertFalse($analisis->esLineal);
    }

    /**
     * 5. Grafo desconectado detecta estado DESCONECTADO (0 caminos entre origen y destino).
     */
    public function test_grafo_desconectado_detecta_cero_caminos(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-5', 'GRAFO DESCONECTADO');

        // Tramo 1 en zona A
        $t1 = CalleTramo::create([
            'nom_ctr' => 'ISLA A',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);

        // Tramo 2 a 1 km de distancia en zona B (desconectado)
        $t2 = CalleTramo::create([
            'nom_ctr' => 'ISLA B',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5100, 'lng' => -68.1400],
                ['lat' => -16.5110, 'lng' => -68.1410],
            ],
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($t2->getKey(), ['ord_rct' => 2]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(EstadoAcoRuta::DESCONECTADO, $analisis->estado);
        $this->assertSame(0, $analisis->cantidadAlternativas);
        $this->assertFalse($analisis->esOptimizable);
    }

    /**
     * 6. Callejón sin salida no rompe la conectividad del camino principal.
     */
    public function test_callejon_sin_salida_no_rompe_camino_principal(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-6', 'CALLEJON SIN SALIDA');

        // Camino principal: N0 -> N1 -> N2
        $tMain1 = CalleTramo::create([
            'nom_ctr' => 'PRINCIPAL 1',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);
        $tMain2 = CalleTramo::create([
            'nom_ctr' => 'PRINCIPAL 2',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);

        // Callejón: N1 -> N_DEAD (sin salida)
        $tDead = CalleTramo::create([
            'nom_ctr' => 'PASAJE CIEGO',
            'tip_ctr' => 'PASO PEATONAL',
            'lon_ctr' => 50.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310],
                ['lat' => -16.5012, 'lng' => -68.1305], // Termina en callejón
            ],
        ]);

        $ruta->tramos()->attach($tMain1->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($tDead->getKey(), ['ord_rct' => 2]);
        $ruta->tramos()->attach($tMain2->getKey(), ['ord_rct' => 3]);

        $analisis = $this->constructor->analizar($ruta);

        // El camino principal sigue siendo 1 camino viable entre N0 y N2
        $this->assertSame(1, $analisis->cantidadAlternativas);
        $this->assertTrue($analisis->esLineal);
    }

    /**
     * 7. Grafo con ciclo no produce bucle infinito gracias al backtracking con conjunto de visitados.
     */
    public function test_grafo_con_ciclo_no_produce_bucle_infinito(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-7', 'GRAFO CON CICLO');

        // N0 -> N1
        $t1 = CalleTramo::create([
            'nom_ctr' => 'T1',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);
        // N1 -> N2
        $t2 = CalleTramo::create([
            'nom_ctr' => 'T2',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1310],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);
        // Ciclo: N2 -> N1
        $tCiclo = CalleTramo::create([
            'nom_ctr' => 'T_CICLO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5020, 'lng' => -68.1320],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);
        // N2 -> N3 (Destino)
        $t3 = CalleTramo::create([
            'nom_ctr' => 'T3',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5020, 'lng' => -68.1320],
                ['lat' => -16.5030, 'lng' => -68.1330],
            ],
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($t2->getKey(), ['ord_rct' => 2]);
        $ruta->tramos()->attach($tCiclo->getKey(), ['ord_rct' => 3]);
        $ruta->tramos()->attach($t3->getKey(), ['ord_rct' => 4]);

        $analisis = $this->constructor->analizar($ruta);

        // Debe terminar instantáneamente sin desbordar memoria ni congelar ejecución
        $this->assertNotNull($analisis);
        $this->assertSame(1, $analisis->cantidadAlternativas);
    }

    /**
     * 8. Geometría vacía devuelve estado SIN_GEOMETRIA.
     */
    public function test_geometria_vacia_devuelve_sin_geometria(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-8', 'SIN GEOMETRIA');

        $t1 = CalleTramo::create([
            'nom_ctr' => 'TRAMO SIN GEO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => null,
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(EstadoAcoRuta::SIN_GEOMETRIA, $analisis->estado);
        $this->assertFalse($analisis->esOptimizable);
    }

    /**
     * 9. Coordenadas dentro de tolerancia geográfica (<= 8m) se agrupan en el mismo nodo.
     */
    public function test_coordenadas_dentro_de_tolerancia_se_agrupan_en_el_mismo_nodo(): void
    {
        $nodos = [];

        // Punto 1: Centro de esquina
        $id1 = $this->constructor->encontrarOCrearNodo(-16.500000, -68.130000, $nodos);

        // Punto 2: A 4 metros de distancia (dentro de tolerancia 8.0 m)
        // 0.000035 grados de latitud ~ 3.89 metros
        $id2 = $this->constructor->encontrarOCrearNodo(-16.500035, -68.130000, $nodos);

        $this->assertSame($id1, $id2);
        $this->assertCount(1, $nodos);
    }

    /**
     * 10. Coordenadas fuera de tolerancia (> 8m) crean nodos independientes.
     */
    public function test_coordenadas_fuera_de_tolerancia_crean_nodos_separados(): void
    {
        $nodos = [];

        // Punto 1
        $id1 = $this->constructor->encontrarOCrearNodo(-16.500000, -68.130000, $nodos);

        // Punto 2: A 25 metros de distancia (fuera de tolerancia)
        // 0.000225 grados de latitud ~ 25.0 metros
        $id2 = $this->constructor->encontrarOCrearNodo(-16.500225, -68.130000, $nodos);

        $this->assertNotSame($id1, $id2);
        $this->assertCount(2, $nodos);
    }

    /**
     * 11. Preservación de distancias métricas de lon_ctr en las aristas.
     */
    public function test_preservacion_de_distancias_metricas(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-11', 'DISTANCIAS');

        $t1 = CalleTramo::create([
            'nom_ctr' => 'TRAMO CON LONGITUD EXACTA',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 522.43,
            'geo_ctr' => [
                ['lat' => -16.502975, 'lng' => -68.136334],
                ['lat' => -16.499293, 'lng' => -68.133344],
            ],
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertCount(1, $analisis->aristas);
        $this->assertEqualsWithDelta(522.43, $analisis->aristas[0]['longitud'], 0.01);
        $this->assertEqualsWithDelta(522.43, $analisis->tramosDetalle[0]['longitud'], 0.01);
    }

    /**
     * 12. Tramo reutilizado en dos rutas distintas preserva topología independiente.
     */
    public function test_tramo_compartido_entre_dos_rutas(): void
    {
        $rutaA = $this->crearRuta('RUT-A', 'RUTA A');
        $rutaB = $this->crearRuta('RUT-B', 'RUTA B');

        $tramoComun = CalleTramo::create([
            'nom_ctr' => 'AVENIDA PEATONAL COMPARTIDA',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 250.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);

        $rutaA->tramos()->attach($tramoComun->getKey(), ['ord_rct' => 1]);
        $rutaB->tramos()->attach($tramoComun->getKey(), ['ord_rct' => 1]);

        $analisisA = $this->constructor->analizar($rutaA);
        $analisisB = $this->constructor->analizar($rutaB);

        $this->assertSame(1, $analisisA->cantidadTramos);
        $this->assertSame(1, $analisisB->cantidadTramos);
        $this->assertSame($analisisA->estado, $analisisB->estado);
    }

    /**
     * 13. Perfil con cobertura SIN_DATOS no genera riesgo inventado y marca riesgo no calculable.
     */
    public function test_perfil_sin_datos_no_inventa_riesgo(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-13', 'SIN DATOS');

        $t1 = CalleTramo::create([
            'nom_ctr' => 'TRAMO VIRGEN',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame('SIN_DATOS', $analisis->coberturaRiesgo);
        $this->assertFalse($analisis->tieneRiesgoSuficiente);
        $this->assertFalse($analisis->esOptimizable);
    }

    /**
     * 14. Estado LISTO_PARA_OPTIMIZAR cuando hay bifurcaciones y riesgo sintético evaluado en testing.
     */
    public function test_estado_listo_para_optimizar_con_alternativas_y_evaluaciones(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-14', 'OPTIMIZABLE');

        // Nivel de seguridad óptimo (5)
        $nivelSeg = NivelSeguridad::firstOrCreate(['niv_nse' => 5], [
            'nom_nse' => 'OPTIMO',
            'col_nse' => '#10b981',
            'des_nse' => 'CONDICIONES PEATONALES OPTIMAS',
        ]);

        // Camino A: N0 -> N1
        $t1 = CalleTramo::create([
            'nom_ctr' => 'CAMINO NORTE',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 150.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $t1->getKey(),
            'cod_niveles_seguridad_eri' => $nivelSeg->getKey(),
            'fee_eri'                   => now(),
            'met_eri'                   => 'AUDITORIA VISUAL',
            'cod_usuarios_eri'          => 1,
        ]);

        // Camino B: N0 -> N1 (Paralelo por Acera Sur)
        $t2 = CalleTramo::create([
            'nom_ctr' => 'CAMINO SUR ALTERNATIVO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 180.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $t2->getKey(),
            'cod_niveles_seguridad_eri' => $nivelSeg->getKey(),
            'fee_eri'                   => now(),
            'met_eri'                   => 'AUDITORIA VISUAL',
            'cod_usuarios_eri'          => 1,
        ]);

        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);
        $ruta->tramos()->attach($t2->getKey(), ['ord_rct' => 2]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertSame(2, $analisis->cantidadAlternativas);
        $this->assertTrue($analisis->tieneRiesgoSuficiente);
        $this->assertSame(EstadoAcoRuta::LISTO_PARA_OPTIMIZAR, $analisis->estado);
        $this->assertTrue($analisis->esOptimizable);
    }

    /**
     * 15. No se ejecuta ServicioAco en rutas no optimizables.
     */
    public function test_no_se_ejecuta_servicio_aco_en_ruta_no_optimizable(): void
    {
        $ruta = $this->crearRuta('RUT-TEST-15', 'NO OPTIMIZABLE');

        $t1 = CalleTramo::create([
            'nom_ctr' => 'TRAMO SOLITARIO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5010, 'lng' => -68.1310],
            ],
        ]);
        $ruta->tramos()->attach($t1->getKey(), ['ord_rct' => 1]);

        $analisis = $this->constructor->analizar($ruta);

        $this->assertFalse($analisis->esOptimizable);

        // Intentar construir grafo sin riesgos sintéticos debe retornar null para proteger producción
        $grafo = $this->constructor->construirGrafo($ruta);
        $this->assertNull($grafo);
    }
}
