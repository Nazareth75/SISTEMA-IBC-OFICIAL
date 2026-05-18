<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstitucionExternaRequest;
use App\Http\Requests\UpdateInstitucionExternaRequest;
use App\Models\InstitucionExterna;
use Illuminate\Http\Request;

class InstitucionExternaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validar permisos
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $query = InstitucionExterna::noEliminadas();

        // Búsqueda
        if ($request->filled('buscar')) {
            $query->buscador($request->get('buscar'));
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->porTipo($request->get('tipo'));
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->get('estado') === 'activa') {
                $query->activas();
            } else {
                $query->inactivas();
            }
        }

        $instituciones = $query->paginate(15)->withQueryString();

        return view('instituciones_externas.index', [
            'instituciones' => $instituciones,
            'tipos' => InstitucionExterna::TIPOS,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return view('instituciones_externas.create', [
            'tipos' => InstitucionExterna::TIPOS,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstitucionExternaRequest $request)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        $validated = $request->validated();

        $validated['act_iex'] = true;
        $validated['fcr_iex'] = now();
        $validated['fmo_iex'] = now();

        InstitucionExterna::create($validated);

        return redirect()->route('instituciones-externas.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */

    public function show($id)
{
    $institucionExterna = InstitucionExterna::findOrFail($id);

    return view('instituciones_externas.show', compact('institucionExterna'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InstitucionExterna $institucionExterna)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return view('instituciones_externas.edit', [
            'institucion' => $institucionExterna,
            'tipos' => InstitucionExterna::TIPOS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstitucionExternaRequest $request, InstitucionExterna $institucionExterna)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        $validated = $request->validated();

        $validated['fmo_iex'] = now();

        $institucionExterna->update($validated);

        return redirect()->route('instituciones-externas.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstitucionExterna $institucionExterna)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $institucionExterna->darDeBaja();

        return redirect()->route('instituciones-externas.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }

    /**
     * Toggle activation/deactivation
     */
    public function toggle(InstitucionExterna $institucionExterna)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        if ($institucionExterna->estaActiva()) {
            $institucionExterna->desactivar();
            $mensaje = 'Institución desactivada exitosamente.';
        } else {
            $institucionExterna->activar();
            $mensaje = 'Institución activada exitosamente.';
        }

        return redirect()->route('instituciones-externas.index')
            ->with('success', $mensaje);
    }
}
