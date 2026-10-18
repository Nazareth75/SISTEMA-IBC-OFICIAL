<?php
namespace App\Http\Controllers;
use App\Models\Ruta;
use App\Services\AsistenteRutaService;
use Illuminate\Http\Request;
class NavegacionAccesibleController extends Controller
{
    public function index(Request $request) {
        abort_unless($request->user()->est_usu==='ACTIVO',403);
        $demo=$request->boolean('demostracion') && $request->user()->tieneRol('ADMINISTRADOR','TECNICO');
        $rutas=Ruta::activas()->where(function($q)use($demo){$q->where('est_rut','PUBLICADA');if($demo)$q->orWhere('ide_rut','like','DEMO-%');})
            ->orderBy('dst_rut')->get(['cod_rutas','nom_rut','ori_rut','dst_rut','ide_rut']);
        return view('rutas.navegacion',compact('rutas','demo'));
    }
    public function show(Request $request,Ruta $ruta,AsistenteRutaService $asistente) {
        abort_unless($request->user()->est_usu==='ACTIVO',403);
        abort_unless($ruta->est_rut==='PUBLICADA'||($ruta->esDemo()&&$request->boolean('demostracion')&&$request->user()->tieneRol('ADMINISTRADOR','TECNICO')),403);
        $datos=$asistente->obtener($ruta);
        return response()->json($datos,$datos['success']?200:422);
    }
}
