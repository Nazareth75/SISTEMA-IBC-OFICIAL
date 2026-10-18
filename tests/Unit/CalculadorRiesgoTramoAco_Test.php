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
use App\Services\Aco\CalculadorRiesgoTramoAco;
use App\Services\TramoRiskService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;

class CalculadorRiesgoTramoAco_Test extends TestCase
{
    use DatabaseTransactions;

    private CalculadorRiesgoTramoAco $calculador;
    private TramoRiskService $riskService;

    public function setUp(): void
    {
        parent::setUp();
        $this->riskService = new TramoRiskService();
        $this->calculador = new CalculadorRiesgoTramoAco(0.25, 0.25, 0.25, 0.25, $this->riskService);
    }

    private function crearRutaPrueba(): Ruta
    {
        return Ruta::create([
            'ide_rut' => 'RUT-TEST-' . rand(1000, 9999),
            'nom_rut' => 'RUTA TEST ACO RIESGO',
            'ori_rut' => 'ORIGEN',
            'dst_rut' => 'DESTINO',
            'est_rut' => 'BORRADOR',
        ]);
    }

    public function test_pesos_validos_son_aceptados_y_suman_uno(): void
    {
        $calc = new CalculadorRiesgoTramoAco(0.40, 0.30, 0.20, 0.10);
        $pesos = $calc->obtenerPesos();

        $this->assertEquals(0.40, $pesos['w_evaluacion']);
        $this->assertEquals(0.30, $pesos['w_condiciones']);
        $this->assertEquals(0.20, $pesos['w_zonas']);
        $this->assertEquals(0.10, $pesos['w_obstaculos']);
    }

    public function test_peso_negativo_es_rechazado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/no puede ser negativo/i');
        new CalculadorRiesgoTramoAco(-0.10, 0.40, 0.40, 0.30);
    }

    public function test_suma_pesos_diferente_de_uno_es_rechazada(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/debe ser igual a 1.0/i');
        new CalculadorRiesgoTramoAco(0.30, 0.30, 0.30, 0.30);
    }

    public function test_pesos_nan_o_inf_son_rechazados(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CalculadorRiesgoTramoAco(NAN, 0.33, 0.33, 0.34);
    }

    public function test_componente_negativo_es_rechazado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/acotado estrictamente en \[0.0, 1.0\]/i');
        $this->calculador->calcularDesdeComponentes(-0.1, 0.5, 0.5, 0.5);
    }

    public function test_componente_mayor_a_uno_es_rechazado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/acotado estrictamente en \[0.0, 1.0\]/i');
        $this->calculador->calcularDesdeComponentes(1.05, 0.5, 0.5, 0.5);
    }

    public function test_componente_inf_o_nan_es_rechazado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculador->calcularDesdeComponentes(0.5, INF, 0.5, 0.5);
    }

    public function test_calculo_escalar_ponderado_exacto(): void
    {
        $rNorm = $this->calculador->calcularDesdeComponentes(0.0, 0.50, 1.0, 0.75);
        $this->assertEquals(0.5625, $rNorm);
        $this->assertGreaterThanOrEqual(0.0, $rNorm);
        $this->assertLessThanOrEqual(1.0, $rNorm);
    }

    public function test_riesgo_cero_con_componentes_optimos(): void
    {
        $rNorm = $this->calculador->calcularDesdeComponentes(0.0, 0.0, 0.0, 0.0);
        $this->assertEquals(0.0, $rNorm);
    }

    public function test_riesgo_uno_con_componentes_criticos(): void
    {
        $rNorm = $this->calculador->calcularDesdeComponentes(1.0, 1.0, 1.0, 1.0);
        $this->assertEquals(1.0, $rNorm);
    }

    public function test_tramo_sin_datos_es_rechazado_sin_inventar_cero(): void
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO COMPLETAMENTE DESCONOCIDO',
            'tip_ctr'       => 'ACERA',
            'lon_ctr'       => 100.0,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/cobertura SIN_DATOS/i');
        $this->calculador->calcular($tramo);
    }

    public function test_tramo_evaluado_sin_condiciones_es_rechazado(): void
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO EVALUADO SIN CONDICIONES',
            'tip_ctr'       => 'ACERA',
        ]);

        $nivel5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/condiciones de entorno registradas/i');
        $this->calculador->calcular($tramo);
    }

    public function test_tramo_seguro_con_ausencia_verificada_de_zonas_y_obstaculos(): void
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO SEGURO Y VERIFICADO',
            'tip_ctr'       => 'ACERA',
        ]);

        $nivel5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen'               => 'PAVIMENTO',
            'des_cen'               => 'EXCELENTE',
            'niv_cen'               => 'BUENO',
        ]);

        $rNorm = $this->calculador->calcular($tramo);
        $this->assertEquals(0.00, $rNorm);
    }

    public function test_obstaculo_activo_incrementa_riesgo_y_resuelto_no_lo_incrementa(): void
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO DINAMICO CON OBSTACULO',
            'tip_ctr'       => 'ACERA',
        ]);

        $nivel5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen'               => 'PAVIMENTO',
            'des_cen'               => 'BUEN ESTADO',
            'niv_cen'               => 'BUENO',
        ]);

        $rInicial = $this->calculador->calcular($tramo);
        $this->assertEquals(0.00, $rInicial);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'POSTE',
            'des_obs'               => 'POSTE CAÍDO',
            'est_obs'               => 'ACTIVO',
        ]);

        ReporteObstaculo::create([
            'codigo'             => 'REP-TEST-1',
            'tipo_obstaculo'     => 'POSTE',
            'descripcion'        => 'Reporte urgente',
            'nivel_riesgo'       => 'CRITICO',
            'prioridad'          => 4,
            'estado'             => 'ACTIVO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $rConObstaculoActivo = $this->calculador->calcular($tramo);
        $this->assertEquals(0.25, $rConObstaculoActivo);
        $this->assertGreaterThan($rInicial, $rConObstaculoActivo);

        $obs->update(['est_obs' => 'RESUELTO']);
        $rConObstaculoResuelto = $this->calculador->calcular($tramo);

        $this->assertEquals(0.00, $rConObstaculoResuelto);
    }

    public function test_reporte_descartado_no_incrementa_riesgo(): void
    {
        $ruta = $this->crearRutaPrueba();
        $tramo = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO CON REPORTE DESCARTADO',
            'tip_ctr'       => 'ACERA',
        ]);

        $nivel5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::create([
            'cod_calles_tramos_eri'     => $tramo->getKey(),
            'cod_niveles_seguridad_eri' => $nivel5->getKey(),
            'fee_eri'                   => now()->toDateString(),
        ]);

        CondicionEntorno::create([
            'cod_calles_tramos_cen' => $tramo->getKey(),
            'tip_cen'               => 'PAVIMENTO',
            'des_cen'               => 'BUEN ESTADO',
            'niv_cen'               => 'BUENO',
        ]);

        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'OTRO',
            'des_obs'               => 'FALSA ALARMA',
            'est_obs'               => 'RESUELTO',
        ]);

        ReporteObstaculo::create([
            'codigo'             => 'REP-DESC-1',
            'tipo_obstaculo'     => 'OTRO',
            'descripcion'        => 'Reporte descartado',
            'nivel_riesgo'       => 'CRITICO',
            'prioridad'          => 4,
            'estado'             => 'DESCARTADO',
            'cod_obstaculos_rpo' => $obs->getKey(),
        ]);

        $rNorm = $this->calculador->calcular($tramo);
        $this->assertEquals(0.00, $rNorm);
    }
}