<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBeneficioRequest;
use App\Http\Requests\UpdateBeneficioRequest;
use App\Models\BeneficioSocial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BeneficioController extends Controller
{
    /**
     * Listar todos los beneficios sociales
     * PANTALLA 1: Catálogo de Beneficios
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar'));
        $tipo = trim((string) $request->get('tipo'));
        $estado = trim((string) $request->get('estado'));

        $beneficios = BeneficioSocial::query()
            ->with('asignaciones')
            ->when($buscar !== '', fn ($q) => $q->buscador($buscar))
            ->when($tipo !== '', fn ($q) => $q->porTipo($tipo))
            ->when($estado === 'activos', fn ($q) => $q->activos())
            ->when($estado === 'inactivos', fn ($q) => $q->inactivos())
            ->orderByDesc('cod_beneficios_sociales')
            ->paginate(15)
            ->withQueryString();

        return view('beneficios.index', [
            'beneficios' => $beneficios,
            'filtros' => compact('buscar', 'tipo', 'estado'),
            'tipos' => BeneficioSocial::TIPOS,
            'estadosFiltro' => [
                'todos' => 'Todos',
                'activos' => 'Activos',
                'inactivos' => 'Inactivos',
            ],
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo beneficio
     * PANTALLA 2: Registrar Beneficio
     */
    public function create(): View
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        return view('beneficios.create', [
            'tipos' => BeneficioSocial::TIPOS,
        ]);
    }

    /**
     * Guardar nuevo beneficio en base de datos
     */
    public function store(StoreBeneficioRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            BeneficioSocial::create([
                'nom_bso' => $validated['nom_bso'],
                'tip_bso' => $validated['tip_bso'],
                'des_bso' => $validated['des_bso'],
                'act_bso' => true,
            ]);
        });

        return redirect()
            ->route('beneficios.index')
            ->with('success', 'Beneficio registrado correctamente.');
    }

    /**
     * Mostrar formulario para editar beneficio
     */
    public function edit(BeneficioSocial $beneficio): View
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        return view('beneficios.edit', [
            'beneficio' => $beneficio,
            'tipos' => BeneficioSocial::TIPOS,
        ]);
    }

    /**
     * Actualizar beneficio en base de datos
     */
    public function update(UpdateBeneficioRequest $request, BeneficioSocial $beneficio): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $beneficio) {
            $beneficio->update([
                'nom_bso' => $validated['nom_bso'],
                'tip_bso' => $validated['tip_bso'],
                'des_bso' => $validated['des_bso'],
            ]);
        });

        return redirect()
            ->route('beneficios.index')
            ->with('success', 'Beneficio actualizado correctamente.');
    }

    /**
     * Eliminar (dar de baja) beneficio
     */
    public function destroy(BeneficioSocial $beneficio): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        DB::transaction(function () use ($beneficio) {
            $beneficio->update([
                'fbj_bso' => now(),
            ]);
        });

        return redirect()
            ->route('beneficios.index')
            ->with('success', 'Beneficio eliminado correctamente.');
    }

    /**
     * Activar o desactivar beneficio (toggle)
     * Acción: ACTIVAR/DESACTIVAR en tabla
     */
    public function toggle(BeneficioSocial $beneficio): RedirectResponse
    {
        abort_unless(Auth::user()?->esAdministrador(), 403);

        DB::transaction(function () use ($beneficio) {
            if ($beneficio->estaActivo()) {
                $beneficio->desactivar();
            } else {
                $beneficio->activar();
            }
        });

        $mensaje = $beneficio->estaActivo()
            ? 'Beneficio activado correctamente.'
            : 'Beneficio desactivado correctamente.';

        return redirect()
            ->route('beneficios.index')
            ->with('success', $mensaje);
    }
}
