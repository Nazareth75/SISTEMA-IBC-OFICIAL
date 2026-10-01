<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\ZonaRiesgo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\Ruta;

class ZonaRiesgoController extends Controller
{
    private function resolverRutaContextual(Request $request, CalleTramo $tramo): Ruta
    {
        $codRutas = $request->input('cod_rutas') ?: $request->query('cod_rutas');
        if ($codRutas) {
            $ruta = $tramo->rutas()->where('rutas.cod_rutas', $codRutas)->first();
            abort_unless($ruta, 404, 'EL TRAMO NO PERTENECE A LA RUTA ESPECIFICADA.');
            return $ruta;
        }

        $ruta = $tramo->rutas()->first() ?? $tramo->ruta;
        abort_unless($ruta, 404, 'EL TRAMO NO TIENE RUTAS ASOCIADAS.');
        return $ruta;
    }

    // ─── Registrar zona ───────────────────────────────────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);

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

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'ZONA DE RIESGO REGISTRADA CORRECTAMENTE.');
    }

    // ─── Eliminar zona ────────────────────────────────────────

    public function destroy(Request $request, CalleTramo $tramo, ZonaRiesgo $zona): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);
        abort_unless($zona->cod_calles_tramos_zri === $tramo->getKey(), 404);

        $zona->delete();

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'ZONA DE RIESGO ELIMINADA CORRECTAMENTE.');
    }
}
