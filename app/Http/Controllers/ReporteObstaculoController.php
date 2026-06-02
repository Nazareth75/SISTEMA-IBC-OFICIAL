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

        $reporte = ReporteObstaculo::create([
            'codigo' => $codigo,
            'tipo_obstaculo' => $request->tipo_obstaculo,
            'descripcion' => $request->descripcion,
            'direccion_referencia' => $request->direccion_referencia,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'foto' => $fotoPath,
            'nivel_riesgo' => $request->nivel_riesgo,
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
        $reporteObstaculo = ReporteObstaculo::findOrFail($id);
        
        if (!auth()->user()->puedeValidarObstaculos() && $reporteObstaculo->reportado_por !== auth()->id()) {
            abort(403, 'No tiene permiso para ver este reporte.');
        }

        return view('reportes_obstaculos.show', compact('reporteObstaculo'));
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

        return view('reportes_obstaculos.mapa_monitoreo', compact('reportes', 'alertasHoy', 'zonasCriticas'));
    }
}
