<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Ruta;

class CondicionEntornoController extends Controller
{
    private function resolverRutaContextual(Request $request, CalleTramo $tramo): Ruta
    {
        $codRutas = $request->input('cod_rutas') ?: $request->query('cod_rutas');
        if ($codRutas) {
            $ruta = $tramo->rutas()->where('rutas.cod_rutas', $codRutas)->first();
            abort_unless($ruta, 404, 'EL TRAMO NO PERTENECE A LA RUTA ESPECIFICADA.');
        } else {
            $ruta = $tramo->rutas()->first() ?? $tramo->ruta;
            abort_unless($ruta, 404, 'EL TRAMO NO TIENE RUTAS ASOCIADAS.');
        }

        abort_unless(
            in_array($ruta->est_rut, ['BORRADOR', 'DESACTUALIZADA']),
            403,
            'SOLO SE PUEDEN MODIFICAR CONDICIONES EN RUTAS CON ESTADO BORRADOR O DESACTUALIZADA.'
        );

        return $ruta;
    }

    // ─── Registrar condición ──────────────────────────────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);

        $data = $request->validate([
            'tip_cen' => 'required|in:' . implode(',', array_keys(CondicionEntorno::TIPOS)),
            'des_cen' => 'required|string|max:250',
            'niv_cen' => 'required|in:' . implode(',', array_keys(CondicionEntorno::NIVELES)),
        ], [
            'tip_cen.required' => 'EL TIPO DE CONDICIÓN ES OBLIGATORIO.',
            'tip_cen.in'       => 'EL TIPO SELECCIONADO NO ES VÁLIDO.',
            'des_cen.required' => 'LA DESCRIPCIÓN ES OBLIGATORIA.',
            'des_cen.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 250 CARACTERES.',
            'niv_cen.required' => 'EL NIVEL ES OBLIGATORIO.',
            'niv_cen.in'       => 'EL NIVEL SELECCIONADO NO ES VÁLIDO.',
        ]);

        $data['des_cen']               = strtoupper(trim($data['des_cen']));
        $data['cod_calles_tramos_cen'] = $tramo->getKey();

        CondicionEntorno::create($data);

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'CONDICIÓN DE ENTORNO REGISTRADA CORRECTAMENTE.');
    }

    // ─── Eliminar condición ───────────────────────────────────

    public function destroy(Request $request, CalleTramo $tramo, CondicionEntorno $condicion): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $rutaContexto = $this->resolverRutaContextual($request, $tramo);
        abort_unless($condicion->cod_calles_tramos_cen === $tramo->getKey(), 404);

        $condicion->delete();

        return redirect()->route('rutas.show', $rutaContexto)
            ->with('success', 'CONDICIÓN DE ENTORNO ELIMINADA CORRECTAMENTE.');
    }
}
