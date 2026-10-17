<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RutaAcoUiTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Obtener o crear usuario con permisos para visualizar rutas
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
     * Test 3: rutas.show siempre muestra la sección 'OPTIMIZACIÓN DE RUTA CON ACO' para cualquier ruta.
     */
    public function test_detalle_ruta_siempre_muestra_seccion_aco(): void
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
        $response->assertSee('OPTIMIZACIÓN DE RUTA CON ACO');
        $response->assertSee('CHECKLIST DE PREPARACIÓN PARA OPTIMIZACIÓN ACO');
        $response->assertSee('EJECUTAR ACO (DESHABILITADO)');
    }

    /**
     * Test 4: Una nueva ruta sin tramos muestra la indicación 'SIGUIENTE PASO: CONFIGURAR TRAMOS'.
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
        $response->assertSee('SIGUIENTE PASO: CONFIGURAR TRAMOS DE LA RUTA');
        $response->assertSee('AGREGAR O ASOCIAR TRAMOS EN EDICIÓN');
    }

    /**
     * Test 5: Una ruta con tramos muestra la tabla detallada de tramos con conexiones de nodos y 'SIN DATOS'.
     */
    public function test_ruta_con_tramos_muestra_tabla_con_conexiones_y_sin_datos(): void
    {
        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-UI-04',
            'nom_rut'              => 'RUTA CON TRAMOS LINEALES',
            'ori_rut'              => 'PUNTO A',
            'dst_rut'              => 'PUNTO B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $this->adminUser->getKey(),
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'SEGMENTO TEST UI',
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
        $response->assertSee('TRAMOS DE LA RUTA Y CONEXIONES TOPOLÓGICAS');
        $response->assertSee('SEGMENTO TEST UI');
        $response->assertSee('180.5 M');
        $response->assertSee('N0');
        $response->assertSee('N1');
        $response->assertSee('SIN DATOS');
        $response->assertSee('Sin riesgo');
        $response->assertSee('EJECUTAR ACO (DESHABILITADO)');
    }
}
