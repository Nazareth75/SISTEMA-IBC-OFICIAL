<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\ZonaRiesgo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ZonaRiesgoController extends Controller
{
    // ─── Registrar zona ───────────────────────────────────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);

        $data = $request->validate([
            'nom_zri'                   => 'required|string|max:150',
            'cod_niveles_seguridad_zri' => [
                'required',
                'integer',
                Rule::exists('niveles_seguridad', 'cod_niveles_seguridad'),
            ],
            'des_zri' => 'nullable|string|max:500',
        ], [
            'nom_zri.required'                   => 'EL NOMBRE DE LA ZONA ES OBLIGATORIO.',
            'nom_zri.max'                        => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'cod_niveles_seguridad_zri.required'  => 'EL NIVEL DE SEGURIDAD ES OBLIGATORIO.',
            'cod_niveles_seguridad_zri.exists'    => 'EL NIVEL SELECCIONADO NO EXISTE.',
        ]);

        $data['nom_zri']               = strtoupper(trim($data['nom_zri']));
        $data['cod_calles_tramos_zri'] = $tramo->getKey();

        ZonaRiesgo::create($data);

        return redirect()->route('rutas.show', $tramo->ruta)
            ->with('success', 'ZONA DE RIESGO REGISTRADA CORRECTAMENTE.');
    }

    // ─── Eliminar zona ────────────────────────────────────────

    public function destroy(CalleTramo $tramo, ZonaRiesgo $zona): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        abort_unless($zona->cod_calles_tramos_zri === $tramo->getKey(), 404);

        $zona->delete();

        return redirect()->route('rutas.show', $tramo->ruta)
            ->with('success', 'ZONA DE RIESGO ELIMINADA CORRECTAMENTE.');
    }
}
