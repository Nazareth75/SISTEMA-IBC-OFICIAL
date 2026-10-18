<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use App\Models\EvaluacionRiesgo;
use App\Models\NivelSeguridad;
use App\Models\Obstaculo;
use App\Models\Role;
use App\Models\Ruta;
use App\Models\User;
use App\Models\VerificacionObstaculo;
use App\Services\Aco\ConfiguracionAco;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use App\Services\Aco\ServicioRecomendacionRutaAco;
use Database\Seeders\DatosDemostracionIbcSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Pruebas unitarias y de integración de acciones sobre Rutas, Tramos Compartidos,
 * Roles, Obstáculos y el Escenario Dinámico ACO.
 */
class AccionesRutasAco_Test extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $voluntario;
    private User $tecnico;
    private ServicioEstadoRecomendacionAco $estadoAcoService;
    private ServicioRecomendacionRutaAco $recomendadorAco;
    private ConstructorGrafoRuta $constructorAco;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estadoAcoService = new ServicioEstadoRecomendacionAco();
        $this->recomendadorAco  = new ServicioRecomendacionRutaAco();
        $this->constructorAco   = new ConstructorGrafoRuta();

        // Sembrar dataset demo
        (new DatosDemostracionIbcSeeder())->run();

        // Resolver o crear usuarios con roles tipificados
        $this->admin = $this->crearOObtenerUsuario('ADMIN_TEST', 'ADMINISTRADOR', 'ADMINISTRADOR');
        $this->voluntario = $this->crearOObtenerUsuario('VOL_TEST', 'VOLUNTARIO', 'VOLUNTARIO');
        $this->tecnico = $this->crearOObtenerUsuario('TEC_TEST', 'TECNICO', 'TECNICO');
    }

    private function crearOObtenerUsuario(string $ide, string $nom, string $rolNombre): User
    {
        $rol = Role::where('nom_rol', $rolNombre)->first();
        $rolId = $rol ? $rol->getKey() : 1;

        return User::firstOrCreate(
            ['ide_usu' => $ide],
            [
                'nom_usu'       => $nom,
                'ape_usu'       => 'TEST',
                'cii_usu'       => 'CI-' . substr(md5($ide), 0, 7),
                'cor_usu'       => strtolower($ide) . '@ibc.org',
                'con_usu'       => bcrypt('password'),
                'cod_roles_usu' => $rolId,
                'est_usu'       => 'ACTIVO',
            ]
        );
    }

    /**
     * 1. Todas las rutas del sistema son editables y eliminables por el administrador.
     */
    public function test_01_todas_las_rutas_son_editables_y_eliminables_por_administrador(): void
    {
        $rutas = Ruta::all();
        $this->assertGreaterThanOrEqual(4, $rutas->count());

        foreach ($rutas as $ruta) {
            $this->assertTrue(
                $ruta->puedeSerEditada($this->admin),
                "La ruta {$ruta->ide_rut} (estado {$ruta->est_rut}) debe ser editable por el administrador."
            );
            $this->assertTrue(
                $ruta->puedeSerEliminada($this->admin),
                "La ruta {$ruta->ide_rut} (estado {$ruta->est_rut}) debe ser eliminable por el administrador."
            );
        }
    }

    /**
     * 2. Rutas canónicas oficiales protegidas (RUT-001, RUT-002, RUT-003) y tramos 40-44 permanecen intactos.
     */
    public function test_02_rutas_oficiales_y_tramos_canonicos_permanecen_intactos(): void
    {
        $rutas = Ruta::all();
        $this->assertNotEmpty($rutas);

        foreach (['RUT-001', 'RUT-002', 'RUT-003'] as $ideRut) {
            $ruta = Ruta::where('ide_rut', $ideRut)->first();
            if ($ruta) {
                $this->assertNull($ruta->fbj_rut, "La ruta {$ideRut} debe permanecer activa e intacta.");
            }
        }

        foreach ([40, 41, 42, 43, 44] as $codTramo) {
            $tramo = CalleTramo::find($codTramo);
            if ($tramo) {
                $this->assertNull($tramo->fbj_ctr, "El tramo oficial {$codTramo} debe permanecer intacto.");
            }
        }
    }

    /**
     * 3. Usuario sin permisos (Voluntario) recibe 403 al intentar editar o eliminar directamente.
     */
    public function test_03_usuario_sin_permiso_no_puede_editar_ni_eliminar_via_http(): void
    {
        $rutaDemo = Ruta::where('ide_rut', 'DEMO-RUT-001')->firstOrFail();

        // Intento de eliminación directa por voluntario
        $resDelete = $this->actingAs($this->voluntario)->delete(route('rutas.destroy', $rutaDemo));
        $resDelete->assertStatus(403);

        // Intento de edición directa por voluntario
        $resEdit = $this->actingAs($this->voluntario)->get(route('rutas.edit', $rutaDemo));
        $resEdit->assertStatus(403);
    }

    /**
     * 4. Edición de una ruta demo persiste correctamente sus atributos.
     */
    public function test_04_administrador_puede_editar_ruta_demo_y_persiste(): void
    {
        $ruta = Ruta::where('ide_rut', 'DEMO-RUT-001')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('rutas.update', $ruta), [
            'nom_rut' => 'DEMO — RUTA 001 RENOMBRADA AUDITORÍA',
            'des_rut' => '[DATOS SIMULADOS DE DEMOSTRACIÓN] Descripción actualizada por auditoría.',
            'ori_rut' => 'Plaza del Estudiante Modificada',
            'dst_rut' => 'Edificio Central IBC Modificado',
            'est_rut' => 'BORRADOR',
        ]);

        $response->assertRedirect(route('rutas.show', $ruta));
        $ruta->refresh();
        $this->assertSame('DEMO — RUTA 001 RENOMBRADA AUDITORÍA', $ruta->nom_rut);
        $this->assertSame('PLAZA DEL ESTUDIANTE MODIFICADA', $ruta->ori_rut);
    }

    /**
     * 5. Eliminación de una ruta demo que comparte tramos no destruye los tramos ni la otra ruta.
     */
    public function test_05_eliminacion_ruta_con_tramos_compartidos_preserva_tramos_y_otra_ruta(): void
    {
        $r5 = Ruta::where('ide_rut', 'DEMO-RUT-005')->firstOrFail();
        $r6 = Ruta::where('ide_rut', 'DEMO-RUT-006')->firstOrFail();

        $tramosCompartidos = $r6->tramos()->pluck('calles_tramos.cod_calles_tramos')->all();
        $this->assertNotEmpty($tramosCompartidos);

        // Administrador elimina RUTA 6
        $response = $this->actingAs($this->admin)->delete(route('rutas.destroy', $r6));
        $response->assertRedirect(route('rutas.index'));
        $response->assertSessionHas('success');

        // R6 eliminada lógicamente
        $this->assertNull(Ruta::find($r6->getKey()));

        // Tramos compartidos siguen existiendo intactos en calles_tramos
        foreach ($tramosCompartidos as $tramoId) {
            $this->assertNotNull(CalleTramo::find($tramoId));
        }

        // RUTA 5 sigue activa y contiene los tramos de la red ACO
        $r5->refresh();
        $this->assertNotNull(Ruta::find($r5->getKey()));
        $this->assertSame(8, $r5->tramos()->count());
    }

    /**
     * 6. Edición estructural invalida la recomendación previa ACO.
     */
    public function test_06_edicion_estructural_invalida_recomendacion_aco(): void
    {
        $r5 = Ruta::where('ide_rut', 'DEMO-RUT-005')->firstOrFail();

        // Generar recomendación inicial
        $rec = $this->recomendadorAco->recomendar($r5);
        $this->assertTrue($rec->success);
        $this->estadoAcoService->guardarRecomendacion($r5, $rec);
        $this->assertFalse($this->estadoAcoService->estaDesactualizada($r5));

        // Actualización estructural: cambio de origen
        $this->actingAs($this->admin)->put(route('rutas.update', $r5), [
            'nom_rut' => $r5->nom_rut,
            'des_rut' => $r5->des_rut,
            'ori_rut' => 'Nuevo Origen Estructural Sopocachi',
            'dst_rut' => $r5->dst_rut,
            'est_rut' => $r5->est_rut,
        ]);

        $this->assertTrue(
            $this->estadoAcoService->estaDesactualizada($r5),
            'Una modificación estructural del origen debe marcar la recomendación como DESACTUALIZADA.'
        );
    }

    /**
     * 7. Obstáculo con verificaciones o reportes asociados impide su eliminación directa para proteger la trazabilidad.
     */
    public function test_07_obstaculo_con_verificaciones_impide_eliminacion_destructiva(): void
    {
        $verificacion = VerificacionObstaculo::where('observacion', 'like', '%DEMO%')->firstOrFail();
        $obstaculo = $verificacion->obstaculo;
        $this->assertNotNull($obstaculo);

        $response = $this->actingAs($this->admin)->delete(route('obstaculos.rechazar', $obstaculo));
        $response->assertSessionHas('error');
        $this->assertNotNull(Obstaculo::find($obstaculo->getKey()));
    }

    /**
     * 8. ESCENARIO COMPLETO DINÁMICO ACO:
     *    - Estado inicial: Camino B (largo pero seguro) recomendado frente a Camino A (corto pero crítico).
     *    - Evento: Resolución de los obstáculos críticos de Camino A mediante flujo colaborativo y confirmación técnica.
     *    - Invalidación: Estado pasa a DESACTUALIZADA.
     *    - Recálculo: ACO recomienda Camino A de forma natural porque C(A) < C(B).
     */
    public function test_08_demostracion_dinamica_aco_cambio_natural_camino_b_hacia_camino_a(): void
    {
        $r5 = Ruta::where('ide_rut', 'DEMO-RUT-005')->firstOrFail();

        $tAcoA1 = CalleTramo::where('nom_ctr', 'like', '%ACO A1%')->firstOrFail();
        $tAcoA2 = CalleTramo::where('nom_ctr', 'like', '%ACO A2%')->firstOrFail();
        $tAcoB1 = CalleTramo::where('nom_ctr', 'like', '%ACO B1%')->firstOrFail();
        $tAcoB2 = CalleTramo::where('nom_ctr', 'like', '%ACO B2%')->firstOrFail();

        $config = new ConfiguracionAco(ants: 20, iterations: 25, wDistance: 0.5, wRisk: 0.5, seed: 42);

        // 1. Ejecución inicial ANTES:
        $rec1 = $this->recomendadorAco->recomendar($r5, $config);
        $this->assertTrue($rec1->success);
        $this->estadoAcoService->guardarRecomendacion($r5, $rec1);

        // Camino B es el más seguro inicialmente debido a los obstáculos críticos en A
        $this->assertNotContains($tAcoA1->getKey(), $rec1->tramosSeleccionadosIds);
        $costoAntes = $rec1->costoTotal;

        // 2. Evento del entorno: El obstáculo en el tramo A1 y A2 son resueltos
        Obstaculo::whereIn('cod_calles_tramos_obs', [$tAcoA1->getKey(), $tAcoA2->getKey()])
            ->whereIn('est_obs', ['ACTIVO', 'REPORTADO'])
            ->update(['est_obs' => 'RESUELTO']);

        $niv5 = NivelSeguridad::where('niv_nse', 5)->firstOrFail();
        EvaluacionRiesgo::whereIn('cod_calles_tramos_eri', [$tAcoA1->getKey(), $tAcoA2->getKey()])
            ->update(['cod_niveles_seguridad_eri' => $niv5->getKey()]);
        CondicionEntorno::whereIn('cod_calles_tramos_cen', [$tAcoA1->getKey(), $tAcoA2->getKey()])
            ->update(['niv_cen' => 'BUENO']);

        // Invalidador notifica el cambio de riesgo en los tramos de la ruta
        $this->estadoAcoService->marcarDesactualizadaPorModificacion(
            $r5,
            'Se confirmaron y resolvieron obstáculos críticos en la ruta.'
        );

        // 3. Comprobar que la recomendación previa quedó DESACTUALIZADA
        $this->assertTrue($this->estadoAcoService->estaDesactualizada($r5));

        // 4. Recálculo dinámico DESPUÉS:
        $rec2 = $this->recomendadorAco->recomendar($r5, $config);
        $this->assertTrue($rec2->success);
        $this->estadoAcoService->guardarRecomendacion($r5, $rec2);

        // Ahora Camino A está libre de obstáculos, su distancia es menor (444.8 m vs 903.4 m)
        // y su riesgo ya no está castigado por obstáculos críticos.
        // Por ende, la función canónica C(e) = wD*D_norm + wR*R_norm favorece a Camino A!
        $this->assertContains($tAcoA1->getKey(), $rec2->tramosSeleccionadosIds);
        $this->assertContains($tAcoA2->getKey(), $rec2->tramosSeleccionadosIds);
        $this->assertLessThan($costoAntes, $rec2->costoTotal);
        $this->assertLessThan($rec1->distanciaTotal, $rec2->distanciaTotal);

        // Comprobar que el historial registra la transición
        $historial = $this->estadoAcoService->obtenerHistorialCambio($r5);
        $this->assertNotNull($historial);
        $this->assertTrue($historial['cambio_ruta_efectivo']);
        $this->assertLessThan(0, $historial['delta_distancia']); // Camino A es más corto
        $this->assertLessThan(0, $historial['delta_costo']);     // Menor costo global
    }
}