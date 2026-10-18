<?php

namespace App\Http\Controllers;

use App\Models\{Ruta, PersonaNoVidente, TokenMovilidad};
use App\Services\AsistenteRutaService;
use Illuminate\Http\Request;

class MovilidadController extends Controller
{
    public function login(Request $request)
    {
        abort_unless(config('movilidad.identificacion_prototipo'),403,'Identificación de prototipo deshabilitada.');
        $datos=$request->validate(['ci'=>'required|string|max:30','nombre'=>'required|string|max:255']);
        $persona=app(\App\Services\IdentificacionMovil::class)->verificar($datos['ci'],$datos['nombre']);
        if(!$persona) return response()->json(['message'=>'No pudimos verificar tus datos. Solicita ayuda al IBC.'],422);
        $secreto=bin2hex(random_bytes(32));
        TokenMovilidad::create(['persona_id'=>$persona->getKey(),'token_hash'=>hash('sha256',$secreto),'expires_at'=>now()->addDays(30)]);
        return response()->json(['token'=>$secreto,'nombre'=>$persona->nom_pnv,'prototipo'=>true]);
    }

    public function sesion(Request $request) {
        return response()->json(['nombre'=>$request->attributes->get('afiliadoMovil')->nom_pnv]);
    }

    private function activo(Request $request): void
    {
        abort_unless($request->attributes->get('afiliadoMovil'),401);
    }

    private function permiteDemo(Request $request): bool
    {
        return $request->boolean('demostracion') && config('movilidad.permitir_demo') && app()->environment(['local','testing']);
    }

    public function index(Request $request)
    {
        $this->activo($request);
        $demo = $this->permiteDemo($request);
        $rutas = Ruta::activas()->where(function ($q) use ($demo) {
            $q->where(fn($p)=>$p->where('est_rut','PUBLICADA')->where('ide_rut','not like','DEMO-%'));
            if ($demo) $q->orWhere('ide_rut', 'DEMO-RUT-LP-OSM');
        })->orderBy('nom_rut')->paginate(10, ['cod_rutas','nom_rut','ori_rut','dst_rut','ide_rut','est_rut']);
        return response()->json($rutas);
    }

    public function show(Request $request, Ruta $ruta, AsistenteRutaService $asistente)
    {
        $this->activo($request);
        abort_unless((!$ruta->esDemo() && $ruta->est_rut === 'PUBLICADA') || ($ruta->ide_rut==='DEMO-RUT-LP-OSM' && $this->permiteDemo($request)), 403);
        $resultado = $asistente->obtener($ruta);
        return response()->json($resultado, $resultado['success'] ? 200 : 422);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('tokenMovilidad')->update(['revoked_at'=>now()]);
        return response()->noContent();
    }

    public function evento(Request $request, \App\Services\AsociadorGpsTramo $asociador)
    {
        $this->activo($request);
        $datos = $request->validate([
            'evento_uuid' => 'required|uuid', 'tipo' => 'required|in:SOS,DETECCION',
            'origen' => 'required|in:ESP32,FLUTTER', 'fecha_evento' => 'required|date|before_or_equal:now',
            'latitud' => 'nullable|required_with:longitud|numeric|between:-90,90',
            'longitud' => 'nullable|required_with:latitud|numeric|between:-180,180',
            'precision_m' => 'nullable|numeric|between:0,10000',
            'fuente_ubicacion' => 'nullable|in:BASTON,TELEFONO',
            'ruta_id' => 'nullable|integer', 'distancias_cm' => 'nullable|array|size:3',
            'distancias_cm.*' => 'numeric|between:-1,10000',
        ]);
        $ruta = isset($datos['ruta_id']) ? Ruta::findOrFail($datos['ruta_id']) : null;
        if ($ruta) abort_unless((!$ruta->esDemo() && $ruta->est_rut === 'PUBLICADA') || ($ruta->ide_rut==='DEMO-RUT-LP-OSM' && $this->permiteDemo($request)),403);
        $tramo = $ruta && isset($datos['latitud'],$datos['longitud'])
            ? $asociador->asociar($ruta, $datos['latitud'], $datos['longitud'], $datos['precision_m'] ?? null) : null;
        $evento = \Illuminate\Support\Facades\DB::transaction(function () use ($request,$datos,$tramo) {
            // Serializar reintentos del mismo usuario para no duplicar evidencia ni SOS.
            PersonaNoVidente::whereKey($request->attributes->get('afiliadoMovil')->getKey())->lockForUpdate()->firstOrFail();
            return \App\Models\EventoBaston::firstOrCreate([
                'persona_id' => $request->attributes->get('afiliadoMovil')->getKey(), 'evento_uuid' => $datos['evento_uuid'],
            ], [
                'tipo' => $datos['tipo'], 'origen' => $datos['origen'], 'fecha_evento' => $datos['fecha_evento'],
                'latitud' => $datos['latitud'] ?? null, 'longitud' => $datos['longitud'] ?? null,
                'precision_m' => $datos['precision_m'] ?? null, 'fuente_ubicacion' => $datos['fuente_ubicacion'] ?? null,
                'tramo_id' => $tramo?->getKey(), 'datos' => $datos,
            ]);
        });
        return response()->json(['registrado' => true,'evento_id' => $evento->getKey(),
            'tramo_id' => $evento->tramo_id,'estado' => $evento->estado,
            'aviso_familiar_enviado' => false], $evento->wasRecentlyCreated ? 201 : 200);
    }
}
