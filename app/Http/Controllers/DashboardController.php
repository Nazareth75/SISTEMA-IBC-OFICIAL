<?php

namespace App\Http\Controllers;

use App\Models\PersonaNoVidente;
use App\Models\Afiliacion;
use App\Models\BeneficioSocial;
use App\Models\AsignacionBeneficio;
use App\Models\TramiteInstitucion;
use App\Models\InstitucionExterna;
use App\Models\AccesoSalud;
use App\Models\Capacitacion;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function estadisticas()
    {
        try {
            $estadisticas = [
                'kpis' => [
                    'totalPersonas' => PersonaNoVidente::whereNull('fbj_pnv')->count(),
                    'personasActivas' => PersonaNoVidente::where('est_pnv', 'ACTIVO')->whereNull('fbj_pnv')->count(),
                    'afiliacionesActivas' => Afiliacion::where('est_afi', 'ACTIVA')->count(),
                    'afiliacionesPendientes' => Afiliacion::where('est_afi', 'PENDIENTE')->count(),
                    'beneficiosAsignados' => AsignacionBeneficio::count(),
                    'tramitesPendientes' => TramiteInstitucion::where('est_tin', 'PENDIENTE')->count(),
                    'institucionesExternas' => InstitucionExterna::where('act_iex', true)->count(),
                    'capacitacionesActivas' => Capacitacion::where('est_cap', 'ACTIVA')->count(),
                    'rutasPublicadas' => Ruta::where('est_rut', 'PUBLICADA')->count(),
                ],
                'distribucionPorEstado' => $this->distribucionPorEstado(),
                'generoPersonas' => $this->generoPersonas(),
                'gradoDiscapacidad' => $this->gradoDiscapacidad(),
                'afiliacionesPorTipo' => ['labels' => [], 'series' => []],
                'beneficiosMasAsignados' => ['labels' => [], 'series' => []],
                'tramitesPorEstado' => ['labels' => [], 'series' => []],
                'registrosPorMes' => ['labels' => [], 'series' => []],
                'distribucionPorCiudad' => $this->distribucionPorCiudad(),
                'ultimasPersonas' => $this->ultimasPersonas(),
                'ultimasAfiliaciones' => $this->ultimasAfiliaciones(),
            ];

            return response()->json($estadisticas);
        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    private function distribucionPorEstado()
    {
        try {
            $estados = PersonaNoVidente::whereNull('fbj_pnv')
                ->groupBy('est_pnv')
                ->selectRaw('est_pnv, count(*) as total')
                ->get();

            return [
                'labels' => $estados->pluck('est_pnv')->toArray(),
                'series' => $estados->pluck('total')->toArray(),
                'colors' => ['#2d6a27', '#f0b429', '#6b7280', '#dc2626', '#3b82f6'],
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'series' => [], 'colors' => []];
        }
    }

    private function generoPersonas()
    {
        try {
            $generos = PersonaNoVidente::whereNull('fbj_pnv')
                ->groupBy('gen_pnv')
                ->selectRaw('gen_pnv, count(*) as total')
                ->get();

            return [
                'labels' => $generos->pluck('gen_pnv')->toArray(),
                'series' => $generos->pluck('total')->toArray(),
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'series' => []];
        }
    }

    private function gradoDiscapacidad()
    {
        try {
            $grados = PersonaNoVidente::whereNull('fbj_pnv')
                ->groupBy('gdi_pnv')
                ->selectRaw('gdi_pnv, count(*) as total')
                ->get();

            return [
                'labels' => $grados->pluck('gdi_pnv')->toArray(),
                'series' => $grados->pluck('total')->toArray(),
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'series' => []];
        }
    }

    private function distribucionPorCiudad()
    {
        try {
            $ciudades = PersonaNoVidente::whereNull('fbj_pnv')
                ->groupBy('ciu_pnv')
                ->selectRaw('ciu_pnv, count(*) as total')
                ->orderByRaw('total DESC')
                ->limit(6)
                ->get();

            return [
                'labels' => $ciudades->pluck('ciu_pnv')->toArray(),
                'series' => $ciudades->pluck('total')->toArray(),
            ];
        } catch (\Exception $e) {
            return ['labels' => [], 'series' => []];
        }
    }

    private function ultimasPersonas()
    {
        try {
            return PersonaNoVidente::whereNull('fbj_pnv')
                ->orderByDesc('fcr_pnv')
                ->limit(5)
                ->get(['cod_personas_no_videntes', 'ide_pnv', 'nom_pnv', 'ape_pnv', 'est_pnv', 'fcr_pnv'])
                ->map(function ($p) {
                    return [
                        'id' => $p->cod_personas_no_videntes,
                        'codigo' => $p->ide_pnv,
                        'nombre' => strtoupper(($p->nom_pnv ?? '') . ' ' . ($p->ape_pnv ?? '')),
                        'estado' => $p->est_pnv,
                        'fecha' => $p->fcr_pnv?->format('d/m/Y H:i') ?? '',
                    ];
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function ultimasAfiliaciones()
    {
        try {
            return Afiliacion::with('persona')
                ->orderByDesc('fcr_afi')
                ->limit(5)
                ->get()
                ->map(function ($a) {
                    $nom = $a->persona?->nom_pnv ?? 'SIN PERSONA';
                    $ape = $a->persona?->ape_pnv ?? '';
                    return [
                        'id' => $a->cod_afiliaciones,
                        'persona' => strtoupper($nom . ' ' . $ape),
                        'estado' => $a->est_afi,
                        'fecha' => $a->fcr_afi?->format('d/m/Y') ?? '',
                    ];
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            return [];
        }
    }
}
