<?php

namespace Tests\Unit;

use App\Http\Controllers\MovilidadController;
use App\Models\CalleTramo;
use App\Models\Obstaculo;
use App\Models\PersonaNoVidente;
use App\Models\ReporteObstaculo;
use App\Models\Ruta;
use App\Models\User;
use App\Models\VerificacionObstaculo;
use App\Services\Aco\CalculadorRiesgoTramoAco;
use App\Services\Aco\ConstructorGrafoRuta;
use App\Services\Aco\ServicioConsensoVerificacionObstaculo;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use App\Services\AsistenteRutaService;
use Database\Seeders\DemoLaPazSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerificacionCasosAcoTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Caso 1: Ruta recién registrada sin evaluar.
     * Conserva su geometría registrada mientras no tenga evaluaciones.
     * Distingue «sin evaluar» de «evaluado sin obstáculos», sin inventar riesgo cero.
     */
    public function test_caso1_ruta_recien_registrada_sin_evaluar(): void
    {
        $usuario = User::firstOrFail();
        $this->actingAs($usuario);

        $ruta = Ruta::create([
            'ide_rut'              => 'RUT-NUEVA-TEST',
            'nom_rut'              => 'Ruta Nueva Sin Evaluar',
            'ori_rut'              => 'Punto Origen A',
            'dst_rut'              => 'Punto Destino B',
            'est_rut'              => 'BORRADOR',
            'cod_usuarios_rut_cre' => $usuario->getKey(),
            'geo_rut'              => [
                'distancia_metros' => 350.0,
                'tiempo_minutos'   => 5,
                'trayecto_coords'  => [
                    ['lat' => -16.5000, 'lng' => -68.1300],
                    ['lat' => -16.5020, 'lng' => -68.1320],
                ],
            ],
        ]);

        $tramo = CalleTramo::create([
            'nom_ctr' => 'Tramo Recién Registrado 1',
            'tip_ctr' => 'ACERA',
            'lon_ctr' => 350.0,
            'ord_ctr' => 1,
            'geo_ctr' => [
                ['lat' => -16.5000, 'lng' => -68.1300],
                ['lat' => -16.5020, 'lng' => -68.1320],
            ],
        ]);
        $ruta->tramos()->attach($tramo->getKey(), ['ord_rct' => 1]);

        $constructor = app(ConstructorGrafoRuta::class);
        $analisis = $constructor->analizar($ruta);

        $this->assertFalse($analisis->esOptimizable);
        $this->assertNotEmpty($analisis->tramosDetalle);

        $detalleTramo = $analisis->tramosDetalle[0];
        $this->assertSame('SIN_EVALUAR', $detalleTramo['estado_visual']);
        $this->assertNull($detalleTramo['riesgo_normalizado']);
        $this->assertSame(0, $detalleTramo['obstaculos_count']);

        // Conserva su geometría registrada
        $this->assertNotNull($ruta->fresh()->geo_rut);
        $this->assertCount(2, $ruta->fresh()->geo_rut['trayecto_coords']);

        // La API móvil restringe rutas no publicadas/desactualizadas
        $request = Request::create('/api/movilidad/rutas/' . $ruta->getKey());
        $request->attributes->set('afiliadoMovil', new PersonaNoVidente());
        try {
            app(MovilidadController::class)->show($request, $ruta, app(AsistenteRutaService::class));
            $this->fail('La API móvil no debe exponer rutas en BORRADOR sin publicar.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    /**
     * Caso 2: Obstáculo validado.
     * Actualiza el riesgo del tramo, disminuye el grosor en la web y recalcula la recomendación ACO.
     * Compara la respuesta de la web con la API móvil.
     */
    public function test_caso2_obstaculo_validado_actualiza_riesgo_grosor_y_recalcula_aco(): void
    {
        $usuario = User::firstOrFail();
        $this->actingAs($usuario);

        (new DemoLaPazSeeder())->run();
        $ruta = Ruta::where('ide_rut', 'DEMO-RUT-LP-OSM')->firstOrFail();

        $asistente = app(AsistenteRutaService::class);
        $estado = app(ServicioEstadoRecomendacionAco::class);
        $constructor = app(ConstructorGrafoRuta::class);
        $calcRiesgo = app(CalculadorRiesgoTramoAco::class);

        // Recomendación y análisis inicial
        $recInicial = $asistente->obtener($ruta->fresh());
        $this->assertTrue($recInicial['success']);

        $tramo = $ruta->tramos->firstWhere('nom_ctr', 'DEMO — LP 2 — Calle Murillo');
        $riesgoBase = $calcRiesgo->calcular($tramo);

        // Reportar y validar un obstáculo (Bache activo con prioridad alta)
        $obs = Obstaculo::create([
            'cod_calles_tramos_obs' => $tramo->getKey(),
            'tip_obs'               => 'BACHE',
            'des_obs'               => 'Bache profundo en cruce peatonal',
            'est_obs'               => 'ACTIVO',
            'ori_obs'               => 'TECNICO',
            'cod_usuarios_obs'      => $usuario->getKey(),
        ]);
        ReporteObstaculo::create([
            'cod_obstaculos_rpo' => $obs->getKey(),
            'codigo'             => 'REP-BACHE-' . uniqid(),
            'tipo_obstaculo'     => 'BACHE',
            'prioridad'          => 4,
            'nivel_riesgo'       => 'ALTO',
            'metodo_deteccion'   => 'MANUAL',
            'estado'             => 'ACTIVO',
            'origen'             => 'TECNICO',
            'reportado_por'      => $usuario->getKey(),
            'descripcion'        => 'Bache confirmado por inspección técnica',
        ]);

        // La ruta se invalida automáticamente para recálculo
        $this->assertTrue($estado->estaDesactualizada($ruta));

        // El riesgo del tramo se incrementa
        $riesgoConObstaculo = $calcRiesgo->calcular($tramo->fresh());
        $this->assertGreaterThan($riesgoBase, $riesgoConObstaculo);

        // Análisis visual: el tramo pasa a estado con obstáculos y menor grosor
        $analisis = $constructor->analizar($ruta->fresh());
        $detalle = collect($analisis->tramosDetalle)->firstWhere('id', $tramo->getKey());
        $this->assertSame('EVALUADO_CON_OBSTACULOS', $detalle['estado_visual']);
        $this->assertGreaterThan(0, $detalle['obstaculos_count']);

        // Grosor: menor riesgo = mayor grosor; mayor riesgo = menor grosor
        $grosorBase = MathRoundGrosor($riesgoBase);
        $grosorConObs = MathRoundGrosor($riesgoConObstaculo);
        $this->assertLessThan($grosorBase, $grosorConObs);

        // Recálculo ACO
        $recNueva = $asistente->obtener($ruta->fresh());
        $this->assertTrue($recNueva['success']);
        $this->assertFalse($estado->estaDesactualizada($ruta));

        // Comparar con la respuesta de la API móvil
        $request = Request::create('/api/movilidad/rutas/' . $ruta->getKey() . '?demostracion=1');
        $request->attributes->set('afiliadoMovil', new PersonaNoVidente());
        config(['movilidad.permitir_demo' => true]);

        $respuestaApi = app(MovilidadController::class)->show($request, $ruta->fresh(), $asistente);
        $this->assertSame(200, $respuestaApi->getStatusCode());
        $datosApi = $respuestaApi->getData(true);

        $this->assertSame($recNueva['geometria_recomendada'], $datosApi['geometria_recomendada']);
        $this->assertSame($recNueva['tramos_seleccionados_ids'], $datosApi['tramos_seleccionados_ids']);
    }

    /**
     * Caso 3: Obstáculo resuelto.
     * Voluntarios indican «YA_NO_ESTA», consenso comunitario, resolución autorizada.
     * El obstáculo se retira del cálculo, el grosor refleja el riesgo restante
     * y ACO tiene en cuenta la distancia entre alternativas de seguridad comparable.
     */
    public function test_caso3_obstaculo_resuelto_retira_del_calculo_restaura_grosor_y_aco_considera_distancia(): void
    {
        $usuario = User::firstOrFail();
        $this->actingAs($usuario);

        (new DemoLaPazSeeder())->run();
        $ruta = Ruta::where('ide_rut', 'DEMO-RUT-LP-OSM')->firstOrFail();

        $asistente = app(AsistenteRutaService::class);
        $estado = app(ServicioEstadoRecomendacionAco::class);
        $constructor = app(ConstructorGrafoRuta::class);
        $calcRiesgo = app(CalculadorRiesgoTramoAco::class);
        $servicioConsenso = app(ServicioConsensoVerificacionObstaculo::class);

        $tramo = $ruta->tramos->firstWhere('nom_ctr', 'DEMO — LP 2 — Calle Murillo');
        $riesgoBase = $calcRiesgo->calcular($tramo);

        // Simular presencia de 7 obstáculos en el tramo
        $obstaculos = [];
        for ($i = 1; $i <= 7; $i++) {
            $obs = Obstaculo::create([
                'cod_calles_tramos_obs' => $tramo->getKey(),
                'tip_obs'               => 'BACHE',
                'des_obs'               => "Obstáculo #{$i} en tramo",
                'est_obs'               => 'ACTIVO',
                'ori_obs'               => 'VOLUNTARIO',
                'cod_usuarios_obs'      => $usuario->getKey(),
            ]);
            ReporteObstaculo::create([
                'cod_obstaculos_rpo' => $obs->getKey(),
                'codigo'             => "REP-OBS-{$i}-" . uniqid(),
                'tipo_obstaculo'     => 'BACHE',
                'prioridad'          => 3,
                'nivel_riesgo'       => 'MEDIO',
                'metodo_deteccion'   => 'MANUAL',
                'estado'             => 'ACTIVO',
                'origen'             => 'VOLUNTARIO',
                'reportado_por'      => $usuario->getKey(),
                'descripcion'        => "Reporte #{$i}",
            ]);
            $obstaculos[] = $obs;
        }

        $riesgoCon7Obs = $calcRiesgo->calcular($tramo->fresh());
        $this->assertGreaterThan($riesgoBase, $riesgoCon7Obs);
        $this->assertLessThan(MathRoundGrosor($riesgoBase), MathRoundGrosor($riesgoCon7Obs));

        // Flujo de verificación colaborativa: voluntarios indican YA_NO_ESTA
        $voluntario1 = User::create([
            'ide_usu' => 'VOL-TEST-1', 'nom_usu' => 'Voluntario A', 'ape_usu' => 'IBC', 'cii_usu' => '9900001',
            'cor_usu' => 'vol1@ibc.test', 'con_usu' => bcrypt('password'), 'est_usu' => 'ACTIVO',
            'cod_roles_usu' => $usuario->cod_roles_usu,
        ]);
        $voluntario2 = User::create([
            'ide_usu' => 'VOL-TEST-2', 'nom_usu' => 'Voluntario B', 'ape_usu' => 'IBC', 'cii_usu' => '9900002',
            'cor_usu' => 'vol2@ibc.test', 'con_usu' => bcrypt('password'), 'est_usu' => 'ACTIVO',
            'cod_roles_usu' => $usuario->cod_roles_usu,
        ]);

        foreach ($obstaculos as $obs) {
            VerificacionObstaculo::create([
                'cod_obstaculo' => $obs->getKey(), 'cod_usuario' => $voluntario1->getKey(),
                'respuesta'     => 'YA_NO_ESTA', 'canal' => 'WEB',
            ]);
            VerificacionObstaculo::create([
                'cod_obstaculo' => $obs->getKey(), 'cod_usuario' => $voluntario2->getKey(),
                'respuesta'     => 'YA_NO_ESTA', 'canal' => 'WEB',
            ]);

            // Consenso alcanzado
            $consenso = $servicioConsenso->evaluarConsenso($obs->fresh());
            $this->assertTrue($consenso['es_candidato_resolucion']);

            // Resolución técnica autorizada
            $obs->resolver();
        }

        // Una vez resueltos todos los obstáculos, se retiran del cálculo
        $riesgoRestante = $calcRiesgo->calcular($tramo->fresh());
        $this->assertEqualsWithDelta($riesgoBase, $riesgoRestante, 0.0001);

        // Su grosor en el mapa refleja el menor riesgo restante
        $this->assertSame(MathRoundGrosor($riesgoBase), MathRoundGrosor($riesgoRestante));
        $this->assertGreaterThan(MathRoundGrosor($riesgoCon7Obs), MathRoundGrosor($riesgoRestante));

        // Estado visual pasa a EVALUADO_SIN_OBSTACULOS
        $analisis = $constructor->analizar($ruta->fresh());
        $detalle = collect($analisis->tramosDetalle)->firstWhere('id', $tramo->getKey());
        $this->assertSame('EVALUADO_SIN_OBSTACULOS', $detalle['estado_visual']);
        $this->assertSame(0, $detalle['obstaculos_count']);

        // Recalcular y verificar que ACO tiene en cuenta la distancia según configuración
        $recFinal = $asistente->obtener($ruta->fresh());
        $this->assertTrue($recFinal['success']);

        // Compara recomendación web con la respuesta de la API móvil
        $request = Request::create('/api/movilidad/rutas/' . $ruta->getKey() . '?demostracion=1');
        $request->attributes->set('afiliadoMovil', new PersonaNoVidente());
        config(['movilidad.permitir_demo' => true]);

        $respuestaApi = app(MovilidadController::class)->show($request, $ruta->fresh(), $asistente);
        $this->assertSame(200, $respuestaApi->getStatusCode());
        $datosApi = $respuestaApi->getData(true);

        $this->assertSame($recFinal['geometria_recomendada'], $datosApi['geometria_recomendada']);
        $this->assertSame($recFinal['tramos_seleccionados_ids'], $datosApi['tramos_seleccionados_ids']);
    }
}

/**
 * Función auxiliar idéntica a la fórmula de grosor en la vista web:
 * weight = Math.round(11 - (r * 7)); min 4
 */
function MathRoundGrosor(float $riesgo): int
{
    $r = max(0.0, min(1.0, $riesgo));
    $w = (int) round(11 - ($r * 7));
    return max(4, $w);
}
