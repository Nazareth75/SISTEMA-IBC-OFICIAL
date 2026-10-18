<?php
namespace App\Http\Controllers;

use App\Models\{EventoBaston, Obstaculo, ReporteObstaculo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;

class EventoBastonController extends Controller
{
    public function index()
    {
        return view('rutas.eventos_baston', ['eventos'=>EventoBaston::latest()->paginate(10)]);
    }
    public function reportar(Request $request, EventoBaston $evento)
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(),403);
        $datos=$request->validate(['tipo_obstaculo'=>['required',Rule::in(array_keys(Obstaculo::TIPOS))],
            'prioridad'=>'required|integer|between:1,4']);
        abort_unless($evento->tipo==='DETECCION' && $evento->tramo_id,422,'El evento necesita un tramo asociado y detección.');
        DB::transaction(function() use ($evento,$datos) {
            $evento=EventoBaston::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if($evento->estado==='EVIDENCIA_REPORTADA') return;
            $obstaculo=Obstaculo::create(['cod_calles_tramos_obs'=>$evento->tramo_id,
                'tip_obs'=>$datos['tipo_obstaculo'],'des_obs'=>'Evidencia del bastón revisada por técnico. Evento '.$evento->getKey(),
                'est_obs'=>'REPORTADO','ori_obs'=>'TECNICO','cod_usuarios_obs'=>Auth::id()]);
            ReporteObstaculo::create(['codigo'=>'BASTON-'.$evento->getKey(),'tipo_obstaculo'=>$datos['tipo_obstaculo'],
                'descripcion'=>'Detección del bastón revisada. No determina automáticamente el tipo de obstáculo.',
                'latitud'=>$evento->latitud,'longitud'=>$evento->longitud,'metodo_deteccion'=>'BASTON',
                'prioridad'=>$datos['prioridad'],'nivel_riesgo'=>[1=>'BAJO',2=>'MEDIO',3=>'ALTO',4=>'CRITICO'][$datos['prioridad']],
                'estado'=>'REPORTADO','reportado_por'=>Auth::id(),'fecha_reporte'=>$evento->fecha_evento,
                'cod_obstaculos_rpo'=>$obstaculo->getKey()]);
            $evento->update(['estado'=>'EVIDENCIA_REPORTADA','datos'=>$evento->datos+['obstaculo_id'=>$obstaculo->getKey()]]);
        });
        return back()->with('success','EVIDENCIA REPORTADA SOBRE EL TRAMO EXISTENTE. RECALCULA ACO.');
    }
}
