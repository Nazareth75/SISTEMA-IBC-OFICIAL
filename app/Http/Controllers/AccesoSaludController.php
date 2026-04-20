<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccesoSaludRequest;
use App\Http\Requests\UpdateAccesoSaludRequest;
use App\Models\AccesoSalud;
use App\Models\Afiliacion;
use App\Models\InstitucionExterna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccesoSaludController extends Controller
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

        $query = AccesoSalud::conRelaciones();

        // Búsqueda
        if ($request->filled('buscar')) {
            $termino = $request->get('buscar');
            $query->whereHas('afiliacion.persona', function ($sub) use ($termino) {
                $sub->where('nom_pnv', 'like', "%{$termino}%")
                    ->orWhere('ape_pnv', 'like', "%{$termino}%")
                    ->orWhere('cii_pnv', 'like', "%{$termino}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->porEstado($request->get('estado'));
        }

        $accesos = $query->paginate(15)->withQueryString();

        return view('accesos_salud.index', [
            'accesos' => $accesos,
            'estados' => AccesoSalud::ESTADOS,
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

        return view('accesos_salud.create', [
            'estados' => AccesoSalud::ESTADOS,
            'instituciones' => InstitucionExterna::activas()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccesoSaludRequest $request)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $validated = $request->validated();

        try {
            $validated['cod_usuarios_asa'] = Auth::id();
            $validated['fcr_asa'] = now();
            $validated['fmo_asa'] = now();

            AccesoSalud::create($validated);

            return redirect()->route('accesos-salud.index')
                ->with('success', 'Acceso registrado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al registrar el acceso: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AccesoSalud $accesoSalud)
    {
        return view('accesos_salud.show', [
            'acceso' => $accesoSalud->load(['afiliacion.persona', 'institucion', 'usuarioRegistro']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccesoSalud $accesoSalud)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return view('accesos_salud.edit', [
            'acceso' => $accesoSalud->load(['afiliacion.persona', 'institucion', 'usuarioRegistro']),
            'estados' => AccesoSalud::ESTADOS,
            'instituciones' => InstitucionExterna::activas()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccesoSaludRequest $request, AccesoSalud $accesoSalud)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $validated = $request->validated();

        try {
            $validated['fmo_asa'] = now();

            $accesoSalud->update($validated);

            return redirect()->route('accesos-salud.index')
                ->with('success', 'Acceso actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el acceso: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccesoSalud $accesoSalud)
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $accesoSalud->darDeBaja();

        return redirect()->route('accesos-salud.index')
            ->with('success', 'Acceso eliminado exitosamente.');
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
}
