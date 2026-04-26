<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\CondicionEntorno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CondicionEntornoController extends Controller
{
    private function verificarContexto(CalleTramo $tramo): void
    {
        $ruta = $tramo->ruta;
        abort_if(!$ruta, 404);
        abort_unless(
            in_array($ruta->est_rut, ['BORRADOR', 'DESACTUALIZADA']),
            403,
            'SOLO SE PUEDEN MODIFICAR CONDICIONES EN RUTAS CON ESTADO BORRADOR O DESACTUALIZADA.'
        );
    }

    // ─── Registrar condición ──────────────────────────────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarContexto($tramo);

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

        return redirect()->route('rutas.show', $tramo->ruta)
            ->with('success', 'CONDICIÓN DE ENTORNO REGISTRADA CORRECTAMENTE.');
    }

    // ─── Eliminar condición ───────────────────────────────────

    public function destroy(CalleTramo $tramo, CondicionEntorno $condicion): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarContexto($tramo);
        abort_unless($condicion->cod_calles_tramos_cen === $tramo->getKey(), 404);

        $condicion->delete();

        return redirect()->route('rutas.show', $tramo->ruta)
            ->with('success', 'CONDICIÓN DE ENTORNO ELIMINADA CORRECTAMENTE.');
    }
}
