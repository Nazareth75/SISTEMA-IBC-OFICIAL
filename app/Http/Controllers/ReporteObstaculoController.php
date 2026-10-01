<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReporteObstaculo;
class ReporteObstaculoController extends Controller
{
    public function index()
    {
        if (!auth()->user()->puedeValidarObstaculos()) {
            abort(403, 'No tiene permiso para ver todos los reportes.');
        }

        $reportes = ReporteObstaculo::with('reportadoPor', 'validadoPor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reportes_obstaculos.index', compact('reportes'));
    }

    public function create()
    {
        if (!auth()->user()->puedeReportarObstaculos()) {
            abort(403, 'No tiene permiso para crear reportes.');
        }

        return view('reportes_obstaculos.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->puedeReportarObstaculos()) {
            abort(403, 'No tiene permiso para crear reportes.');
        }

        $request->validate([
            'tipo_obstaculo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'direccion_referencia' => 'nullable|string|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'foto' => 'nullable|image|max:5120',
            'nivel_riesgo' => 'required|string|in:BAJO,MEDIO,ALTO,CRITICO',
            'metodo_deteccion' => 'nullable|string|in:MANUAL,BASTON,IA',
        ]);

        $prioridadMap = [
            'BAJO' => 1,
            'MEDIO' => 2,
            'ALTO' => 3,
            'CRITICO' => 4,
        ];

        // Generar código automático
        $lastReporte = ReporteObstaculo::orderBy('id', 'desc')->first();
        if ($lastReporte && preg_match('/^OBS-(\d+)$/', $lastReporte->codigo, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $codigo = 'OBS-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('reportes_obstaculos', 'public');
        }

        // Para esta fase, los reportes desde la web se registran siempre como MANUAL
        $metodoDeteccion = $request->input('metodo_deteccion') ?: 'MANUAL';
        if (!in_array($metodoDeteccion, ['MANUAL', 'BASTON', 'IA'])) {
            $metodoDeteccion = 'MANUAL';
        }

        $reporte = ReporteObstaculo::create([
            'codigo' => $codigo,
            'tipo_obstaculo' => $request->tipo_obstaculo,
            'descripcion' => $request->descripcion,
            'direccion_referencia' => $request->direccion_referencia,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'foto' => $fotoPath,
            'nivel_riesgo' => $request->nivel_riesgo,
            'metodo_deteccion' => $metodoDeteccion,
            'prioridad' => $prioridadMap[$request->nivel_riesgo],
            'estado' => 'REPORTADO',
            'reportado_por' => auth()->id(),
            'fecha_reporte' => now(),
        ]);

        return redirect()->route('reportes-obstaculos.mis-reportes')
            ->with('success', 'Reporte creado exitosamente con el código ' . $codigo);
    }

    public function show($id)
    {
        $reporteObstaculo = ReporteObstaculo::with(['reportadoPor', 'validadoPor', 'obstaculo.tramo.ruta'])
            ->findOrFail($id);
        
        if (!auth()->user()->puedeValidarObstaculos() && $reporteObstaculo->reportado_por !== auth()->id()) {
            abort(403, 'No tiene permiso para ver este reporte.');
        }

        $rutas = [];
        if (auth()->user()->puedeValidarObstaculos() && $reporteObstaculo->estado === 'ACTIVO' && !$reporteObstaculo->cod_obstaculos_rpo) {
            $rutas = \App\Models\Ruta::with(['tramos' => function($q) {
                $q->whereNull('fbj_ctr')->orderBy('ord_ctr');
            }])->whereNull('fbj_rut')->orderBy('nom_rut')->get();
        }

        return view('reportes_obstaculos.show', compact('reporteObstaculo', 'rutas'));
    }

    public function convertirEnObstaculo(Request $request, $id)
    {
        if (!auth()->user()->puedeValidarObstaculos() && !auth()->user()->puedeGestionarObstaculos()) {
            abort(403, 'No tiene permiso para convertir reportes en obstáculos.');
        }

        $reporte = ReporteObstaculo::findOrFail($id);

        if ($reporte->estado !== 'ACTIVO') {
            return redirect()->back()->with('error', 'Solo se pueden vincular a rutas los reportes que se encuentren en estado ACTIVO.');
        }

        if ($reporte->cod_obstaculos_rpo !== null) {
            return redirect()->back()->with('error', 'Este reporte ya se encuentra vinculado a un obstáculo existente.');
        }

        $request->validate([
            'cod_rutas' => 'required|exists:rutas,cod_rutas',
            'cod_calles_tramos' => 'required|exists:calles_tramos,cod_calles_tramos',
            'tip_obs' => 'required|in:' . implode(',', array_keys(\App\Models\Obstaculo::TIPOS)),
            'des_obs' => 'nullable|string|max:250',
        ], [
            'cod_rutas.required' => 'Debe seleccionar una ruta.',
            'cod_calles_tramos.required' => 'Debe seleccionar un tramo perteneciente a la ruta.',
            'tip_obs.required' => 'El tipo de obstáculo es obligatorio.',
            'tip_obs.in' => 'El tipo de obstáculo seleccionado no es válido.',
        ]);

        $ruta = \App\Models\Ruta::findOrFail($request->cod_rutas);
        $tramo = $ruta->tramos()->where('calles_tramos.cod_calles_tramos', $request->cod_calles_tramos)->first();
        if (!$tramo) {
            $tramo = \App\Models\CalleTramo::where('cod_calles_tramos', $request->cod_calles_tramos)
                ->where('cod_rutas_ctr', $ruta->cod_rutas)
                ->whereNull('fbj_ctr')
                ->first();
        }

        if (!$tramo) {
            return redirect()->back()->with('error', 'El tramo seleccionado no pertenece a la ruta especificada.');
        }

        $autor = $reporte->reportadoPor;
        $oriObs = 'TECNICO';
        if ($autor) {
            if ($autor->esVoluntario()) {
                $oriObs = 'VOLUNTARIO';
            } elseif (method_exists($autor, 'esAfiliado') && $autor->esAfiliado()) {
                $oriObs = 'AFILIADO';
            }
        }

        $descripcion = $request->input('des_obs') ?: $reporte->descripcion;
        $descripcion = strtoupper(substr(trim($descripcion), 0, 250));

        \Illuminate\Support\Facades\DB::transaction(function () use ($reporte, $tramo, $request, $oriObs, $descripcion) {
            $obstaculo = \App\Models\Obstaculo::create([
                'cod_calles_tramos_obs' => $tramo->cod_calles_tramos,
                'tip_obs' => $request->tip_obs,
                'des_obs' => $descripcion,
                'est_obs' => 'ACTIVO',
                'ori_obs' => $oriObs,
                'cod_usuarios_obs' => auth()->id(),
                'fot_obs' => $reporte->foto,
            ]);

            $reporte->update([
                'cod_obstaculos_rpo' => $obstaculo->cod_obstaculos,
            ]);
        });

        return redirect()->route('reportes-obstaculos.show', $reporte->id)
            ->with('success', 'Obstáculo creado y vinculado correctamente al tramo de navegación.');
    }

    public function validar(Request $request, $id)
    {
        if (!auth()->user()->puedeValidarObstaculos()) {
            abort(403, 'No tiene permiso para validar reportes.');
        }

        $reporteObstaculo = ReporteObstaculo::findOrFail($id);

        $request->validate([
            'observacion_validacion' => 'nullable|string',
        ]);

        $reporteObstaculo->update([
            'estado' => 'ACTIVO',
            'validado_por' => auth()->id(),
            'fecha_validacion' => now(),
            'observacion_validacion' => $request->observacion_validacion,
        ]);

        return redirect()->back()->with('success', 'Reporte validado correctamente.');
    }

    public function descartar($id)
    {
        if (!auth()->user()->puedeValidarObstaculos()) {
            abort(403, 'No tiene permiso para descartar reportes.');
        }

        $reporteObstaculo = ReporteObstaculo::findOrFail($id);

        $reporteObstaculo->update([
            'estado' => 'DESCARTADO',
            'validado_por' => auth()->id(),
            'fecha_validacion' => now(),
        ]);

        return redirect()->back()->with('success', 'Reporte descartado.');
    }

    public function resolver($id)
    {
        if (!auth()->user()->puedeValidarObstaculos()) {
            abort(403, 'No tiene permiso para resolver reportes.');
        }

        $reporteObstaculo = ReporteObstaculo::findOrFail($id);

        $reporteObstaculo->update([
            'estado' => 'RESUELTO',
            'fecha_resolucion' => now(),
        ]);

        return redirect()->back()->with('success', 'Reporte marcado como resuelto.');
    }

    public function misReportes()
    {
        if (!auth()->user()->puedeReportarObstaculos()) {
            abort(403, 'No tiene permiso para ver sus reportes.');
        }

        $query = ReporteObstaculo::where('reportado_por', auth()->id());

        $reportes = (clone $query)->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => (clone $query)->count(),
            'pendientes' => (clone $query)->where('estado', 'REPORTADO')->count(),
            'criticos' => (clone $query)->where('nivel_riesgo', 'CRITICO')->count(),
            'solucionados' => (clone $query)->where('estado', 'RESUELTO')->count(),
            'hoy' => (clone $query)->whereDate('created_at', today())->count(),
        ];

        return view('reportes_obstaculos.mis_reportes', compact('reportes', 'stats'));
    }

    public function mapaMonitoreo()
    {
        $reportes = ReporteObstaculo::with('reportadoPor', 'validadoPor')
            ->orderBy('created_at', 'desc')
            ->get();

        $alertasHoy = ReporteObstaculo::whereDate('created_at', today())->count();
        $zonasCriticas = ReporteObstaculo::where('nivel_riesgo', 'CRITICO')
            ->whereIn('estado', ['REPORTADO', 'ACTIVO'])
            ->count();

        $sinUbicacionCount = ReporteObstaculo::where(function ($q) {
            $q->whereNull('latitud')->orWhereNull('longitud');
        })->count();

        return view('reportes_obstaculos.mapa_monitoreo', compact('reportes', 'alertasHoy', 'zonasCriticas', 'sinUbicacionCount'));
    }
}
