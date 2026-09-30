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
use App\Services\TramoRiskService;
use App\Services\RutaEstimacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TramoRiskServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected TramoRiskService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new TramoRiskService();
    }

    /**
     * Helper para crear una ruta de prueba temporal.
     */
    private function crearRutaPrueba(): Ruta
    {
        return Ruta::create([
            'ide_rut' => 'RUT-TEST-' . rand(100, 999),
            'nom_rut' => 'RUTA DE PRUEBA FASE 3A',
            'ori_rut' => 'ORIGEN TEST',
            'dst_rut' => 'DESTINO TEST',
            'est_rut' => 'BORRADOR',
        ]);
    }

    /**
     * Caso A: Tramo sin información.
     * Los valores desconocidos deben devolver null, no cero.
     */
    public function test_caso_a_tramo_sin_informacion()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO SIN INFO TEST',
            'tip_ctr'       => 'ACERA',
            'lon_ctr'       => 100.0,
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertNull($perfil['riesgo_evaluacion']);
        $this->assertEquals('NO_EVALUADO', $perfil['estado_evaluacion']);
        $this->assertNull($perfil['nivel_seguridad_nombre']);
        $this->assertNull($perfil['riesgo_condiciones']);
        $this->assertNull($perfil['riesgo_zonas_max']);
        $this->assertEquals(0, $perfil['obstaculos_activos_count']);
        $this->assertEquals(0, $perfil['obstaculos_reportados_count']);
        $this->assertNull($perfil['prioridad_maxima']);
        $this->assertNull($perfil['prioridad_normalizada']);
        $this->assertEquals('SIN_DATOS', $perfil['cobertura_datos']);
        $this->assertEquals('SIN_DATOS', $perfil['completitud_datos']);
    }

    /**
     * Caso B: Seguridad 5 (ÓPTIMO) -> riesgo_evaluacion = 0.00
     */
    public function test_caso_b_seguridad_5_optimo()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO OPTIMO TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        $nivel5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();

        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(0.00, $perfil['riesgo_evaluacion']);
        $this->assertEquals('EVALUADO', $perfil['estado_evaluacion']);
        $this->assertEquals('ÓPTIMO', $perfil['nivel_seguridad_nombre']);
    }

    /**
     * Caso C: Seguridad 1 (CRÍTICO) -> riesgo_evaluacion = 1.00
     */
    public function test_caso_c_seguridad_1_critico()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO CRITICO TEST',
            'tip_ctr'       => 'CRUCE',
        ]);

        $nivel1 = NivelSeguridad::where('niv_nse', 1)->firstOrFail();

        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel1->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(1.00, $perfil['riesgo_evaluacion']);
        $this->assertEquals('CRÍTICO', $perfil['nivel_seguridad_nombre']);
    }

    /**
     * Caso D: Condiciones BUENO (0.00) + REGULAR (0.50) + MALO (1.00)
     * Promedio esperado = 0.50
     */
    public function test_caso_d_condiciones_promedio()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO CONDICIONES TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen' => 'PAVIMENTO',
            'des_cen' => 'BUEN ESTADO',
            'niv_cen' => 'BUENO', // 0.00
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen' => 'ILUMINACIÓN',
            'des_cen' => 'LUZ MEDIA',
            'niv_cen' => 'REGULAR', // 0.50
        ]);
        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen' => 'PENDIENTE',
            'des_cen' => 'PENDIENTE EMPINADA',
            'niv_cen' => 'MALO', // 1.00
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(0.50, $perfil['riesgo_condiciones']);
        $this->assertEquals(3, $perfil['condiciones_count']);
    }

    /**
     * Caso E: Múltiples zonas activas -> utilizar la de mayor riesgo.
     */
    public function test_caso_e_multiples_zonas_mayor_riesgo()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO ZONAS TEST',
            'tip_ctr'       => 'CALLE',
        ]);

        $nivel4 = NivelSeguridad::where('niv_nse', 4)->firstOrFail(); // riesgo = (5-4)/4 = 0.25
        $nivel2 = NivelSeguridad::where('niv_nse', 2)->firstOrFail(); // riesgo = (5-2)/4 = 0.75

        ZonaRiesgo::create([
            'nom_zri'                   => 'ZONA ALTO NIVEL',
            'cod_calles_tramos_zri'     => $tramo->getKey(),
            'cod_niveles_seguridad_zri' => $nivel4->getKey(),
            'act_zri'                   => true,
        ]);

        ZonaRiesgo::create([
            'nom_zri'                   => 'ZONA BAJO NIVEL (MAYOR RIESGO)',
            'cod_calles_tramos_zri'     => $tramo->getKey(),
            'cod_niveles_seguridad_zri' => $nivel2->getKey(),
            'act_zri'                   => true,
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(0.75, $perfil['riesgo_zonas_max']);
        $this->assertEquals(2, $perfil['zonas_activas_count']);
    }

    /**
     * Caso F: Obstáculos -> contabilizar correctamente ACTIVO y REPORTADO.
     */
    public function test_caso_f_obstaculos_conteo()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO OBSTACULOS TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'POSTE',
            'des_obs'               => 'POSTE CAYENDO',
            'est_obs'               => 'ACTIVO',
        ]);
        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'OBRA',
            'des_obs'               => 'ZANJA ABIERTA',
            'est_obs'               => 'REPORTADO',
        ]);
        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'BACHE',
            'des_obs'               => 'BACHE RESUELTO',
            'est_obs'               => 'RESUELTO',
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(1, $perfil['obstaculos_activos_count']);
        $this->assertEquals(1, $perfil['obstaculos_reportados_count']);
        $this->assertContains('POSTE', $perfil['tipos_obstaculos']);
        $this->assertContains('OBRA', $perfil['tipos_obstaculos']);
        $this->assertNotContains('BACHE', $perfil['tipos_obstaculos']);
    }

    /**
     * Caso G: Obstáculo sin reporte asociado -> prioridad debe permanecer desconocida (null).
     */
    public function test_caso_g_obstaculo_sin_reporte_asociado()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO SIN REPORTE TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'VEHÍCULO',
            'des_obs'               => 'VEHÍCULO MAL ESTACIONADO',
            'est_obs'               => 'ACTIVO',
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertFalse($perfil['tiene_reportes_asociados']);
        $this->assertNull($perfil['prioridad_maxima']);
        $this->assertNull($perfil['prioridad_normalizada']);
    }

    /**
     * Caso H: Verificar que rutas y OSRM continúan intactos.
     */
    public function test_caso_h_rutas_y_osrm_intactos()
    {
        $estimador = new RutaEstimacionService();
        $this->assertInstanceOf(RutaEstimacionService::class, $estimador);

        $ruta = $this->crearRutaPrueba();
        $this->assertEquals('BORRADOR', $ruta->est_rut);

        // Probar transiciones de estado de ruta intactas
        $ruta->enviarARevision();
        $this->assertEquals('EN REVISIÓN', $ruta->est_rut);
    }

    /**
     * Caso I: Obstáculo RESUELTO -> no cuenta como obstáculo vigente.
     */
    public function test_caso_i_obstaculo_resuelto_no_es_vigente()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO OBSTACULO RESUELTO TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'ESCALÓN',
            'des_obs'               => 'ESCALÓN REPARADO',
            'est_obs'               => 'RESUELTO',
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertEquals(0, $perfil['obstaculos_activos_count']);
        $this->assertEquals(0, $perfil['obstaculos_reportados_count']);
        $this->assertEmpty($perfil['tipos_obstaculos']);
    }

    /**
     * Caso J: Reporte DESCARTADO asociado -> no modifica prioridad vigente.
     */
    public function test_caso_j_reporte_descartado_no_modifica_prioridad()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO REPORTE DESCARTADO TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'POSTE',
            'des_obs'               => 'POSTE LUZ',
            'est_obs'               => 'ACTIVO',
        ]);

        ReporteObstaculo::create([
            'codigo'             => 'OBS-TEST-' . rand(1000, 9999),
            'tipo_obstaculo'     => 'POSTE',
            'descripcion'        => 'Reporte descartado de alta prioridad',
            'nivel_riesgo'       => 'CRITICO',
            'prioridad'          => 4,
            'estado'             => 'DESCARTADO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertFalse($perfil['tiene_reportes_asociados']);
        $this->assertNull($perfil['prioridad_maxima']);
        $this->assertNull($perfil['prioridad_normalizada']);
    }

    /**
     * Caso K: Reporte RESUELTO asociado -> no modifica prioridad vigente.
     */
    public function test_caso_k_reporte_resuelto_no_modifica_prioridad()
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO REPORTE RESUELTO TEST',
            'tip_ctr'       => 'ACERA',
        ]);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'BACHE',
            'des_obs'               => 'BACHE TAPADO',
            'est_obs'               => 'ACTIVO',
        ]);

        ReporteObstaculo::create([
            'codigo'             => 'OBS-TEST-' . rand(1000, 9999),
            'tipo_obstaculo'     => 'BACHE',
            'descripcion'        => 'Reporte resuelto anteriormente',
            'nivel_riesgo'       => 'ALTO',
            'prioridad'          => 3,
            'estado'             => 'RESUELTO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $perfil = $this->service->obtenerPerfil($tramo);

        $this->assertFalse($perfil['tiene_reportes_asociados']);
        $this->assertNull($perfil['prioridad_maxima']);
        $this->assertNull($perfil['prioridad_normalizada']);
    }
}
