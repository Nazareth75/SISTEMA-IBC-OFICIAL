<?php
namespace Tests\Unit;
use App\Services\VistaPreviaMerced;
use Tests\TestCase;
class VistaPreviaMercedTest extends TestCase {
 public function test_preview_uses_complete_osm_paths_and_does_not_claim_evaluated_risk(): void {
  cache()->forget('previa-merced-v2');
  $d=app(VistaPreviaMerced::class)->obtener();
  $this->assertTrue($d['success']);$this->assertTrue($d['vista_previa']);
  $this->assertFalse($d['riesgo_evaluado']);$this->assertSame('SIN EVALUAR',$d['nivel_riesgo_estimado']);
  $this->assertGreaterThanOrEqual(2,count($d['caminos_candidatos']));
  $this->assertEqualsWithDelta(4537.35,$d['distancia_mas_corta'],0.1);
  $this->assertTrue(collect($d['caminos_candidatos'])->contains('mas_corta',true));
  $base=$d['caminos_candidatos'][0];
  foreach($d['caminos_candidatos'] as $c){
   $this->assertNull($c['riesgo']);$this->assertSame($base['nodos'][0],$c['nodos'][0]);
   $this->assertSame(end($base['nodos']),end($c['nodos']));
   $this->assertGreaterThanOrEqual(0,$c['refuerzo_relativo']);$this->assertLessThanOrEqual(1,$c['refuerzo_relativo']);
   foreach($c['geometria'] as $i=>$geo){
    $this->assertGreaterThanOrEqual(2,count($geo));
    if($i>0)$this->assertSame(end($c['geometria'][$i-1]),$geo[0]);
   }
  }
  $this->assertCount(count($d['tramos_seleccionados_ids']),$d['geometria_recomendada']);
  $this->assertNotEmpty($d['instrucciones']);
  $request=\Illuminate\Http\Request::create('/api/movilidad/rutas');
  $request->attributes->set('afiliadoMovil', new \App\Models\PersonaNoVidente());
  config(['movilidad.permitir_vista_previa'=>true]);
  $respuesta=app(\App\Http\Controllers\MovilidadController::class)->previaMerced($request);
  $this->assertSame(200,$respuesta->getStatusCode());
  $this->assertSame($d['geometria_recomendada'],$respuesta->getData(true)['geometria_recomendada']);
 }
}
