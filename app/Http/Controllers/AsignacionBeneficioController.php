<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsignacionBeneficioRequest;
use App\Http\Requests\UpdateAsignacionBeneficioRequest;
use App\Models\AsignacionBeneficio;
use App\Models\Afiliacion;
use App\Models\BeneficioSocial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AsignacionBeneficioController extends Controller
{
    /**
     * Listar todas las asignaciones de beneficios
     * PANTALLA 3: Listado de Asignaciones
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar'));
        $tipo = trim((string) $request->get('tipo'));
        $estado = trim((string) $request->get('estado'));

        $asignaciones = AsignacionBeneficio::query()
            ->conRelaciones()
            ->actuales()
            ->when($buscar !== '', fn ($q) => $q->buscador($buscar))
            ->when($tipo !== '', fn ($q) => $q->porTipoBeneficio($tipo))
            ->when($estado !== '', fn ($q) => $q->porEstado($estado))
            ->orderByDesc('cod_asignacion_beneficios')
            ->paginate(15)
            ->withQueryString();

        return view('asignacion_beneficios.index', [
            'asignaciones' => $asignaciones,
            'filtros' => compact('buscar', 'tipo', 'estado'),
            'tipos' => BeneficioSocial::TIPOS,
            'estados' => AsignacionBeneficio::ESTADOS,
        ]);
    }

    /**
     * Mostrar formulario para asignar beneficio a un afiliado
     * PANTALLA 4: Asignar Beneficio
     */
    public function create(): View
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        return view('asignacion_beneficios.create', [
            'beneficiosActivos' => BeneficioSocial::activos()->orderBy('nom_bso')->get(),
            'tipos' => BeneficioSocial::TIPOS,
        ]);
    }

    /**
     * Guardar nueva asignación de beneficio
     */
    public function store(StoreAsignacionBeneficioRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        $validated = $request->validated();

        // Verificar si el afiliado ya tiene este beneficio activo
        if (AsignacionBeneficio::tieneBeneficioActivo(
            $validated['cod_afiliaciones_abe'],
            $validated['cod_beneficios_sociales_abe']
        )) {
            return back()
                ->withInput()
                ->with('error', 'Este afiliado ya tiene asignado este beneficio en estado ACTIVO.');
        }

        DB::transaction(function () use ($validated) {
            $validated['cod_usuarios_abe'] = Auth::id();

            AsignacionBeneficio::create($validated);
        });

        return redirect()
            ->route('asignacion-beneficios.index')
            ->with('success', 'Beneficio asignado correctamente al afiliado.');
    }

    /**
     * Mostrar formulario para editar asignación
     * PANTALLA 5: Ver/Editar Asignación
     */
    public function edit(AsignacionBeneficio $asignacionBeneficio): View
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        $asignacionBeneficio->load(['afiliacion.persona', 'beneficio', 'usuarioRegistro']);

        return view('asignacion_beneficios.edit', [
            'asignacion' => $asignacionBeneficio,
            'estados' => AsignacionBeneficio::ESTADOS,
            'beneficiosActivos' => BeneficioSocial::activos()->orderBy('nom_bso')->get(),
        ]);
    }

    /**
     * Actualizar asignación de beneficio
     */
    public function update(UpdateAsignacionBeneficioRequest $request, AsignacionBeneficio $asignacionBeneficio): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        $validated = $request->validated();

        // Verificar si cambió de beneficio y si ya tiene ese beneficio activo
        if ($validated['cod_beneficios_sociales_abe'] !== $asignacionBeneficio->cod_beneficios_sociales_abe) {
            if (AsignacionBeneficio::where('cod_asignacion_beneficios', '!=', $asignacionBeneficio->cod_asignacion_beneficios)
                ->where('cod_afiliaciones_abe', $asignacionBeneficio->cod_afiliaciones_abe)
                ->where('cod_beneficios_sociales_abe', $validated['cod_beneficios_sociales_abe'])
                ->where('est_abe', 'ACTIVO')
                ->whereNull('fbj_abe')
                ->exists()
            ) {
                return back()
                    ->withInput()
                    ->with('error', 'Este afiliado ya tiene asignado ese beneficio en estado ACTIVO.');
            }
        }

        DB::transaction(function () use ($validated, $asignacionBeneficio) {
            $asignacionBeneficio->update($validated);
        });

        return redirect()
            ->route('asignacion-beneficios.index')
            ->with('success', 'Asignación de beneficio actualizada correctamente.');
    }

    /**
     * Eliminar (dar de baja) asignación de beneficio
     */
    public function destroy(AsignacionBeneficio $asignacionBeneficio): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        DB::transaction(function () use ($asignacionBeneficio) {
            $asignacionBeneficio->update([
                'fbj_abe' => now(),
            ]);
        });

        return redirect()
            ->route('asignacion-beneficios.index')
            ->with('success', 'Asignación de beneficio eliminada correctamente.');
    }

    /**
     * Obtener afiliados activos para el buscador (AJAX)
     */
    public function buscarAfiliados(Request $request)
    {
        try {
            $termino = trim((string) $request->get('q', ''));

            $query = Afiliacion::query()
                ->activas()
                ->with('persona');

            // Si hay término de búsqueda, filtra
            if (!empty($termino) && strlen($termino) >= 1) {
                $query->whereHas('persona', function ($sub) use ($termino) {
                    $sub->where('nom_pnv', 'like', '%' . $termino . '%')
                        ->orWhere('ape_pnv', 'like', '%' . $termino . '%')
                        ->orWhere('cii_pnv', 'like', '%' . $termino . '%');
                });
            }

            // Ordena por nombre y limita a 15 resultados
            $afiliados = $query
                ->orderBy('cod_personas_no_videntes_afi')
                ->limit(15)
                ->get()
                ->filter(function ($afiliacion) {
                    // Asegura que la persona exista
                    return $afiliacion->persona !== null;
                })
                ->map(function ($afiliacion) {
                    return [
                        'id' => $afiliacion->cod_afiliaciones,
                        'text' => "{$afiliacion->persona->nom_pnv} {$afiliacion->persona->ape_pnv} (CI: {$afiliacion->persona->cii_pnv})",
                    ];
                })
                ->values(); // Reindexa el array

            return response()->json($afiliados);
        } catch (\Exception $e) {
            \Log::error('Error en buscarAfiliados: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}
