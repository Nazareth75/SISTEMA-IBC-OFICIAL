<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAfiliacionRequest;
use App\Http\Requests\UpdateAfiliacionRequest;
use App\Models\Afiliacion;
use App\Models\PersonaNoVidente;
use App\Models\TipoAfiliado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AfiliacionController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar'));
        $estado = trim((string) $request->get('estado'));
        $tipo = trim((string) $request->get('tipo'));

        $afiliaciones = Afiliacion::query()
            ->noEliminadas()
            ->with(['persona', 'tipoAfiliado'])
            ->buscar($buscar)
            ->when($estado !== '', fn ($q) => $q->where('est_afi', $estado))
            ->when($tipo !== '', fn ($q) => $q->where('cod_tipos_afiliado_afi', $tipo))
            ->orderByDesc('cod_afiliaciones')
            ->paginate(10)
            ->withQueryString();

            $afiliaciones->getCollection()->each(function ($afiliacion) {
                $afiliacion->actualizarEstadoPorVencimiento();
            });

        return view('afiliaciones.index', [
            'afiliaciones' => $afiliaciones,
            'filtros' => compact('buscar', 'estado', 'tipo'),
            'estados' => ['ACTIVA', 'INACTIVA', 'SUSPENDIDA', 'VENCIDA'],
            'tiposAfiliado' => TipoAfiliado::activos()->orderBy('nom_taf')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(Auth::user()?->puedeCrearAfiliacion(), 403);

        return view('afiliaciones.create', [
            'tiposAfiliado' => TipoAfiliado::activos()->orderBy('nom_taf')->get(),
            'personas' => PersonaNoVidente::query()
                ->activas()
                ->where('est_pnv', 'ACTIVO')
                ->orderBy('nom_pnv')
                ->get(),
        ]);
    }

    public function store(StoreAfiliacionRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeCrearAfiliacion(), 403);

        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $data['ide_afi'] = $this->generarCodigo();
            $data['cod_usuarios_afi'] = Auth::id();
            $data['est_afi'] = $data['est_afi'] ?? 'ACTIVA';

            Afiliacion::create($data);
        });

        return redirect()
            ->route('afiliaciones.index')
            ->with('success', 'Afiliación registrada correctamente.');
    }

    public function show(Afiliacion $afiliacione): View
    {
        $afiliacione->load(['persona', 'tipoAfiliado', 'usuarioRegistro']);
        $afiliacione->actualizarEstadoPorVencimiento();
        $afiliacione->refresh();

        return view('afiliaciones.show', [
            'afiliacion' => $afiliacione,
        ]);
    }

    public function edit(Afiliacion $afiliacione): View
    {
        abort_unless(Auth::user()?->puedeEditarAfiliacion(), 403);

        $afiliacione->load(['persona', 'tipoAfiliado']);
        $afiliacione->actualizarEstadoPorVencimiento();
        $afiliacione->refresh();

        return view('afiliaciones.edit', [
            'afiliacion' => $afiliacione,
            'tiposAfiliado' => TipoAfiliado::activos()->orderBy('nom_taf')->get(),
            'estados' => ['ACTIVA', 'INACTIVA', 'SUSPENDIDA', 'VENCIDA'],
        ]);
    }

    public function update(UpdateAfiliacionRequest $request, Afiliacion $afiliacione): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarAfiliacion(), 403);

        DB::transaction(function () use ($request, $afiliacione) {
            $data = $request->validated();
            $afiliacione->update($data);
            $afiliacione->actualizarEstadoPorVencimiento();
        });

        return redirect()
            ->route('afiliaciones.show', $afiliacione)
            ->with('success', 'Afiliación actualizada correctamente.');
    }

    public function destroy(Afiliacion $afiliacione): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEliminarAfiliacion(), 403);

        $afiliacione->update([
            'fbj_afi' => now(),
        ]);

        return redirect()
            ->route('afiliaciones.index')
            ->with('success', 'Afiliación eliminada correctamente.');
    }

    public function buscarPersonasSinAfiliacion(Request $request)
    {
        abort_unless(Auth::user()?->puedeCrearAfiliacion(), 403);

        $query = trim((string) $request->get('q', ''));

        // Obtener IDs de personas que ya tienen afiliación activa/vigente
        $personasConAfiliacion = Afiliacion::query()
            ->where(function ($q) {
                $q->where('est_afi', 'ACTIVA')
                  ->orWhere('est_afi', 'VENCIDA');
            })
            ->whereNull('fbj_afi')
            ->distinct()
            ->pluck('cod_personas_no_videntes_afi');

        // Buscar personas sin afiliación
        $personas = PersonaNoVidente::query()
            ->whereNotIn('cod_personas_no_videntes', $personasConAfiliacion)
            ->where('est_pnv', 'ACTIVO')
            ->when($query !== '', function ($q) use ($query) {
                return $q->where('nom_pnv', 'LIKE', $query . '%')
                         ->orWhere('cii_pnv', 'LIKE', '%' . $query . '%');
            })
            ->orderBy('nom_pnv')
            ->limit(5)
            ->get(['cod_personas_no_videntes', 'ide_pnv', 'nom_pnv', 'app_pnv', 'apm_pnv', 'cii_pnv', 'gen_pnv'])
            ->map(function ($persona) {
                return [
                    'id' => $persona->cod_personas_no_videntes,
                    'nombre_completo' => $persona->nombre_completo,
                    'cii' => $persona->cii_pnv,
                    'genero' => $persona->gen_pnv,
                    'codigo' => $persona->ide_pnv,
                ];
            });

        return response()->json($personas);
    }

    private function generarCodigo(): string
    {
        $ultimo = Afiliacion::query()
            ->orderByDesc('cod_afiliaciones')
            ->value('ide_afi');

        if (!$ultimo) {
            return 'AFI-001';
        }

        $numero = (int) str_replace('AFI-', '', $ultimo);

        return 'AFI-' . str_pad((string) ($numero + 1), 3, '0', STR_PAD_LEFT);
    }
}