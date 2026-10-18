<?php
namespace Tests\Unit;

use App\Models\{Ruta,Obstaculo,ReporteObstaculo,User};
use App\Services\Aco\{ServicioRecomendacionRutaAco,ConfiguracionAco};
use Database\Seeders\DemoLaPazSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoLaPazDinamicaTest extends TestCase {
    use DatabaseTransactions;
    public function test_riesgo_cambia_camino_y_resolucion_restaura_sin_fijar_ganador(): void {
        $usuario=User::firstOrFail();$this->actingAs($usuario);
        (new DemoLaPazSeeder())->run();
        $ruta=Ruta::where('ide_rut','DEMO-RUT-LP-OSM')->firstOrFail();
        $motor=app(ServicioRecomendacionRutaAco::class);$cfg=new ConfiguracionAco(beta:1.0,ants:60,iterations:100,seed:42);
        $antes=$motor->recomendar($ruta,$cfg);
        $tramo=$ruta->tramos->firstWhere('nom_ctr','DEMO — LP 2 — Calle Murillo');
        $obs=Obstaculo::create(['cod_calles_tramos_obs'=>$tramo->getKey(),'tip_obs'=>'OBRA','des_obs'=>'[DEMO] Obstrucción simulada',
            'est_obs'=>'REPORTADO','ori_obs'=>'TECNICO','cod_usuarios_obs'=>$usuario->getKey()]);
        ReporteObstaculo::create(['codigo'=>'DEMO-TEST-'.Str::uuid(),'tipo_obstaculo'=>'OBRA','descripcion'=>'[DEMO] Prioridad alta simulada',
            'nivel_riesgo'=>'ALTO','prioridad'=>4,'estado'=>'REPORTADO','metodo_deteccion'=>'MANUAL',
            'cod_obstaculos_rpo'=>$obs->getKey(),'reportado_por'=>$usuario->getKey(),'fecha_reporte'=>now()]);
        $despues=$motor->recomendar($ruta->fresh(),$cfg);$obs->resolver();$resuelto=$motor->recomendar($ruta->fresh(),$cfg);
        $this->assertTrue($antes->success && $despues->success && $resuelto->success);
        $this->assertNotSame($antes->tramosSeleccionadosIds,$despues->tramosSeleccionadosIds);
        $this->assertSame($antes->tramosSeleccionadosIds,$resuelto->tramosSeleccionadosIds);
        $this->assertEqualsWithDelta($antes->costoTotal,$resuelto->costoTotal,0.00001);
        $salida=storage_path('framework/testing');if(!is_dir($salida))mkdir($salida,0775,true);
        file_put_contents($salida.'/demo-dinamica.json',json_encode(['antes'=>$antes->toArray(),'obstaculo'=>$despues->toArray(),'resuelto'=>$resuelto->toArray()],JSON_PRETTY_PRINT));
    }
}
