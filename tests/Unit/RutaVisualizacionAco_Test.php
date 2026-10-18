<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RutaVisualizacionAco_Test extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::first() ?? User::create([
            'nom_usu'  => 'ADMINISTRADOR TEST',
            'email'    => 'admin_test@ibc.org',
            'password' => bcrypt('password'),
            'rol_usu'  => 'ADMINISTRADOR',
        ]);
    }

    /**
     * Test 1: El listado general /rutas muestra la columna ESTADO ACO.
     */
    public function test_listado_rutas_muestra_columna_estado_aco(): void
    {
        Ruta::create([
            'ide_rut'              => 'RUT-UI-00',
            'nom_rut'              => 'RUTA TEST LISTADO',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('rutas.index'));

        $response->assertStatus(200);
        $response->assertSee('ESTADO ACO');
    }

    /**
     * Test 2: Una ruta sin tramos muestra 'Sin tramos' en el listado general.
     */
    public function test_ruta_sin_tramos_muestra_badge_sin_tramos_en_listado(): void
    {
        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-UI-01',
            'nom_rut'              => 'RUTA SIN SEGMENTOS UI',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('rutas.index'));

        $response->assertStatus(200);
        $response->assertSee('RUT-UI-01');
        $response->assertSee('Sin tramos');
    }

    /**
     * Test 3: rutas.show muestra la sección 'RUTA RECOMENDADA' y los acordeones técnicos para administradores.
     */
    public function test_detalle_ruta_muestra_seccion_aco_y_detalles_tecnicos(): void
    {
        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-UI-02',
            'nom_rut'              => 'RUTA PARA VER SECCION ACO',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('rutas.show', $ruta));

        $response->assertStatus(200);
        $response->assertSee('RUTA RECOMENDADA');
        $response->assertSee('VER DETALLES TÉCNICOS Y BENCHMARKING DE ACO');
        $response->assertSee('LEYENDA:');
        $response->assertSee('Ruta recomendada');
    }

    /**
     * Test 4: Una nueva ruta sin tramos muestra la indicación 'OPTIMIZACIÓN ACO NO DISPONIBLE'.
     */
    public function test_ruta_sin_tramos_muestra_indicacion_siguiente_paso(): void
    {
        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-UI-03',
            'nom_rut'              => 'RUTA RECIEN CREADA',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('rutas.show', $ruta));

        $response->assertStatus(200);
        $response->assertSee('OPTIMIZACIÓN ACO NO DISPONIBLE ACTUALMENTE PARA ESTA RUTA');
        $response->assertSee('La ruta no posee tramos viales asociados.');
    }

    /**
     * Test 5 (SECCIÓN 19): Tramo sin datos muestra 'RIESGO NO EVALUADO' y NUNCA 'Sin riesgo'.
     */
    public function test_tramo_sin_datos_muestra_riesgo_no_evaluado_y_nunca_sin_riesgo(): void
    {
        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-UI-04',
            'nom_rut'              => 'RUTA CON TRAMOS LINEALES SIN DATOS',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'SEGMENTO TEST UI SIN DATOS',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 180.50,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5015, 'lng' => -68.1315],
            ],
        ]);

        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $response = $this->actingAs($this->adminUser)->get(route('rutas.show', $ruta));

        $response->assertStatus(200);
        $response->assertSee('TABLA DE TRAMOS Y RIESGO VIAL');
        $response->assertSee('SEGMENTO TEST UI SIN DATOS');
        $response->assertSee('SIN_DATOS');
        $response->assertSee('RIESGO NO EVALUADO');
        $response->assertDontSee('Sin riesgo');
    }
}