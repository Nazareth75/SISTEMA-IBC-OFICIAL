<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ruta;
use App\Models\CalleTramo;
use App\Models\NivelSeguridad;
use App\Models\EvaluacionRiesgo;
use App\Models\CondicionEntorno;
use App\Models\ZonaRiesgo;
use App\Models\Obstaculo;
use App\Models\ReporteObstaculo;
use App\Models\User;
use App\Services\Aco\ConfiguracionAco;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\CalculadorRiesgoTramoAco;
use App\Services\Aco\ServicioAco;
use App\Services\Aco\ServicioRutaReferencia;
use App\Services\Aco\ServicioRecomendacionRutaAco;
use App\Services\Aco\ServicioExperimentosAco;
use App\Services\TramoRiskService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ServicioRecomendacionRutaAco_Test extends TestCase
{
    use DatabaseTransactions;

    private ServicioRecomendacionRutaAco $servicio;
    private TramoRiskService $riskService;
    private User $adminUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->riskService = new TramoRiskService();
        $constructor = new ConstructorGrafoRuta($this->riskService);
        $calcRiesgo  = new CalculadorRiesgoTramoAco(riskService: $this->riskService);
        $aco         = new ServicioAco();
        $ref         = new ServicioRutaReferencia();

        $this->servicio = new ServicioRecomendacionRutaAco($constructor, $aco, $ref, $calcRiesgo);

        $this->adminUser = User::first() ?? User::create([
            'nom_usu'  => 'ADMINISTRADOR TEST',
            'email'    => 'admin_test@ibc.org',
            'password' => bcrypt('password'),
            'rol_usu'  => 'ADMINISTRADOR',
        ]);
    }

    public function test_ruta_sin_tramos_retorna_recomendacion_fallida(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-SIN-TRAMOS',
            'nom_rut' => 'Ruta sin tramos test',
            'ori_rut' => 'Origen',
            'dst_rut' => 'Destino',
            'est_rut' => 'BORRADOR',
        ]);

        $resultado = $this->servicio->recomendar($ruta);

        $this->assertFalse($resultado->success);
        $this->assertStringContainsString('no tiene tramos físicos', $resultado->mensajeError);
    }

    public function test_ruta_con_alternativas_genera_recomendacion_valida_y_metricas(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-ACO-REC-1',
            'nom_rut' => 'Ruta ACO con alternativas de prueba',
            'ori_rut' => 'Origen Aco',
            'dst_rut' => 'Destino Aco',
            'est_rut' => 'EN REVISIÓN',
        ]);

        $niveles = NivelSeguridad::all()->keyBy('niv_nse');
        $nivCritico = $niveles[1]->cod_niveles_seguridad;
        $nivOptimo  = $niveles[5]->cod_niveles_seguridad;

        $tComun1 = CalleTramo::create([
            'nom_ctr' => 'TRAMO INICIAL N0-N1',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [['lat' => -16.5000, 'lng' => -68.1300], ['lat' => -16.5010, 'lng' => -68.1300]],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tComun1->getKey(),
            'cod_niveles_seguridad_eri' => $nivOptimo,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tComun1->getKey(),
            'tip_cen' => 'PAVIMENTO',
            'des_cen' => 'BUENO',
            'niv_cen' => 'BUENO',
        ]);

        $tA1 = CalleTramo::create([
            'nom_ctr' => 'CAMINO A1 (CORTO/RIESGOSO)',
            'tip_ctr' => 'CALLE',
            'lon_ctr' => 100.0,
            'geo_ctr' => [['lat' => -16.5010, 'lng' => -68.1300], ['lat' => -16.5020, 'lng' => -68.1300]],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tA1->getKey(),
            'cod_niveles_seguridad_eri' => $nivCritico,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tA1->getKey(),
            'tip_cen' => 'PAVIMENTO',
            'des_cen' => 'MALO',
            'niv_cen' => 'MALO',
        ]);
        $obsA = Obstaculo::create([
            'cod_calles_tramos_obs' => $tA1->getKey(),
            'tip_obs' => 'OBRA',
            'des_obs' => 'OBRA PROFUNDA',
            'est_obs' => 'ACTIVO',
        ]);
        ReporteObstaculo::create([
            'codigo' => 'REP-A1',
            'tipo_obstaculo' => 'OBRA',
            'descripcion' => 'CRITICO',
            'nivel_riesgo' => 'CRITICO',
            'prioridad' => 4,
            'estado' => 'ACTIVO',
            'cod_obstaculos_rpo' => $obsA->getKey(),
        ]);

        $tA2 = CalleTramo::create([
            'nom_ctr' => 'CAMINO A2 (CORTO/RIESGOSO)',
            'tip_ctr' => 'CRUCE',
            'lon_ctr' => 100.0,
            'geo_ctr' => [['lat' => -16.5020, 'lng' => -68.1300], ['lat' => -16.5030, 'lng' => -68.1300]],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tA2->getKey(),
            'cod_niveles_seguridad_eri' => $nivCritico,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tA2->getKey(),
            'tip_cen' => 'SEÑALÉTICA',
            'des_cen' => 'MALO',
            'niv_cen' => 'MALO',
        ]);

        $tB1 = CalleTramo::create([
            'nom_ctr' => 'CAMINO B1 (LARGO/SEGURO)',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 300.0,
            'geo_ctr' => [
                ['lat' => -16.5010, 'lng' => -68.1300],
                ['lat' => -16.5015, 'lng' => -68.1280],
                ['lat' => -16.5020, 'lng' => -68.1270]
            ],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tB1->getKey(),
            'cod_niveles_seguridad_eri' => $nivOptimo,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tB1->getKey(),
            'tip_cen' => 'PAVIMENTO',
            'des_cen' => 'BUENO',
            'niv_cen' => 'BUENO',
        ]);

        $tB2 = CalleTramo::create([
            'nom_ctr' => 'CAMINO B2 (LARGO/SEGURO)',
            'tip_ctr' => 'PASO PEATONAL',
            'lon_ctr' => 300.0,
            'geo_ctr' => [
                ['lat' => -16.5020, 'lng' => -68.1270],
                ['lat' => -16.5025, 'lng' => -68.1280],
                ['lat' => -16.5030, 'lng' => -68.1300]
            ],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tB2->getKey(),
            'cod_niveles_seguridad_eri' => $nivOptimo,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tB2->getKey(),
            'tip_cen' => 'ILUMINACIÓN',
            'des_cen' => 'BUENO',
            'niv_cen' => 'BUENO',
        ]);

        $tComun2 = CalleTramo::create([
            'nom_ctr' => 'TRAMO FINAL N4-N5',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
            'geo_ctr' => [['lat' => -16.5030, 'lng' => -68.1300], ['lat' => -16.5040, 'lng' => -68.1300]],
        ]);
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri' => $tComun2->getKey(),
            'cod_niveles_seguridad_eri' => $nivOptimo,
            'fee_eri' => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tComun2->getKey(),
            'tip_cen' => 'PAVIMENTO',
            'des_cen' => 'BUENO',
            'niv_cen' => 'BUENO',
        ]);

        $ruta->tramos()->attach([
            $tComun1->getKey() => ['ord_rct' => 1],
            $tA1->getKey()     => ['ord_rct' => 2],
            $tA2->getKey()     => ['ord_rct' => 3],
            $tB1->getKey()     => ['ord_rct' => 4],
            $tB2->getKey()     => ['ord_rct' => 5],
            $tComun2->getKey() => ['ord_rct' => 6],
        ]);

        $config = new ConfiguracionAco(
            ants: 15,
            iterations: 20,
            wDistance: 0.3,
            wRisk: 0.7,
            seed: 42
        );

        $resultado = $this->servicio->recomendar($ruta, $config);

        $this->assertTrue($resultado->success);
        $this->assertEquals(2, $resultado->cantidadAlternativas);
        $this->assertNotEmpty($resultado->nodosSeleccionados);
        $this->assertNotEmpty($resultado->tramosSeleccionadosIds);
        $this->assertGreaterThan(0.0, $resultado->distanciaTotal);
        $this->assertGreaterThanOrEqual(0.0, $resultado->riesgoTotal);
        $this->assertGreaterThan(0.0, $resultado->costoTotal);
        $this->assertGreaterThan(0.0, $resultado->tiempoEjecucionMs);

        $this->assertGreaterThan(0.0, $resultado->costoReferencia);
        $this->assertNotNull($resultado->gap);
        $this->assertGreaterThanOrEqual(0.0, $resultado->gap);

        $this->assertGreaterThan(0.0, $resultado->distanciaCaminoCorto);
        $this->assertGreaterThan(0.0, $resultado->riesgoCaminoCorto);
        $this->assertNotNull($resultado->m7);
        $this->assertNotNull($resultado->m8);

        $this->assertGreaterThan($resultado->distanciaCaminoCorto, $resultado->distanciaTotal);
        $this->assertLessThan($resultado->riesgoCaminoCorto, $resultado->riesgoTotal);
        $this->assertGreaterThan(0.0, $resultado->m7);

        $this->assertNotEmpty($resultado->geometriaRecomendada);
        $this->assertNotEmpty($resultado->geometriaMasCorta);
        $this->assertCount(1, $resultado->obstaculosDetalle);
        $this->assertEquals(1, $resultado->obstaculosActivosCount);

        $arr = $resultado->toArray();
        $this->assertTrue($arr['success']);
        $this->assertArrayHasKey('metricas', $arr);
        $this->assertArrayHasKey('M1', $arr['metricas']);
        $this->assertArrayHasKey('M7', $arr['metricas']);
    }

    public function test_demo_rut_005_escenario_principal_evalua_tres_alternativas(): void
    {
        $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->with('tramos')->first();
        if (!$rutaDemo) {
            $this->seed(\Database\Seeders\DatosDemostracionIbcSeeder::class);
            $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->with('tramos')->first();
        }
        $this->assertNotNull($rutaDemo);

        $config = new ConfiguracionAco(
            ants: 20,
            iterations: 25,
            wDistance: 0.5,
            wRisk: 0.5,
            seed: 42
        );

        $resultado = $this->servicio->recomendar($rutaDemo, $config);

        $this->assertTrue($resultado->success);
        $this->assertEquals(3, $resultado->cantidadAlternativas);
        $this->assertGreaterThan(0.0, $resultado->distanciaTotal);
        $this->assertGreaterThanOrEqual(0.0, $resultado->riesgoTotal);
        $this->assertNotNull($resultado->m7);
        $this->assertNotNull($resultado->m8);
        $this->assertGreaterThan(0.0, $resultado->m7);
        $this->assertEquals('BAJO', $resultado->nivelRiesgoEstimado);
        $this->assertStringContainsString('Evita los tramos con mayor riesgo', $resultado->motivoRecomendacion);
    }

    public function test_metricas_m7_y_m8_retornan_null_cuando_denominador_es_cero(): void
    {
        $m7Null = ServicioExperimentosAco::calculateRiskReduction(0.0, 0.5);
        $this->assertNull($m7Null);

        $m8Null = ServicioExperimentosAco::calculateDistanceIncrease(0.0, 500.0);
        $this->assertNull($m8Null);

        $gapNull = ServicioExperimentosAco::calculateGap(1.5, 0.0);
        $this->assertNull($gapNull);
    }

    public function test_endpoint_recalcular_aco_responde_json_estructurado(): void
    {
        $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->first();
        if (!$rutaDemo) {
            $this->seed(\Database\Seeders\DatosDemostracionIbcSeeder::class);
            $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->first();
        }

        $response = $this->actingAs($this->adminUser)->postJson(route('rutas.aco.recalcular', $rutaDemo));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'recomendacion' => [
                'ruta_id',
                'nodos_seleccionados',
                'distancia_total',
                'costo_total',
                'nivel_riesgo_estimado',
                'motivo_recomendacion',
                'metricas' => [
                    'M1', 'M2', 'M3', 'M4', 'M7', 'M8'
                ],
                'cantidad_alternativas',
                'geometria_alternativas',
            ]
        ]);
    }
}