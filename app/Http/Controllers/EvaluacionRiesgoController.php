<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\EvaluacionRiesgo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\Ruta;

class EvaluacionRiesgoController extends Controller
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

    // ─── Registrar evaluación ─────────────────────────────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);

        $data = $request->validate([
            'cod_niveles_seguridad_eri' => [
                'required',
                'integer',
                Rule::exists('niveles_seguridad', 'cod_niveles_seguridad'),
            ],
            'fee_eri' => 'required|date|date_format:Y-m-d',
            'met_eri' => 'nullable|string|max:150',
            'obs_eri' => 'nullable|string|max:500',
        ], [
            'cod_niveles_seguridad_eri.required' => 'EL NIVEL DE SEGURIDAD ES OBLIGATORIO.',
            'cod_niveles_seguridad_eri.exists'   => 'EL NIVEL SELECCIONADO NO EXISTE.',
            'fee_eri.required'                   => 'LA FECHA DE EVALUACIÓN ES OBLIGATORIA.',
            'fee_eri.date'                       => 'LA FECHA DE EVALUACIÓN NO ES VÁLIDA.',
            'fee_eri.date_format'                => 'LA FECHA DEBE TENER FORMATO YYYY-MM-DD.',
            'met_eri.max'                        => 'EL MÉTODO NO PUEDE EXCEDER 150 CARACTERES.',
        ]);

        $data['cod_calles_tramos_eri'] = $tramo->getKey();
        $data['cod_usuarios_eri']      = Auth::id();

        EvaluacionRiesgo::create($data);

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'EVALUACIÓN DE RIESGO REGISTRADA CORRECTAMENTE.');
    }

    // ─── Eliminar evaluación ──────────────────────────────────

    public function destroy(Request $request, CalleTramo $tramo, EvaluacionRiesgo $evaluacion): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);
        abort_unless($evaluacion->cod_calles_tramos_eri === $tramo->getKey(), 404);

        $evaluacion->delete();

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'EVALUACIÓN DE RIESGO ELIMINADA CORRECTAMENTE.');
    }
}
