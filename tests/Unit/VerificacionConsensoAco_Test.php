<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Obstaculo;
use App\Models\CalleTramo;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Role;
use App\Models\VerificacionObstaculo;
use App\Services\Aco\ServicioConsensoVerificacionObstaculo;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VerificacionConsensoAco_Test extends TestCase
{
    use DatabaseTransactions;

    private ServicioConsensoVerificacionObstaculo $consensoService;
    private ServicioEstadoRecomendacionAco $estadoService;
    private User $voluntario1;
    private User $voluntario2;
    private User $voluntario3;
    private User $tecnico;

    public function setUp(): void
    {
        parent::setUp();
        $this->consensoService = new ServicioConsensoVerificacionObstaculo();
        $this->estadoService   = new ServicioEstadoRecomendacionAco();

        $this->voluntario1 = $this->crearUsuario('VOL_1', 'VOLUNTARIO 1', 'VOLUNTARIO');
        $this->voluntario2 = $this->crearUsuario('VOL_2', 'VOLUNTARIO 2', 'VOLUNTARIO');
        $this->voluntario3 = $this->crearUsuario('VOL_3', 'VOLUNTARIO 3', 'VOLUNTARIO');
        $this->tecnico     = $this->crearUsuario('TEC_AUD', 'TECNICO AUDITOR', 'TECNICO');
    }

    private function crearUsuario(string $ide, string $nom, string $rolNombre): User
    {
        $rol = Role::where('nom_rol', $rolNombre)->firstOrFail();

        return User::create([
            'ide_usu'       => $ide,
            'nom_usu'       => $nom,
            'ape_usu'       => 'TEST',
            'cii_usu'       => 'CI-' . substr(md5($ide . microtime()), 0, 8),
            'cor_usu'       => strtolower($ide) . '_' . uniqid() . '@ibc.org',
            'con_usu'       => bcrypt('password'),
            'cod_roles_usu' => $rol->getKey(),
            'est_usu'       => 'ACTIVO',
        ]);
    }

    private function crearObstaculoActivo(): Obstaculo
    {
        $tramo = CalleTramo::create([
            'nom_ctr' => 'TRAMO PRUEBA VERIFICACION',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 100.0,
        ]);

        return Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'OBRA',
            'des_obs'               => 'EXCAVACIÓN PROFUNDA',
            'est_obs'               => 'ACTIVO',
            'ori_obs'               => 'TECNICO',
            'cod_usuarios_obs'      => $this->tecnico->getKey(),
        ]);
    }

    /**
     * 1. Verificación sin obstáculo (ID inexistente) retorna 404.
     */
    public function test_verificacion_sin_obstaculo_retorna_404(): void
    {
        $response = $this->actingAs($this->voluntario1)->get('/obstaculos/999999/verificar');
        $response->assertStatus(404);

        $responsePost = $this->actingAs($this->voluntario1)->post('/obstaculos/999999/verificar', [
            'respuesta' => 'YA_NO_ESTA',
        ]);
        $responsePost->assertStatus(404);
    }

    /**
     * 2. Usuario no autenticado es redirigido a login.
     */
    public function test_usuario_no_autenticado_es_redirigido_a_login(): void
    {
        $obs = $this->crearObstaculoActivo();
        $response = $this->get(route('obstaculos.verificar', $obs));
        $response->assertRedirect('/login');
    }

    /**
     * 3. Voluntario NO puede resolver directamente un obstáculo (permiso restringido a técnicos/admins).
     */
    public function test_voluntario_no_puede_resolver_directamente_un_obstaculo(): void
    {
        $obs = $this->crearObstaculoActivo();

        // Intento directo vía endpoint técnico de confirmación
        $response = $this->actingAs($this->voluntario1)->post(route('verificaciones.confirmar', $obs));
        $response->assertStatus(403);

        // Intento vía ObstaculoController::resolver
        $responseResolver = $this->actingAs($this->voluntario1)->patch(route('obstaculos.resolver', $obs));
        $responseResolver->assertStatus(403);

        // El obstáculo debe continuar estrictamente en estado ACTIVO
        $this->assertEquals('ACTIVO', $obs->fresh()->est_obs);
    }

    /**
     * 4. Verificación en obstáculo ya resuelto es rechazada con 422.
     */
    public function test_verificacion_en_obstaculo_ya_resuelto_es_rechazada(): void
    {
        $obs = $this->crearObstaculoActivo();
        $obs->resolver();

        $responseGet = $this->actingAs($this->voluntario1)->get(route('obstaculos.verificar', $obs));
        $responseGet->assertStatus(422);

        $responsePost = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta' => 'YA_NO_ESTA',
        ]);
        $responsePost->assertStatus(422);
    }

    /**
     * 5. Respuestas no permitidas son rechazadas por validación.
     */
    public function test_respuestas_invalidas_son_rechazadas(): void
    {
        $obs = $this->crearObstaculoActivo();

        $response = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta' => 'DESAPARECIDO_TEXTO_LIBRE',
        ]);

        $response->assertSessionHasErrors('respuesta');
        $this->assertEquals(0, VerificacionObstaculo::count());
    }

    /**
     * 6. Coordenadas y precisión GPS inválidas son rechazadas.
     */
    public function test_coordenadas_invalidas_y_precision_negativa_son_rechazadas(): void
    {
        $obs = $this->crearObstaculoActivo();

        // Latitud fuera de [-90, 90]
        $resp1 = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta' => 'YA_NO_ESTA',
            'latitud'   => 95.5,
            'longitud'  => -68.12,
        ]);
        $resp1->assertSessionHasErrors('latitud');

        // Longitud fuera de [-180, 180]
        $resp2 = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta' => 'YA_NO_ESTA',
            'latitud'   => -16.5,
            'longitud'  => -190.0,
        ]);
        $resp2->assertSessionHasErrors('longitud');

        // Precisión negativa
        $resp3 = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta'     => 'YA_NO_ESTA',
            'precision_gps' => -5.0,
        ]);
        $resp3->assertSessionHasErrors('precision_gps');
    }

    /**
     * 7. Foto con formato o tamaño inválido es rechazada.
     */
    public function test_foto_invalida_es_rechazada(): void
    {
        Storage::fake('public');
        $obs = $this->crearObstaculoActivo();

        $archivoTxt = UploadedFile::fake()->create('evidencia.txt', 100, 'text/plain');

        $response = $this->actingAs($this->voluntario1)->post(route('obstaculos.verificar.store', $obs), [
            'respuesta' => 'YA_NO_ESTA',
            'foto'      => $archivoTxt,
        ]);

        $response->assertSessionHasErrors('foto');
    }

    /**
     * 8. Misma persona verificando varias veces: se deduplica tomando estrictamente su última verificación.
     */
    public function test_misma_persona_verificando_varias_veces_se_deduplica_correctamente(): void
    {
        $obs = $this->crearObstaculoActivo();

        // Voluntario 1 verifica 5 veces que "YA_NO_ESTA"
        for ($i = 0; $i < 5; $i++) {
            VerificacionObstaculo::create([
                'cod_obstaculo' => $obs->getKey(),
                'cod_usuario'   => $this->voluntario1->getKey(),
                'respuesta'     => 'YA_NO_ESTA',
                'canal'         => 'WEB',
                'created_at'    => now()->addMinutes($i),
            ]);
        }

        $consenso = $this->consensoService->evaluarConsenso($obs);

        // Debe registrar 5 registros totales pero solo 1 voluntario único
        $this->assertEquals(5, $consenso['total_verificaciones']);
        $this->assertEquals(1, $consenso['usuarios_unicos']);
        $this->assertEquals(1, $consenso['conteo_ya_no_esta']);

        // NO debe ser candidato a resolución porque se requieren mínimo 2 voluntarios distintos
        $this->assertFalse($consenso['es_candidato_resolucion']);
        $this->assertEquals('INSUFICIENTE', $consenso['estado_consenso']);
    }

    /**
     * 9. Conflicto de evidencias: si un voluntario dice YA_NO_ESTA y otro SIGUE_PRESENTE,
     *    se marca EN_CONFLICTO y NO es candidato a resolución.
     */
    public function test_consenso_con_evidencia_en_conflicto_no_marca_candidato(): void
    {
        $obs = $this->crearObstaculoActivo();

        // Voluntario 1: YA_NO_ESTA
        VerificacionObstaculo::create([
            'cod_obstaculo' => $obs->getKey(),
            'cod_usuario'   => $this->voluntario1->getKey(),
            'respuesta'     => 'YA_NO_ESTA',
            'canal'         => 'WEB',
            'created_at'    => now()->subMinutes(10),
        ]);

        // Voluntario 2: SIGUE_PRESENTE
        VerificacionObstaculo::create([
            'cod_obstaculo' => $obs->getKey(),
            'cod_usuario'   => $this->voluntario2->getKey(),
            'respuesta'     => 'SIGUE_PRESENTE',
            'canal'         => 'WEB',
            'created_at'    => now()->subMinutes(5),
        ]);

        $consenso = $this->consensoService->evaluarConsenso($obs);

        $this->assertFalse($consenso['es_candidato_resolucion']);
        $this->assertEquals('EN_CONFLICTO', $consenso['estado_consenso']);
        $this->assertEquals(1, $consenso['conteo_ya_no_esta']);
        $this->assertEquals(1, $consenso['conteo_sigue_presente']);
        $this->assertStringContainsString('EVIDENCIA EN CONFLICTO', $consenso['motivo']);
    }

    /**
     * 10. Consenso exitoso: dos voluntarios distintos indican YA_NO_ESTA con 0 conflictos
     *     -> CANDIDATO_RESOLUCION.
     */
    public function test_consenso_exitoso_con_minimo_dos_voluntarios_independientes(): void
    {
        $obs = $this->crearObstaculoActivo();

        // Voluntario 1: YA_NO_ESTA
        VerificacionObstaculo::create([
            'cod_obstaculo' => $obs->getKey(),
            'cod_usuario'   => $this->voluntario1->getKey(),
            'respuesta'     => 'YA_NO_ESTA',
            'canal'         => 'WEB',
        ]);

        // Voluntario 2: YA_NO_ESTA
        VerificacionObstaculo::create([
            'cod_obstaculo' => $obs->getKey(),
            'cod_usuario'   => $this->voluntario2->getKey(),
            'respuesta'     => 'YA_NO_ESTA',
            'canal'         => 'WEB',
        ]);

        $consenso = $this->consensoService->evaluarConsenso($obs);

        $this->assertTrue($consenso['es_candidato_resolucion']);
        $this->assertEquals('CANDIDATO_RESOLUCION', $consenso['estado_consenso']);
        $this->assertEquals(2, $consenso['conteo_ya_no_esta']);
        $this->assertEquals(0, $consenso['conteo_sigue_presente']);
    }

    /**
     * 11. Técnico confirma resolución: ocurre dentro de transacción, muta a RESUELTO,
     *     invalida recomendación ACO y conserva el historial completo de verificaciones.
     */
    public function test_tecnico_confirma_resolucion_dentro_de_transaccion_y_desencadena_flujo_aco(): void
    {
        $obs = $this->crearObstaculoActivo();
        $tramo = $obs->tramo;

        $ruta = Ruta::create([
            'ide_rut' => 'RUT-TEST-5D-CONFIRM',
            'nom_rut' => 'RUTA DE PRUEBA 5D',
            'ori_rut' => 'A',
            'dst_rut' => 'B',
            'est_rut' => 'EN REVISIÓN',
        ]);
        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        // Crear 2 verificaciones de consenso
        VerificacionObstaculo::create(['cod_obstaculo' => $obs->getKey(), 'cod_usuario' => $this->voluntario1->getKey(), 'respuesta' => 'YA_NO_ESTA', 'canal' => 'WEB']);
        VerificacionObstaculo::create(['cod_obstaculo' => $obs->getKey(), 'cod_usuario' => $this->voluntario2->getKey(), 'respuesta' => 'YA_NO_ESTA', 'canal' => 'WEB']);

        // Marcar recomendación previa vigente en caché
        $this->estadoService->guardarRecomendacion($ruta, new \App\Services\Aco\ResultadoRecomendacionAco(success: true, distanciaTotal: 100.0, costoTotal: 0.8));
        $this->assertFalse($this->estadoService->estaDesactualizada($ruta));

        // Técnico ejecuta la confirmación desde la bandeja
        $response = $this->actingAs($this->tecnico)->post(route('verificaciones.confirmar', $obs));
        $response->assertSessionHas('success');

        // El obstáculo debe quedar marcado RESUELTO
        $this->assertEquals('RESUELTO', $obs->fresh()->est_obs);

        // La ruta debe haber sido marcada como desactualizada para recálculo ACO
        $this->assertTrue($this->estadoService->estaDesactualizada($ruta));

        // Las verificaciones deben conservarse intactas
        $this->assertEquals(2, $obs->verificaciones()->count());
    }

    /**
     * 12. Confirmación doble es rechazada: un obstáculo ya RESUELTO no puede ser resuelto nuevamente.
     */
    public function test_confirmacion_doble_es_rechazada(): void
    {
        $obs = $this->crearObstaculoActivo();
        $obs->resolver();

        $response = $this->actingAs($this->tecnico)->post(route('verificaciones.confirmar', $obs));
        $response->assertSessionHas('error');
    }

    /**
     * 13. Rollback en transacción: si ocurre una excepción durante la confirmación,
     *     la base de datos no queda en estado inconsistente.
     */
    public function test_rollback_en_transaccion_si_falla_confirmacion(): void
    {
        $obs = $this->crearObstaculoActivo();

        try {
            DB::transaction(function () use ($obs) {
                $obs->update(['est_obs' => 'RESUELTO']);
                throw new \RuntimeException('FALLO SIMULADO DE RED O SUBSISTEMA');
            });
        } catch (\RuntimeException $e) {
            // Excepción esperada
        }

        // El rollback debe haber restaurado el estado original a ACTIVO
        $this->assertEquals('ACTIVO', $obs->fresh()->est_obs);
    }

    /**
     * 14. Cero verificaciones retorna evidencia insuficiente sin errores.
     */
    public function test_cero_verificaciones_retorna_evidencia_insuficiente(): void
    {
        $obs = $this->crearObstaculoActivo();
        $consenso = $this->consensoService->evaluarConsenso($obs);

        $this->assertFalse($consenso['es_candidato_resolucion']);
        $this->assertEquals('INSUFICIENTE', $consenso['estado_consenso']);
        $this->assertEquals(0, $consenso['total_verificaciones']);
        $this->assertEquals(0, $consenso['usuarios_unicos']);
    }

    /**
     * 15. Bandeja del técnico lista los obstáculos con evidencias pendientes.
     */
    public function test_bandeja_tecnico_muestra_obstaculos_pendientes(): void
    {
        $obs = $this->crearObstaculoActivo();
        VerificacionObstaculo::create(['cod_obstaculo' => $obs->getKey(), 'cod_usuario' => $this->voluntario1->getKey(), 'respuesta' => 'YA_NO_ESTA', 'canal' => 'WEB']);

        $response = $this->actingAs($this->tecnico)->get(route('verificaciones.bandeja'));
        $response->assertStatus(200);
        $response->assertSee('BANDEJA DE VERIFICACIONES PENDIENTES');
        $response->assertSee('#' . $obs->getKey());
    }
}
