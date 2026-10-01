<?php

namespace Tests\Unit;

use App\Models\CalleTramo;
use App\Models\Obstaculo;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TramoReusableTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Caso A: Un tramo puede pertenecer a una ruta.
     */
    public function test_caso_a_un_tramo_puede_pertenecer_a_una_ruta(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-A',
            'nom_rut' => 'RUTA DE PRUEBA A',
            'ori_rut' => 'ORIGEN A',
            'dst_rut' => 'DESTINO A',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO COMPARTIDO 1',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 150.00,
        ]);

        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $this->assertCount(1, $ruta->fresh()->tramos);
        $this->assertEquals($tramo->getKey(), $ruta->tramos->first()->getKey());
        $this->assertEquals(1, $ruta->tramos->first()->pivot->ord_rct);
    }

    /**
     * Caso B: El mismo tramo puede pertenecer a dos rutas.
     */
    public function test_caso_b_el_mismo_tramo_puede_pertenecer_a_dos_rutas(): void
    {
        $ruta1 = Ruta::create([
            'ide_rut' => 'RUT-TEST-B1',
            'nom_rut' => 'RUTA B1',
            'ori_rut' => 'ORIGEN B1',
            'dst_rut' => 'DESTINO B1',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $ruta2 = Ruta::create([
            'ide_rut' => 'RUT-TEST-B2',
            'nom_rut' => 'RUTA B2',
            'ori_rut' => 'ORIGEN B2',
            'dst_rut' => 'DESTINO B2',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO REUTILIZADO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 200.00,
        ]);

        $ruta1->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);
        $ruta2->tramos()->attach($tramo->getKey(), ['ord_rct' => 3]);

        $this->assertCount(2, $tramo->rutas);
        $this->assertTrue($tramo->rutas->contains($ruta1));
        $this->assertTrue($tramo->rutas->contains($ruta2));
    }

    /**
     * Caso C y D: El orden puede ser diferente en cada ruta y Ruta::tramos devuelve los segmentos en el orden correcto.
     */
    public function test_caso_c_d_orden_diferente_y_ordenamiento_correcto(): void
    {
        $ruta1 = Ruta::create([
            'ide_rut' => 'RUT-TEST-C1',
            'nom_rut' => 'RUTA C1',
            'ori_rut' => 'ORIGEN C1',
            'dst_rut' => 'DESTINO C1',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $ruta2 = Ruta::create([
            'ide_rut' => 'RUT-TEST-C2',
            'nom_rut' => 'RUTA C2',
            'ori_rut' => 'ORIGEN C2',
            'dst_rut' => 'DESTINO C2',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $tramoX = CalleTramo::create(['nom_ctr' => 'TRAMO X', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);
        $tramoY = CalleTramo::create(['nom_ctr' => 'TRAMO Y', 'tip_ctr' => 'CRUCE', 'lon_ctr' => 50]);

        // En Ruta 1: X (orden 1), Y (orden 2)
        $ruta1->tramos()->attach($tramoX->getKey(), ['ord_rct' => 1]);
        $ruta1->tramos()->attach($tramoY->getKey(), ['ord_rct' => 2]);

        // En Ruta 2: Y (orden 1), X (orden 2)
        $ruta2->tramos()->attach($tramoY->getKey(), ['ord_rct' => 1]);
        $ruta2->tramos()->attach($tramoX->getKey(), ['ord_rct' => 2]);

        $tramosRuta1 = $ruta1->fresh()->tramos;
        $tramosRuta2 = $ruta2->fresh()->tramos;

        $this->assertEquals($tramoX->getKey(), $tramosRuta1[0]->getKey());
        $this->assertEquals($tramoY->getKey(), $tramosRuta1[1]->getKey());

        $this->assertEquals($tramoY->getKey(), $tramosRuta2[0]->getKey());
        $this->assertEquals($tramoX->getKey(), $tramosRuta2[1]->getKey());
    }

    /**
     * Caso E: geo_ctr válido es aceptado y calcula distancia geodésica.
     */
    public function test_caso_e_geo_ctr_valido_es_aceptado_y_calcula_distancia(): void
    {
        $geoValido = [
            ['lat' => -16.502975, 'lng' => -68.136334],
            ['lat' => -16.502910, 'lng' => -68.136307],
            ['lat' => -16.502800, 'lng' => -68.136200],
        ];

        $this->assertTrue(CalleTramo::esGeoValido($geoValido));

        $distanciaCalculada = CalleTramo::calcularLongitudGeodesica($geoValido);
        $this->assertGreaterThan(0, $distanciaCalculada);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO GEO CON CÁLCULO',
            'tip_ctr' => 'ACERA',
            'geo_ctr' => $geoValido,
            'lon_ctr' => $distanciaCalculada,
        ]);

        $this->assertEquals($geoValido, $tramo->fresh()->geo_ctr);
        $this->assertEquals($distanciaCalculada, (float) $tramo->fresh()->lon_ctr);
    }

    /**
     * Caso F: Coordenadas inválidas son rechazadas por el validador helper.
     */
    public function test_caso_f_coordenadas_invalidas_son_rechazadas(): void
    {
        // Menos de 2 puntos
        $this->assertFalse(CalleTramo::esGeoValido([['lat' => -16.5, 'lng' => -68.1]]));

        // Puntos sin lat/lng
        $this->assertFalse(CalleTramo::esGeoValido([['x' => 10, 'y' => 20], ['x' => 30, 'y' => 40]]));

        // Latitud fuera de rango (>90)
        $this->assertFalse(CalleTramo::esGeoValido([
            ['lat' => -16.5, 'lng' => -68.1],
            ['lat' => 95.0, 'lng' => -68.1],
        ]));

        // No es arreglo
        $this->assertFalse(CalleTramo::esGeoValido("string invalido"));
    }

    /**
     * Caso G: Perfil de riesgo sigue funcionando.
     */
    public function test_caso_g_perfil_riesgo_sigue_funcionando(): void
    {
        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO CON PERFIL',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 120.00,
        ]);

        $perfil = $tramo->perfilRiesgo();

        $this->assertIsArray($perfil);
        $this->assertEquals($tramo->getKey(), $perfil['tramo_id']);
        $this->assertEquals('SIN_DATOS', $perfil['cobertura_datos']);
    }

    /**
     * Caso H e I: Obstáculo en tramo compartido desactualiza todas las rutas PUBLICADAS y no modifica BORRADOR/EN REVISIÓN.
     */
    public function test_caso_h_i_obstaculo_desactualiza_rutas_publicadas_compartidas(): void
    {
        $user = User::first() ?? User::factory()->create();

        $rutaPublicada1 = Ruta::create([
            'ide_rut' => 'RUT-PUB-1',
            'nom_rut' => 'PUBLICADA 1',
            'ori_rut' => 'O1',
            'dst_rut' => 'D1',
            'est_rut' => 'PUBLICADA',
            'cod_usuarios_rut_cre' => $user->getKey(),
        ]);

        $rutaPublicada2 = Ruta::create([
            'ide_rut' => 'RUT-PUB-2',
            'nom_rut' => 'PUBLICADA 2',
            'ori_rut' => 'O2',
            'dst_rut' => 'D2',
            'est_rut' => 'PUBLICADA',
            'cod_usuarios_rut_cre' => $user->getKey(),
        ]);

        $rutaBorrador = Ruta::create([
            'ide_rut' => 'RUT-BORR',
            'nom_rut' => 'BORRADOR 1',
            'ori_rut' => 'O3',
            'dst_rut' => 'D3',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => $user->getKey(),
        ]);

        $tramoCompartido = CalleTramo::create([
            'nom_ctr' => 'TRAMO COMPARTIDO CRÍTICO',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 300.00,
        ]);

        $rutaPublicada1->tramos()->attach($tramoCompartido->getKey(), ['ord_rct' => 1]);
        $rutaPublicada2->tramos()->attach($tramoCompartido->getKey(), ['ord_rct' => 2]);
        $rutaBorrador->tramos()->attach($tramoCompartido->getKey(), ['ord_rct' => 1]);

        // Crear obstáculo en el tramo compartido
        Obstaculo::create([
            'cod_calles_tramos_obs' => $tramoCompartido->getKey(),
            'tip_obs'               => 'BACHE',
            'des_obs'               => 'BACHE PROFUNDO EN TRAMO COMPARTIDO',
            'est_obs'               => 'ACTIVO',
            'ori_obs'               => 'TECNICO',
            'cod_usuarios_obs'      => $user->getKey(),
        ]);

        // Verificar que ambas rutas PUBLICADAS cambiaron a DESACTUALIZADA
        $this->assertEquals('DESACTUALIZADA', $rutaPublicada1->fresh()->est_rut);
        $this->assertEquals('DESACTUALIZADA', $rutaPublicada2->fresh()->est_rut);

        // Verificar que la ruta BORRADOR NO cambió de estado
        $this->assertEquals('BORRADOR', $rutaBorrador->fresh()->est_rut);
    }

    /**
     * Caso J: Migración preserva relaciones legacy si existen tramos previos.
     */
    public function test_caso_j_migracion_preserva_relaciones_legacy(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-LEGACY',
            'nom_rut' => 'RUTA LEGACY',
            'ori_rut' => 'OL',
            'dst_rut' => 'DL',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        // Crear un tramo con cod_rutas_ctr legacy directo
        $tramoLegacy = CalleTramo::create([
            'cod_rutas_ctr' => $ruta->getKey(),
            'nom_ctr'       => 'TRAMO LEGACY DIRECTO',
            'tip_ctr'       => 'ACERA',
            'ord_ctr'       => 5,
            'lon_ctr'       => 180.00,
        ]);

        // Simular ejecución manual del bloque de migración para tramos legacy
        DB::table('ruta_calles_tramos')->insertOrIgnore([
            'cod_rutas_rct'         => $tramoLegacy->cod_rutas_ctr,
            'cod_calles_tramos_rct' => $tramoLegacy->getKey(),
            'ord_rct'               => $tramoLegacy->ord_ctr ?? 1,
            'fcr_rct'               => now(),
            'fmo_rct'               => now(),
        ]);

        $this->assertCount(1, $ruta->fresh()->tramos);
        $this->assertEquals(5, $ruta->tramos->first()->pivot->ord_rct);
    }

    /**
     * Caso K: Mismo ord_rct en una misma ruta es rechazado por la restricción de integridad única.
     */
    public function test_caso_k_mismo_ord_rct_en_ruta_es_rechazado(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-K',
            'nom_rut' => 'RUTA K',
            'ori_rut' => 'OK',
            'dst_rut' => 'DK',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $tramo1 = CalleTramo::create(['nom_ctr' => 'TRAMO K1', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);
        $tramo2 = CalleTramo::create(['nom_ctr' => 'TRAMO K2', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $ruta->tramos()->attach($tramo1->getKey(), ['ord_rct' => 1]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $ruta->tramos()->attach($tramo2->getKey(), ['ord_rct' => 1]);
    }

    /**
     * Caso L: Mismo tramo asociado dos veces a la misma ruta es rechazado por restricción de unicidad.
     */
    public function test_caso_l_mismo_tramo_asociado_dos_veces_a_misma_ruta_es_rechazado(): void
    {
        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-L',
            'nom_rut' => 'RUTA L',
            'ori_rut' => 'OL',
            'dst_rut' => 'DL',
            'est_rut' => 'BORRADOR',
            'cod_usuarios_rut_cre' => 1,
        ]);

        $tramo = CalleTramo::create(['nom_ctr' => 'TRAMO L', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 2]);
    }

    /**
     * Caso M: geo_ctr con un solo punto es rechazado.
     */
    public function test_caso_m_un_solo_punto_geo_ctr_es_rechazado(): void
    {
        $geoUnPunto = [['lat' => -16.5, 'lng' => -68.1]];
        $this->assertFalse(CalleTramo::esGeoValido($geoUnPunto));
    }

    /**
     * Caso N: Latitud fuera del rango [-90, 90] es rechazada.
     */
    public function test_caso_n_latitud_fuera_de_rango_es_rechazada(): void
    {
        $geoLatInvalida = [
            ['lat' => -16.5, 'lng' => -68.1],
            ['lat' => 95.5, 'lng' => -68.1],
        ];
        $this->assertFalse(CalleTramo::esGeoValido($geoLatInvalida));
    }

    /**
     * Caso O: Longitud fuera del rango [-180, 180] es rechazada.
     */
    public function test_caso_o_longitud_fuera_de_rango_es_rechazada(): void
    {
        $geoLngInvalida = [
            ['lat' => -16.5, 'lng' => -68.1],
            ['lat' => -16.6, 'lng' => 190.2],
        ];
        $this->assertFalse(CalleTramo::esGeoValido($geoLngInvalida));
    }

    /**
     * Caso P: Polilínea válida es aceptada y su longitud geodésica es calculada correctamente.
     */
    public function test_caso_p_polilinea_valida_es_aceptada_y_calculada(): void
    {
        $geoPolilineaValida = [
            ['lat' => -16.502975, 'lng' => -68.136334],
            ['lat' => -16.502910, 'lng' => -68.136307],
            ['lat' => -16.502800, 'lng' => -68.136200],
        ];

        $this->assertTrue(CalleTramo::esGeoValido($geoPolilineaValida));
        $longitudMetros = CalleTramo::calcularLongitudGeodesica($geoPolilineaValida);

        $this->assertEquals(24.51, $longitudMetros);
    }

    /**
     * Caso Q: Tramo X pertenece a RUT-A y RUT-B. Crear evaluación desde contexto RUT-B vuelve a RUT-B.
     */
    public function test_caso_q_evaluacion_desde_contexto_rut_b_vuelve_a_rut_b(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $rutaA = Ruta::create(['ide_rut' => 'RUT-Q-A', 'nom_rut' => 'RUTA QA', 'ori_rut' => 'OA', 'dst_rut' => 'DA', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);
        $rutaB = Ruta::create(['ide_rut' => 'RUT-Q-B', 'nom_rut' => 'RUTA QB', 'ori_rut' => 'OB', 'dst_rut' => 'DB', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);

        $tramoX = CalleTramo::create(['nom_ctr' => 'TRAMO X', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $rutaA->tramos()->attach($tramoX->getKey(), ['ord_rct' => 1]);
        $rutaB->tramos()->attach($tramoX->getKey(), ['ord_rct' => 1]);

        $nivel = \App\Models\NivelSeguridad::first();

        $response = $this->post(route('tramos.evaluaciones.store', $tramoX), [
            'cod_rutas' => $rutaB->getKey(),
            'cod_niveles_seguridad_eri' => $nivel->getKey(),
            'fee_eri' => date('Y-m-d'),
            'met_eri' => 'INSPECCIÓN EN CAMPO',
        ]);

        $response->assertRedirect(route('rutas.show', $rutaB));
    }

    /**
     * Caso R: Tramo X no pertenece a RUT-C. Intentar utilizar RUT-C como contexto es rechazado con 404.
     */
    public function test_caso_r_contexto_de_ruta_no_asociada_es_rechazado(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $rutaC = Ruta::create(['ide_rut' => 'RUT-R-C', 'nom_rut' => 'RUTA RC', 'ori_rut' => 'OC', 'dst_rut' => 'DC', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);
        $tramoX = CalleTramo::create(['nom_ctr' => 'TRAMO X NO ASOCIADO', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $nivel = \App\Models\NivelSeguridad::first();

        $response = $this->post(route('tramos.evaluaciones.store', $tramoX), [
            'cod_rutas' => $rutaC->getKey(),
            'cod_niveles_seguridad_eri' => $nivel->getKey(),
            'fee_eri' => date('Y-m-d'),
        ]);

        $response->assertStatus(404);
    }

    /**
     * Caso S: Instalación y ejecución de migraciones en testing deja presentes los índices únicos uq_ruta_tramo y uq_ruta_orden.
     */
    public function test_caso_s_indices_unicos_presentes_en_esquema_testing(): void
    {
        $indexes = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'ruta_calles_tramos'
        ");

        $indexNames = array_column($indexes, 'INDEX_NAME');

        $this->assertContains('uq_ruta_tramo', $indexNames);
        $this->assertContains('uq_ruta_orden', $indexNames);
    }

    /**
     * Caso T: Desvincular tramo (detach) y volver a asociar Ruta A + Tramo X + orden 2 debe ejecutarse correctamente.
     */
    public function test_caso_t_desvinculacion_y_reasociacion_exitosa(): void
    {
        $rutaA = Ruta::create(['ide_rut' => 'RUT-T-A', 'nom_rut' => 'RUTA TA', 'ori_rut' => 'OTA', 'dst_rut' => 'DTA', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => 1]);
        $tramoX = CalleTramo::create(['nom_ctr' => 'TRAMO T', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        // 1. Asociar Ruta A + Tramo X + orden 2
        $rutaA->tramos()->attach($tramoX->getKey(), ['ord_rct' => 2]);
        $this->assertCount(1, $rutaA->fresh()->tramos);
        $this->assertEquals(2, $rutaA->tramos->first()->pivot->ord_rct);

        // 2. Desvincular (detach physical delete)
        $rutaA->tramos()->detach($tramoX->getKey());
        $this->assertCount(0, $rutaA->fresh()->tramos);

        // 3. Volver a asociar Ruta A + Tramo X + orden 2
        $rutaA->tramos()->attach($tramoX->getKey(), ['ord_rct' => 2]);
        $this->assertCount(1, $rutaA->fresh()->tramos);
        $this->assertEquals(2, $rutaA->tramos->first()->pivot->ord_rct);
    }

    /**
     * Caso U: Tramo pertenece a dos rutas y la petición omite cod_rutas → Debe rechazarse por contexto ambiguo (422).
     */
    public function test_caso_u_tramo_compartido_sin_cod_rutas_es_rechazado_por_ambiguedad(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $rutaA = Ruta::create(['ide_rut' => 'RUT-U-A', 'nom_rut' => 'RUTA UA', 'ori_rut' => 'OUA', 'dst_rut' => 'DUA', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);
        $rutaB = Ruta::create(['ide_rut' => 'RUT-U-B', 'nom_rut' => 'RUTA UB', 'ori_rut' => 'OUB', 'dst_rut' => 'DUB', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);
        $tramoX = CalleTramo::create(['nom_ctr' => 'TRAMO U COMPARTIDO', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $rutaA->tramos()->attach($tramoX->getKey(), ['ord_rct' => 1]);
        $rutaB->tramos()->attach($tramoX->getKey(), ['ord_rct' => 1]);

        $nivel = \App\Models\NivelSeguridad::first();

        // Omitir cod_rutas en la petición
        $response = $this->post(route('tramos.evaluaciones.store', $tramoX), [
            'cod_niveles_seguridad_eri' => $nivel->getKey(),
            'fee_eri' => date('Y-m-d'),
        ]);

        $response->assertStatus(422);
    }

    /**
     * Caso V: Tramo pertenece a una sola ruta y la petición omite cod_rutas → Fallback legacy funciona.
     */
    public function test_caso_v_tramo_unica_ruta_sin_cod_rutas_utiliza_fallback(): void
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $rutaV = Ruta::create(['ide_rut' => 'RUT-V-1', 'nom_rut' => 'RUTA V1', 'ori_rut' => 'OV', 'dst_rut' => 'DV', 'est_rut' => 'BORRADOR', 'cod_usuarios_rut_cre' => $user->getKey()]);
        $tramoY = CalleTramo::create(['nom_ctr' => 'TRAMO V ÚNICO', 'tip_ctr' => 'ACERA', 'lon_ctr' => 100]);

        $rutaV->tramos()->attach($tramoY->getKey(), ['ord_rct' => 1]);

        $nivel = \App\Models\NivelSeguridad::first();

        // Omitir cod_rutas en la petición
        $response = $this->post(route('tramos.evaluaciones.store', $tramoY), [
            'cod_niveles_seguridad_eri' => $nivel->getKey(),
            'fee_eri' => date('Y-m-d'),
        ]);

        $response->assertRedirect(route('rutas.show', $rutaV));
    }
}

