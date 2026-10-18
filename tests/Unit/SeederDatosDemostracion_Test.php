<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use App\Models\EvaluacionRiesgo;
use App\Models\Obstaculo;
use App\Models\ReporteObstaculo;
use App\Models\Ruta;
use App\Models\VerificacionObstaculo;
use App\Models\ZonaRiesgo;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\EstadoAcoRuta;
use App\Services\TramoRiskService;
use Database\Seeders\DatosDemostracionIbcSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Pruebas unitarias de integridad, topología y cobertura del seeder de demostración.
 *
 * Se ejecutan bajo transacciones estrictamente sobre la base de datos de testing.
 */
class SeederDatosDemostracion_Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Asegurar que el entorno sea testing
        $this->assertEquals('testing', config('app.env'));
        $this->assertStringContainsString('testing', config('database.connections.mysql.database'));

        // Sembrar el dataset de demostración para las pruebas
        (new DatosDemostracionIbcSeeder())->run();
    }

    public function test_a_seeder_puebla_cantidades_esperadas_en_rangos_requeridos(): void
    {
        // Rutas demo (4 a 6 requeridas)
        $rutasCount = Ruta::where('ide_rut', 'like', 'DEMO-RUT-%')->count();
        $this->assertGreaterThanOrEqual(4, $rutasCount);
        $this->assertLessThanOrEqual(6, $rutasCount);

        // Tramos demo (15 a 30 requeridos)
        $tramosCount = CalleTramo::where('nom_ctr', 'like', 'DEMO — %')->count();
        $this->assertGreaterThanOrEqual(15, $tramosCount);
        $this->assertLessThanOrEqual(30, $tramosCount);

        // Condiciones de entorno (20 a 40 requeridas)
        $condicionesCount = CondicionEntorno::where('des_cen', 'like', '[DEMO]%')->count();
        $this->assertGreaterThanOrEqual(20, $condicionesCount);
        $this->assertLessThanOrEqual(40, $condicionesCount);

        // Evaluaciones de riesgo (15 a 25 requeridas)
        $evaluacionesCount = EvaluacionRiesgo::where('obs_eri', 'like', '[DEMO]%')->count();
        $this->assertGreaterThanOrEqual(15, $evaluacionesCount);
        $this->assertLessThanOrEqual(25, $evaluacionesCount);

        // Zonas de riesgo (8 a 15 requeridas)
        $zonasCount = ZonaRiesgo::where('nom_zri', 'like', 'DEMO — %')->count();
        $this->assertGreaterThanOrEqual(8, $zonasCount);
        $this->assertLessThanOrEqual(15, $zonasCount);

        // Obstáculos (15 a 30 requeridos)
        $obstaculosCount = Obstaculo::where('des_obs', 'like', '[DEMO]%')->count();
        $this->assertGreaterThanOrEqual(15, $obstaculosCount);
        $this->assertLessThanOrEqual(30, $obstaculosCount);

        // Reportes (20 a 40 requeridos)
        $reportesCount = ReporteObstaculo::where('codigo', 'like', 'DEMO-REP-%')->count();
        $this->assertGreaterThanOrEqual(20, $reportesCount);
        $this->assertLessThanOrEqual(40, $reportesCount);

        // Verificaciones colaborativas demo
        $verificacionesCount = VerificacionObstaculo::where('observacion', 'like', '%DEMO%')->count();
        $this->assertGreaterThanOrEqual(2, $verificacionesCount);
    }

    public function test_b_escenarios_obligatorios_rutas_y_coberturas(): void
    {
        $constructor = new ConstructorGrafoRuta();

        // 1. Ruta sin tramos
        $r1 = Ruta::where('ide_rut', 'DEMO-RUT-001')->firstOrFail();
        $analisis1 = $constructor->analizar($r1);
        $this->assertSame(0, $analisis1->cantidadTramos);
        $this->assertSame(EstadoAcoRuta::SIN_TRAMOS, $analisis1->estado);

        // 2. Ruta con tramos pero SIN_DATOS
        $r2 = Ruta::where('ide_rut', 'DEMO-RUT-002')->firstOrFail();
        $analisis2 = $constructor->analizar($r2);
        $this->assertSame(3, $analisis2->cantidadTramos);
        $this->assertSame(TramoRiskService::COBERTURA_SIN_DATOS, $analisis2->coberturaRiesgo);
        $this->assertTrue($analisis2->esLineal);
        $this->assertFalse($analisis2->esOptimizable);

        // 3. Ruta con cobertura PARCIAL
        $r3 = Ruta::where('ide_rut', 'DEMO-RUT-003')->firstOrFail();
        $analisis3 = $constructor->analizar($r3);
        $this->assertSame(2, $analisis3->cantidadTramos);
        $this->assertSame(TramoRiskService::COBERTURA_PARCIAL, $analisis3->coberturaRiesgo);

        // 4. Ruta lineal con información COMPLETA
        $r4 = Ruta::where('ide_rut', 'DEMO-RUT-004')->firstOrFail();
        $analisis4 = $constructor->analizar($r4);
        $this->assertSame(3, $analisis4->cantidadTramos);
        $this->assertSame(TramoRiskService::COBERTURA_COMPLETO, $analisis4->coberturaRiesgo);
        $this->assertTrue($analisis4->esLineal);
        $this->assertSame(EstadoAcoRuta::GRAFO_LINEAL, $analisis4->estado);
        $this->assertFalse($analisis4->esOptimizable);
    }

    public function test_c_escenario_aco_grafo_con_bifurcaciones_y_multiples_caminos(): void
    {
        $constructor = new ConstructorGrafoRuta();

        // 5. Ruta con bifurcaciones y dos o más caminos
        $r5 = Ruta::where('ide_rut', 'DEMO-RUT-005')->firstOrFail();
        $analisis5 = $constructor->analizar($r5);

        $this->assertSame(8, $analisis5->cantidadTramos);
        $this->assertSame(7, $analisis5->cantidadNodos);
        $this->assertFalse($analisis5->esLineal);
        $this->assertGreaterThanOrEqual(2, $analisis5->cantidadAlternativas);
        $this->assertSame(TramoRiskService::COBERTURA_PARCIAL, $analisis5->coberturaRiesgo);
        $this->assertTrue($analisis5->tieneRiesgoSuficiente);
        $this->assertSame(EstadoAcoRuta::LISTO_PARA_OPTIMIZAR, $analisis5->estado);
        $this->assertTrue($analisis5->esOptimizable);
    }

    public function test_d_escenarios_obligatorios_evaluaciones_condiciones_y_obstaculos(): void
    {
        // 6, 7, 8. Condiciones BUENO, REGULAR, MALO
        $this->assertTrue(CondicionEntorno::where('des_cen', 'like', '[DEMO]%')->where('niv_cen', 'BUENO')->exists());
        $this->assertTrue(CondicionEntorno::where('des_cen', 'like', '[DEMO]%')->where('niv_cen', 'REGULAR')->exists());
        $this->assertTrue(CondicionEntorno::where('des_cen', 'like', '[DEMO]%')->where('niv_cen', 'MALO')->exists());

        // 9 a 13. Evaluaciones en los 5 niveles de seguridad
        for ($niv = 1; $niv <= 5; $niv++) {
            $existeEval = EvaluacionRiesgo::where('obs_eri', 'like', '[DEMO]%')
                ->whereHas('nivelSeguridad', fn ($q) => $q->where('niv_nse', $niv))
                ->exists();
            $this->assertTrue($existeEval, "Debe existir evaluación con nivel de seguridad {$niv}");
        }

        // 14. Tramo sin obstáculos vigentes (Tramo Seguro B1)
        $tB1 = CalleTramo::where('nom_ctr', 'like', '%ACO B1%')->firstOrFail();
        $this->assertSame(0, $tB1->obstaculos()->whereIn('est_obs', ['ACTIVO', 'REPORTADO'])->count());

        // 15. Tramo con obstáculo vigente (Tramo C1 o Lin1)
        $tLin1 = CalleTramo::where('nom_ctr', 'like', '%LINEAL COMPLETO 1%')->firstOrFail();
        $this->assertGreaterThanOrEqual(1, $tLin1->obstaculos()->whereIn('est_obs', ['ACTIVO', 'REPORTADO'])->count());

        // 16. Tramo con varios obstáculos vigentes (Tramo crítico A1 o A2)
        $tA1 = CalleTramo::where('nom_ctr', 'like', '%ACO A1%')->firstOrFail();
        $this->assertGreaterThanOrEqual(2, $tA1->obstaculos()->whereIn('est_obs', ['ACTIVO', 'REPORTADO'])->count());

        // 17. Obstáculo resuelto
        $this->assertTrue(Obstaculo::where('des_obs', 'like', '[DEMO]%')->where('est_obs', 'RESUELTO')->exists());

        // 18. Zona de riesgo activa
        $this->assertTrue(ZonaRiesgo::where('nom_zri', 'like', 'DEMO — %')->where('act_zri', true)->exists());

        // 19. Reporte manual
        $this->assertTrue(ReporteObstaculo::where('codigo', 'like', 'DEMO-REP-%')->where('metodo_deteccion', 'MANUAL')->exists());

        // 20. Reporte simulado proveniente de bastón (marcado explícito)
        $reportesBaston = ReporteObstaculo::where('codigo', 'like', 'DEMO-REP-%')->where('metodo_deteccion', 'BASTON')->get();
        $this->assertNotEmpty($reportesBaston);
        foreach ($reportesBaston as $repBaston) {
            $this->assertStringContainsString('SIMULACIÓN DE DETECCIÓN POR BASTÓN', $repBaston->descripcion);
        }
    }

    public function test_e_reversibilidad_limpieza_segura_de_datos_demo(): void
    {
        // Limpiar exclusivamente los datos demo
        DatosDemostracionIbcSeeder::limpiarDemo();

        // Verificar que ningún registro demo subsista
        $this->assertSame(0, Ruta::where('ide_rut', 'like', 'DEMO-RUT-%')->count());
        $this->assertSame(0, CalleTramo::where('nom_ctr', 'like', 'DEMO — %')->count());
        $this->assertSame(0, CondicionEntorno::where('des_cen', 'like', '[DEMO]%')->count());
        $this->assertSame(0, EvaluacionRiesgo::where('obs_eri', 'like', '[DEMO]%')->count());
        $this->assertSame(0, ZonaRiesgo::where('nom_zri', 'like', 'DEMO — %')->count());
        $this->assertSame(0, Obstaculo::where('des_obs', 'like', '[DEMO]%')->count());
        $this->assertSame(0, ReporteObstaculo::where('codigo', 'like', 'DEMO-REP-%')->count());
        $this->assertSame(0, VerificacionObstaculo::where('observacion', 'like', '%DEMO%')->count());

        // Volver a sembrar de forma idempotente
        DatosDemostracionIbcSeeder::sembrarDemo();
        $this->assertGreaterThan(0, Ruta::where('ide_rut', 'like', 'DEMO-RUT-%')->count());
        $this->assertGreaterThan(0, CalleTramo::where('nom_ctr', 'like', 'DEMO — %')->count());
    }

    public function test_f_modelo_nm_reutilizable_demostrado(): void
    {
        // Tramo ACO B1 está asociado a RUTA 5 y a RUTA 6
        $tB1 = CalleTramo::where('nom_ctr', 'like', '%ACO B1%')->firstOrFail();
        $rutasAsociadas = $tB1->rutas()->get();

        $this->assertCount(2, $rutasAsociadas);
        $idsRutas = $rutasAsociadas->pluck('ide_rut')->all();
        $this->assertContains('DEMO-RUT-005', $idsRutas);
        $this->assertContains('DEMO-RUT-006', $idsRutas);
    }
}