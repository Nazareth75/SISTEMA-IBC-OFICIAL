<?php

namespace Tests\Unit;

use App\Models\{Role, Ruta, User};
use App\Services\AsistenteRutaService;
use App\Services\Aco\{ConstructorGrafoRuta, ResultadoRecomendacionAco};
use Database\Seeders\DemoLaPazSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AsistenteRutaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_geometria_osm_forma_alternativas_y_web_api_comparten_recomendacion(): void
    {
        (new DemoLaPazSeeder())->run();
        $ruta = Ruta::where('ide_rut', 'DEMO-RUT-LP-OSM')->firstOrFail();
        $analisis = app(ConstructorGrafoRuta::class)->analizar($ruta);
        $this->assertTrue($analisis->esOptimizable, implode(' ', $analisis->motivosBloqueo));
        $this->assertGreaterThanOrEqual(2, $analisis->cantidadAlternativas);
        $asistente = app(AsistenteRutaService::class);
        $primera = $asistente->obtener($ruta);
        $this->assertTrue($primera['success']);
        $caminos=$primera['caminos_candidatos'];
        $this->assertCount($analisis->cantidadAlternativas,$caminos);
        $recomendada=collect($caminos)->firstWhere('recomendada',true);
        $this->assertSame($primera['geometria_recomendada'],$recomendada['geometria']);
        $this->assertSame($primera['tramos_seleccionados_ids'],$recomendada['tramos_ids']);
        foreach($caminos as $camino) {
            $this->assertSame($analisis->nodoOrigenId,$camino['nodos'][0]);
            $this->assertSame($analisis->nodoDestinoId,end($camino['nodos']));
            $this->assertGreaterThanOrEqual(0,$camino['refuerzo_relativo']);
            $this->assertLessThanOrEqual(1,$camino['refuerzo_relativo']);
            foreach($camino['tramos_ids'] as $i=>$id) {
                $this->assertSame($ruta->tramos->firstWhere('cod_calles_tramos',$id)->geo_ctr,$camino['geometria'][$i]);
            }
        }
        $segunda = $asistente->obtener($ruta);
        $this->assertSame($primera, $segunda);
        $this->assertStringContainsString('Datos simulados', $primera['resumen_audio']);
        $this->assertSame($primera['tramos_seleccionados_ids'], $asistente->obtenerResultado($ruta)->tramosSeleccionadosIds);
        $rol = Role::firstOrCreate(['nom_rol' => 'ADMINISTRADOR_NO_VIDENTE'], ['act_rol' => true]);
        $usuario = User::create(['ide_usu' => 'TEST-ASISTENTE', 'nom_usu' => 'TEST', 'ape_usu' => 'ASISTENTE',
            'cii_usu' => 'TEST-ASISTENTE', 'cor_usu' => 'asistente@example.test', 'con_usu' => bcrypt('test'),
            'cod_roles_usu' => $rol->getKey(), 'est_usu' => 'ACTIVO']);
        $this->actingAs($usuario)
            ->getJson(route('rutas.asistente', $ruta))->assertOk()
            ->assertJsonPath('nodos_seleccionados', $primera['nodos_seleccionados']);
        $this->get(route('rutas.index'))->assertOk()->assertSee('INICIAR ASISTENTE');
        foreach (['ADMINISTRADOR' => false, 'ADMINISTRADOR_NO_VIDENTE' => true] as $nombreRol => $visible) {
            $rolPrueba = Role::firstOrCreate(['nom_rol' => $nombreRol], ['act_rol' => true]);
            $usuario->cod_roles_usu = $rolPrueba->getKey();
            $usuario->save();
            $usuario->unsetRelation('role');
            $this->actingAs($usuario);
            foreach (['rutas.index', 'rutas.show', 'rutas.edit'] as $pagina) {
                $respuesta = $this->get(route($pagina, $pagina === 'rutas.index' ? [] : $ruta))->assertOk();
                if ($visible) {
                    $respuesta->assertSee('data-asistente-ruta', false)->assertSee('data-detener-asistente', false);
                } else {
                    $respuesta->assertDontSee('data-asistente-ruta', false)->assertDontSee('data-detener-asistente', false)
                        ->assertDontSee('data-leer-rutas', false)->assertDontSee('js/asistente-rutas.js', false);
                }
            }
            $this->get(route('rutas.index'))->assertSee('VER EVENTOS Y SOS DEL BASTÓN')->assertSee('mapa-catalogo-rutas');
            $this->get(route('rutas.edit', $ruta))->assertSee('VER CAMINOS ACO Y TRAMOS GUARDADOS');
        }
        $salida=storage_path('framework/testing');
        if(!is_dir($salida))mkdir($salida,0775,true);
        file_put_contents($salida.'/preview-rutas.html',$this->get(route('rutas.index'))->assertOk()->getContent());
        file_put_contents($salida.'/preview-detalle.html',$this->get(route('rutas.show',$ruta))->assertOk()->getContent());
        file_put_contents($salida.'/preview-editar.html',$this->get(route('rutas.edit',$ruta))->assertOk()->getContent());
        file_put_contents($salida.'/preview-asistente.json',json_encode($primera));
        file_put_contents($salida.'/preview-eventos.html',$this->get(route('rutas.eventos'))->assertOk()->getContent());
        $this->get(route('navegacion.index',['demostracion'=>1]))->assertOk()->assertSee('¿A DÓNDE QUIERES IR?');
        $this->getJson(route('navegacion.ruta',[$ruta,'demostracion'=>1]))->assertOk()->assertJsonPath('geometria_recomendada',$primera['geometria_recomendada']);
    }

    public function test_dto_conserva_metricas_en_cache(): void
    {
        $original = new ResultadoRecomendacionAco(success: true, m7: 21.4, m8: 12.5, bestFoundIteration: 3,
            tramosSeleccionadosIds: [4, 7], geometriaRecomendada: [[['lat' => -16.5, 'lng' => -68.13]]]);
        $this->assertSame($original->toArray(), ResultadoRecomendacionAco::desdeArray($original->toArray())->toArray());
    }
}
