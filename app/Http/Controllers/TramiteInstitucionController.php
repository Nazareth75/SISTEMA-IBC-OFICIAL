<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTramiteInstitucionRequest;
use App\Http\Requests\UpdateTramiteInstitucionRequest;
use App\Models\Afiliacion;
use App\Models\InstitucionExterna;
use App\Models\TramiteInstitucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TramiteInstitucionController extends Controller
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

        $query = TramiteInstitucion::conRelaciones();

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
            $query->porEstado($request->get('estado'));
        }

        $tramites = $query->paginate(15)->withQueryString();

        return view('tramites_instituciones.index', [
            'tramites' => $tramites,
            'tipos' => TramiteInstitucion::TIPOS,
            'estados' => TramiteInstitucion::ESTADOS,
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

        return view('tramites_instituciones.create', [
            'tipos' => TramiteInstitucion::TIPOS,
            'estados' => TramiteInstitucion::ESTADOS,
            'instituciones' => InstitucionExterna::activas()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTramiteInstitucionRequest $request)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $validated = $request->validated();

        try {
            $validated['est_tin'] = 'PENDIENTE';
            $validated['cod_usuarios_tin'] = Auth::id();

            TramiteInstitucion::create($validated);

            return redirect()->route('tramites-instituciones.index')
                ->with('success', 'Trámite registrado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al registrar el trámite: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TramiteInstitucion $tramiteInstitucion)
    {
        return view('tramites_instituciones.show', [
            'tramite' => $tramiteInstitucion->load(['afiliacion.persona', 'institucion', 'usuarioRegistro']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TramiteInstitucion $tramiteInstitucion)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return view('tramites_instituciones.edit', [
            'tramite' => $tramiteInstitucion->load(['afiliacion.persona', 'institucion', 'usuarioRegistro']),
            'tipos' => TramiteInstitucion::TIPOS,
            'estados' => TramiteInstitucion::ESTADOS,
            'instituciones' => InstitucionExterna::activas()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTramiteInstitucionRequest $request, TramiteInstitucion $tramiteInstitucion)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $validated = $request->validated();

        try {
            $validated['fmo_tin'] = now();

            // Si cambio a RESUELTO y no tiene fecha de resolución, agregarla
            if ($validated['est_tin'] === 'RESUELTO' && !$validated['fer_tin']) {
                $validated['fer_tin'] = now()->toDateString();
            }

            $tramiteInstitucion->update($validated);

            return redirect()->route('tramites-instituciones.index')
                ->with('success', 'Trámite actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el trámite: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TramiteInstitucion $tramiteInstitucion)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $tramiteInstitucion->darDeBaja();

        return redirect()->route('tramites-instituciones.index')
            ->with('success', 'Trámite eliminado exitosamente.');
    }

    /**
     * AJAX: Buscar afiliados activos
     */
    public function buscarAfiliados(Request $request)
    {
        try {
            $termino = trim((string) $request->get('q', ''));

            $query = Afiliacion::activas()->with('persona');

            if (!empty($termino) && strlen($termino) >= 1) {
                $query->whereHas('persona', function ($sub) use ($termino) {
                    $sub->where('nom_pnv', 'like', "%{$termino}%")
                        ->orWhere('ape_pnv', 'like', "%{$termino}%")
                        ->orWhere('cii_pnv', 'like', "%{$termino}%");
                });
            }

            $afiliaciones = $query->limit(15)->get()
                ->map(function ($afiliacion) {
                    $persona = $afiliacion->persona;
                    return [
                        'id' => $afiliacion->cod_afiliaciones,
                        'text' => $persona
                            ? "{$persona->nom_pnv} {$persona->ape_pnv} ({$persona->cii_pnv})"
                            : "Afiliación #{$afiliacion->cod_afiliaciones}",
                    ];
                })
                ->values();

            return response()->json([
                'results' => $afiliaciones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'error' => 'Error al buscar afiliados.',
            ], 500);
        }
    }

    /**
     * AJAX: Cambiar estado de trámite
     */
    public function cambiarEstado(Request $request, TramiteInstitucion $tramiteInstitucion)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $validated = $request->validate([
            'nuevo_estado' => 'required|in:' . implode(',', array_keys(TramiteInstitucion::ESTADOS)),
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $tramiteInstitucion->cambiarEstado(
                $validated['nuevo_estado'],
                $validated['observaciones'] ?? null
            );

            return redirect()->back()->with('success', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
