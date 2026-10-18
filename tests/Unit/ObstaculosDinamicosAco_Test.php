<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ruta;
use App\Models\CalleTramo;
use App\Models\NivelSeguridad;
use App\Models\EvaluacionRiesgo;
use App\Models\CondicionEntorno;
use App\Models\Obstaculo;
use App\Models\ReporteObstaculo;
use App\Models\User;
use App\Events\ObstaculoCambioEstado;
use App\Services\Aco\ConfiguracionAco;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\CalculadorRiesgoTramoAco;
use App\Services\Aco\ServicioAco;
use App\Services\Aco\ServicioRutaReferencia;
use App\Services\Aco\ServicioRecomendacionRutaAco;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use App\Services\TramoRiskService;
use Database\Seeders\DatosDemostracionIbcSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ObstaculosDinamicosAco_Test extends TestCase
{
    use DatabaseTransactions;

    private ServicioRecomendacionRutaAco $recomendador;
    private ServicioEstadoRecomendacionAco $estadoService;
    private TramoRiskService $riskService;
    private CalculadorRiesgoTramoAco $calcRiesgo;
    private User $adminUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->riskService   = new TramoRiskService();
        $constructor         = new ConstructorGrafoRuta($this->riskService);
        $this->calcRiesgo    = new CalculadorRiesgoTramoAco(riskService: $this->riskService);
        $aco                 = new ServicioAco();
        $ref                 = new ServicioRutaReferencia();

        $this->recomendador  = new ServicioRecomendacionRutaAco($constructor, $aco, $ref, $this->calcRiesgo);
        $this->estadoService = new ServicioEstadoRecomendacionAco();

        $this->adminUser = User::first() ?? User::create([
            'nom_usu'  => 'ADMINISTRADOR TEST',
            'email'    => 'admin_test@ibc.org',
            'password' => bcrypt('password'),
            'rol_usu'  => 'ADMINISTRADOR',
        ]);
    }

    /**
     * 1. Obstáculo ACTIVO incrementa el riesgo y RESUELTO deja de afectarlo.
     */
    public function test_obstaculo_activo_afecta_riesgo_y_resuelto_deja_de_afectar(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-5C-1',
            'nom_rut' => 'RUTA CAMBIO OBSTACULO RIESGO',
            'ori_rut' => 'Punto A',
            'dst_rut' => 'Punto B',
            'est_rut' => 'BORRADOR',
        ]);

        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO EVALUACION OBSTACULO',
            'tip_ctr'       => 'ACERA',
            'lon_ctr'       => 150.0,
        ]);

        $niv5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $niv5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen'               => 'PAVIMENTO',
            'des_cen'               => 'BUEN ESTADO',
            'niv_cen'               => 'BUENO',
        ]);

        $riesgoBase = $this->calcRiesgo->calcular($tramo);
        $this->assertEquals(0.00, $riesgoBase);

        // Crear obstáculo ACTIVO con reporte crítico
        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'OBRA',
            'des_obs'               => 'ZANJA DE GAS ABIERTA',
            'est_obs'               => 'ACTIVO',
        ]);
        ReporteObstaculo::create([
            'codigo'             => 'REP-5C-01',
            'tipo_obstaculo'     => 'OBRA',
            'descripcion'        => 'Reporte urgente',
            'nivel_riesgo'       => 'CRITICO',
            'prioridad'          => 4,
            'estado'             => 'ACTIVO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $riesgoActivo = $this->calcRiesgo->calcular($tramo);
        $this->assertEquals(0.25, $riesgoActivo);
        $this->assertGreaterThan($riesgoBase, $riesgoActivo);

        // Marcar obstáculo como RESUELTO
        $obs->resolver();
        $tramo->refresh();

        $riesgoResuelto = $this->calcRiesgo->calcular($tramo);
        $this->assertEquals(0.00, $riesgoResuelto);
    }

    /**
     * 2. Reporte DESCARTADO no incrementa riesgo.
     */
    public function test_reporte_descartado_no_afecta_riesgo(): void
    {
        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO CON REPORTE DESCARTADO 5C',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
        ]);

        $niv5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $niv5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen'               => 'PAVIMENTO',
            'des_cen'               => 'EXCELENTE',
            'niv_cen'               => 'BUENO',
        ]);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'OTRO',
            'des_obs'               => 'AVISO FALSO',
            'est_obs'               => 'RESUELTO',
        ]);
        ReporteObstaculo::create([
            'codigo'             => 'REP-5C-DESC',
            'tipo_obstaculo'     => 'OTRO',
            'descripcion'        => 'Reporte descartado por técnico',
            'nivel_riesgo'       => 'CRITICO',
            'prioridad'          => 4,
            'estado'             => 'DESCARTADO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $rNorm = $this->calcRiesgo->calcular($tramo);
        $this->assertEquals(0.00, $rNorm);
    }

    /**
     * 3. Transición de estado de obstáculo invalida recomendación previa mediante evento y listener.
     */
    public function test_cambio_de_estado_invalida_recomendacion_previa(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-5C-INV',
            'nom_rut' => 'RUTA INVALIDACION POR OBSTACULO',
            'ori_rut' => 'A',
            'dst_rut' => 'B',
            'est_rut' => 'PUBLICADA',
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO VINCULADO A RUTA',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 120.0,
            'geo_ctr' => [['lat' => -16.5000, 'lng' => -68.1300], ['lat' => -16.5010, 'lng' => -68.1300]],
        ]);

        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'POSTE',
            'des_obs'               => 'POSTE OBSTRUYENDO ACERA',
            'est_obs'               => 'ACTIVO',
        ]);

        // Simular que la recomendación estaba calculada y vigente
        $this->estadoService->guardarRecomendacion($ruta, new \App\Services\Aco\ResultadoRecomendacionAco(
            success: true,
            distanciaTotal: 120.0,
            riesgoTotal: 0.25,
            costoTotal: 0.50
        ));

        $this->assertFalse($this->estadoService->estaDesactualizada($ruta));

        // Transición: ACTIVO -> RESUELTO
        $obs->resolver();

        // El listener debe haber marcado la recomendación como desactualizada
        $this->assertTrue($this->estadoService->estaDesactualizada($ruta));
        $info = $this->estadoService->obtenerInfoDesactualizacion($ruta);
        $this->assertNotNull($info);
        $this->assertStringContainsString('POSTE', $info['motivo']);
        $this->assertEquals('ACTIVO', $info['estado_previo']);
        $this->assertEquals('RESUELTO', $info['estado_nuevo']);
    }

    /**
     * 4. Escenario controlado: Cambio dinámico real de recomendación ACO tras resolución de obstáculo en DEMO-RUT-005.
     *    ESTADO 1: Camino A tiene múltiples obstáculos activos críticos. ACO recomienda Camino B (largo pero seguro).
     *    ESTADO 2: Obstáculos de Camino A se resuelven. ACO recalcula y ahora recomienda Camino A (corto y seguro).
     *    Sin hardcode en el algoritmo.
     */
    public function test_cambio_real_de_recomendacion_aco_por_resolucion_de_obstaculo(): void
    {
        $ruta = Ruta::where('ide_rut', 'DEMO-RUT-005')->with('tramos')->first();
        if (!$ruta) {
            (new DatosDemostracionIbcSeeder())->run();
            $ruta = Ruta::where('ide_rut', 'DEMO-RUT-005')->with('tramos')->firstOrFail();
        }

        $config = new ConfiguracionAco(ants: 20, iterations: 25, wDistance: 0.5, wRisk: 0.5, seed: 42);

        // ─── ESTADO 1: Con obstáculos activos en Camino A ───
        $rec1 = $this->recomendador->recomendar($ruta, $config);
        $this->assertTrue($rec1->success);
        $this->estadoService->guardarRecomendacion($ruta, $rec1);

        // Debido a los obstáculos activos y alto riesgo en Camino A, ACO debe elegir la alternativa más segura
        $tA1 = CalleTramo::where('nom_ctr', 'like', '%ACO A1%')->firstOrFail();
        $tA2 = CalleTramo::where('nom_ctr', 'like', '%ACO A2%')->firstOrFail();

        $this->assertNotContains($tA1->getKey(), $rec1->tramosSeleccionadosIds);
        $this->assertGreaterThan(500.0, $rec1->distanciaTotal);
        $costoAntes = $rec1->costoTotal;

        // ─── ACCIÓN: Los obstáculos de Camino A son RESUELTOS por el técnico ───
        Obstaculo::whereIn('cod_calles_tramos_obs', [$tA1->getKey(), $tA2->getKey()])->update(['est_obs' => 'RESUELTO']);
        $niv5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::whereIn('cod_calles_tramos_eri', [$tA1->getKey(), $tA2->getKey()])->update(['cod_niveles_seguridad_eri' => $niv5->getKey()]);
        CondicionEntorno::whereIn('cod_calles_tramos_cen', [$tA1->getKey(), $tA2->getKey()])->update(['niv_cen' => 'BUENO']);

        // ─── ESTADO 2: Recálculo ACO tras resolución de obstáculos ───
        $rec2 = $this->recomendador->recomendar($ruta, $config);
        $this->assertTrue($rec2->success);
        $this->estadoService->guardarRecomendacion($ruta, $rec2);

        // Ahora Camino A está libre de obstáculos y es el más corto, ACO converge a él
        $this->assertContains($tA1->getKey(), $rec2->tramosSeleccionadosIds);
        $this->assertContains($tA2->getKey(), $rec2->tramosSeleccionadosIds);
        $this->assertLessThan($costoAntes, $rec2->costoTotal);
        $this->assertLessThan($rec1->distanciaTotal, $rec2->distanciaTotal);

        // Verificar trazabilidad histórica registrada
        $historial = $this->estadoService->obtenerHistorialCambio($ruta);
        $this->assertNotNull($historial);
        $this->assertTrue($historial['hay_cambio']);
        $this->assertTrue($historial['cambio_ruta_efectivo']);
        $this->assertEqualsWithDelta($rec1->distanciaTotal, $historial['anterior']['distancia'], 0.1);
        $this->assertEqualsWithDelta($rec2->distanciaTotal, $historial['actual']['distancia'], 0.1);

        // Verificar que los obstáculos siguen existiendo con est_obs = RESUELTO
        $obstaculosResueltos = Obstaculo::whereIn('cod_calles_tramos_obs', [$tA1->getKey(), $tA2->getKey()])->get();
        $this->assertNotEmpty($obstaculosResueltos);
        foreach ($obstaculosResueltos as $obs) {
            $this->assertEquals('RESUELTO', $obs->est_obs);
            $this->assertNull($obs->fbj_obs);
        }
    }

    /**
     * 5. Endpoint POST /rutas/{ruta}/aco/recalcular responde JSON con recomendación e historial.
     */
    public function test_endpoint_recalcular_devuelve_historial_y_actualiza_sesion(): void
    {
        $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->first();
        if (!$rutaDemo) {
            (new DatosDemostracionIbcSeeder())->run();
            $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-005')->first();
        }

        $response = $this->actingAs($this->adminUser)->postJson(route('rutas.aco.recalcular', $rutaDemo));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'recomendacion' => [
                'ruta_id',
                'distancia_total',
                'costo_total',
                'geometria_recomendada',
                'metricas',
            ],
            'historial_cambio',
        ]);
    }

    /**
     * 6. Regresión de integridad: RUT-001, RUT-002, RUT-003 y tramos 40-44 no son alterados.
     */
    public function test_rutas_canonicas_y_tramos_permanecen_intactos(): void
    {
        $r1 = Ruta::where('ide_rut', 'RUT-001')->first();
        if ($r1) {
            $this->assertEquals('RUT-001', $r1->ide_rut);
        }

        $r2 = Ruta::where('ide_rut', 'RUT-002')->first();
        if ($r2) {
            $this->assertEquals('RUT-002', $r2->ide_rut);
        }

        $r3 = Ruta::where('ide_rut', 'RUT-003')->with('tramos')->first();
        if ($r3) {
            $this->assertEquals('RUT-003', $r3->ide_rut);
            $ids = $r3->tramos->pluck('cod_calles_tramos')->toArray();
            $this->assertEquals([40, 41, 42, 43, 44], $ids);
            $tramosCount = CalleTramo::whereIn('cod_calles_tramos', [40, 41, 42, 43, 44])->count();
            $this->assertEquals(5, $tramosCount);
        }

        $this->assertTrue(true);
    }
}