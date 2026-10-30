<?php
namespace Tests\Unit;
use App\Models\{Ruta,Obstaculo,User,PersonaNoVidente};
use App\Services\AsistenteRutaService;
use App\Services\Aco\{ServicioEstadoRecomendacionAco,CalculadorRiesgoTramoAco};
use App\Http\Controllers\MovilidadController;
use Database\Seeders\DemoLaPazSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;
class ActualizacionMovilTest extends TestCase {
 use DatabaseTransactions;
 public function test_resolucion_invalida_cache_y_recalcula_riesgo_y_geometria_compartida():void {
  $this->actingAs(User::firstOrFail());
  (new DemoLaPazSeeder())->run();
  $ruta=Ruta::where('ide_rut','DEMO-RUT-LP-OSM')->firstOrFail();
  $asistente=app(AsistenteRutaService::class);$estado=app(ServicioEstadoRecomendacionAco::class);
  $antes=$asistente->obtener($ruta->fresh());$this->assertTrue($antes['success']);
  $tramo=$ruta->tramos->firstWhere('nom_ctr','DEMO — LP 2 — Calle Murillo');
  $calc=app(CalculadorRiesgoTramoAco::class);$base=$calc->calcular($tramo);
  $obs=Obstaculo::create(['cod_calles_tramos_obs'=>$tramo->getKey(),'tip_obs'=>'OBRA','des_obs'=>'Prueba transaccional','est_obs'=>'ACTIVO','ori_obs'=>'TECNICO','cod_usuarios_obs'=>auth()->id()]);
  $this->assertTrue($estado->estaDesactualizada($ruta));$this->assertGreaterThan($base,$calc->calcular($tramo->fresh()));
  $asistente->obtener($ruta->fresh());$this->assertFalse($estado->estaDesactualizada($ruta));
  $obs->resolver();$this->assertTrue($estado->estaDesactualizada($ruta));
  $resuelto=$asistente->obtener($ruta->fresh());
  $this->assertFalse($estado->estaDesactualizada($ruta));
  $this->assertEqualsWithDelta($base,$calc->calcular($tramo->fresh()),0.00001);
  $this->assertSame($antes['tramos_seleccionados_ids'],$resuelto['tramos_seleccionados_ids']);
  $this->assertSame($antes['geometria_recomendada'],$resuelto['geometria_recomendada']);
  $this->assertSame($resuelto['geometria_recomendada'],$asistente->obtenerResultado($ruta->fresh())->toArray()['geometria_recomendada']);
 }
 public function test_api_permite_recalcular_publicada_desactualizada_pero_no_borrador():void {
  $request=Request::create('/api/movilidad/rutas/1');$request->attributes->set('afiliadoMovil',new PersonaNoVidente());
  $ruta=new Ruta(['ide_rut'=>'PRUEBA-PUBLICADA','nom_rut'=>'Ruta registrada','est_rut'=>'DESACTUALIZADA']);
  $asistente=\Mockery::mock(AsistenteRutaService::class);$asistente->shouldReceive('obtener')->once()->with($ruta)->andReturn(['success'=>true]);
  $this->assertSame(200,app(MovilidadController::class)->show($request,$ruta,$asistente)->getStatusCode());
  $ruta->est_rut='BORRADOR';
  try{app(MovilidadController::class)->show($request,$ruta,$asistente);$this->fail('No debe exponer borradores');}
  catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
 }
 public function test_vista_previa_independiente_no_se_expone_al_afiliado_normal():void {
  config(['movilidad.permitir_vista_previa'=>true]);
  $request=Request::create('/api/movilidad/rutas');$request->attributes->set('afiliadoMovil',new PersonaNoVidente());
  try{app(MovilidadController::class)->previaMerced($request);$this->fail('Vista previa requiere desarrollo explícito');}
  catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
 }
}
