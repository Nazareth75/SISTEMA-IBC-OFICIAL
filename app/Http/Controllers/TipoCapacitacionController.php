<?php

namespace App\Http\Controllers;

use App\Models\TipoCapacitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipoCapacitacionController extends Controller
{
    private function soloAdmin(): void
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->soloAdmin();

        $query = TipoCapacitacion::query();

        if ($request->filled('buscar')) {
            $query->buscador($request->get('buscar'));
        }

        $tipos = $query->orderBy('nom_tca')->paginate(10)->withQueryString();

        return view('tipos_capacitacion.index', compact('tipos'));
    }

    public function create(): View
    {
        $this->soloAdmin();

        return view('tipos_capacitacion.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'nom_tca' => 'required|string|max:100|unique:tipos_capacitacion,nom_tca',
            'des_tca' => 'nullable|string|max:250',
        ], [
            'nom_tca.required' => 'EL NOMBRE DEL TIPO ES OBLIGATORIO.',
            'nom_tca.max'      => 'EL NOMBRE NO PUEDE EXCEDER 100 CARACTERES.',
            'nom_tca.unique'   => 'YA EXISTE UN TIPO DE CAPACITACIÓN CON ESE NOMBRE.',
            'des_tca.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 250 CARACTERES.',
        ]);

        TipoCapacitacion::create([
            'nom_tca' => $request->nom_tca,
            'des_tca' => $request->des_tca,
            'act_tca' => true,
        ]);

        return redirect()->route('tipos-capacitacion.index')
            ->with('success', 'TIPO DE CAPACITACIÓN REGISTRADO CORRECTAMENTE.');
    }

    public function edit(TipoCapacitacion $tiposCapacitacion): View
    {
        $this->soloAdmin();

        return view('tipos_capacitacion.edit', ['tipo' => $tiposCapacitacion]);
    }

    public function update(Request $request, TipoCapacitacion $tiposCapacitacion): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'nom_tca' => 'required|string|max:100|unique:tipos_capacitacion,nom_tca,' . $tiposCapacitacion->cod_tipos_capacitacion . ',cod_tipos_capacitacion',
            'des_tca' => 'nullable|string|max:250',
        ], [
            'nom_tca.required' => 'EL NOMBRE DEL TIPO ES OBLIGATORIO.',
            'nom_tca.max'      => 'EL NOMBRE NO PUEDE EXCEDER 100 CARACTERES.',
            'nom_tca.unique'   => 'YA EXISTE UN TIPO DE CAPACITACIÓN CON ESE NOMBRE.',
            'des_tca.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 250 CARACTERES.',
        ]);

        $tiposCapacitacion->update([
            'nom_tca' => $request->nom_tca,
            'des_tca' => $request->des_tca,
        ]);

        return redirect()->route('tipos-capacitacion.index')
            ->with('success', 'TIPO DE CAPACITACIÓN ACTUALIZADO CORRECTAMENTE.');
    }

    public function destroy(TipoCapacitacion $tiposCapacitacion): RedirectResponse
    {
        $this->soloAdmin();

        if ($tiposCapacitacion->capacitaciones()->exists()) {
            return redirect()->route('tipos-capacitacion.index')
                ->with('error', 'NO SE PUEDE ELIMINAR: EL TIPO TIENE CAPACITACIONES ASOCIADAS.');
        }

        $tiposCapacitacion->delete();

        return redirect()->route('tipos-capacitacion.index')
            ->with('success', 'TIPO DE CAPACITACIÓN ELIMINADO CORRECTAMENTE.');
    }

    public function toggle(TipoCapacitacion $tiposCapacitacion): RedirectResponse
    {
        $this->soloAdmin();

        $tiposCapacitacion->update(['act_tca' => !$tiposCapacitacion->act_tca]);

        $mensaje = $tiposCapacitacion->act_tca
            ? 'TIPO DE CAPACITACIÓN ACTIVADO CORRECTAMENTE.'
            : 'TIPO DE CAPACITACIÓN DESACTIVADO CORRECTAMENTE.';

        return redirect()->route('tipos-capacitacion.index')
            ->with('success', $mensaje);
    }
}
