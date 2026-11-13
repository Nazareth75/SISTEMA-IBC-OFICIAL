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
    public function test_evidencia_baston_revisada_invalida_ruta_y_sos_no_es_obstaculo(): void {
        $usuario=User::firstOrFail(); $this->actingAs($usuario);
        (new DemoLaPazSeeder())->run();
        $ruta=Ruta::where('ide_rut','DEMO-RUT-LP-OSM')->firstOrFail();
        $asistente=app(\App\Services\AsistenteRutaService::class);
        $asistente->obtener($ruta);
        $estado=app(\App\Services\Aco\ServicioEstadoRecomendacionAco::class);
        $tramo=$ruta->tramos->first();
        $cantidad=Obstaculo::count();
        $evento=\App\Models\EventoBaston::create(['usuario_id'=>$usuario->getKey(),
            'evento_uuid'=>(string)Str::uuid(),'tipo'=>'DETECCION','origen'=>'ESP32',
            'tramo_id'=>$tramo->getKey(),'fecha_evento'=>now(),'datos'=>[]]);
        $this->assertSame($cantidad,Obstaculo::count());
        $request=\Illuminate\Http\Request::create('/eventos-baston','POST',['tipo_obstaculo'=>'OBRA','prioridad'=>4]);
        $controlador=app(\App\Http\Controllers\EventoBastonController::class);
        $controlador->reportar($request,$evento);
        $this->assertSame($cantidad+1,Obstaculo::count());
        $this->assertTrue($estado->estaDesactualizada($ruta));
        $this->assertTrue($asistente->obtener($ruta->fresh())['success']);
        $this->assertFalse($estado->estaDesactualizada($ruta));
        $controlador->reportar($request,$evento->fresh());
        $this->assertSame($cantidad+1,Obstaculo::count());
        $reporte=ReporteObstaculo::where('codigo','BASTON-'.$evento->getKey())->firstOrFail();
        $reporte->update(['prioridad'=>1]);
        $this->assertTrue($estado->estaDesactualizada($ruta));
        $sos=\App\Models\EventoBaston::create(['usuario_id'=>$usuario->getKey(),
            'evento_uuid'=>(string)Str::uuid(),'tipo'=>'SOS','origen'=>'ESP32',
            'tramo_id'=>$tramo->getKey(),'fecha_evento'=>now(),'datos'=>[]]);
        try { $controlador->reportar($request,$sos); $this->fail('SOS no debe generar obstaculos.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(422,$e->getStatusCode()); }
        $this->assertSame($cantidad+1,Obstaculo::count());
    }

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
