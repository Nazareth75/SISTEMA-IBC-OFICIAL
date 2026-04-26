<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TramoController extends Controller
{
    private function verificarEditable(Ruta $ruta): void
    {
        abort_unless(
            in_array($ruta->est_rut, ['BORRADOR', 'DESACTUALIZADA']),
            403,
            'SOLO SE PUEDEN MODIFICAR TRAMOS EN RUTAS CON ESTADO BORRADOR O DESACTUALIZADA.'
        );
    }

    private function verificarPertenencia(Ruta $ruta, CalleTramo $tramo): void
    {
        abort_unless($tramo->cod_rutas_ctr === $ruta->getKey(), 404);
    }

    // ─── Crear tramo ──────────────────────────────────────────

    public function store(Request $request, Ruta $ruta): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarEditable($ruta);

        $data = $request->validate([
            'nom_ctr' => 'required|string|max:150',
            'tip_ctr' => 'required|in:' . implode(',', array_keys(CalleTramo::TIPOS)),
            'ord_ctr' => 'nullable|integer|min:1|max:999',
            'lon_ctr' => 'nullable|numeric|min:0|max:99999.99',
        ], [
            'nom_ctr.required' => 'EL NOMBRE DEL TRAMO ES OBLIGATORIO.',
            'nom_ctr.max'      => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'tip_ctr.required' => 'EL TIPO DE TRAMO ES OBLIGATORIO.',
            'tip_ctr.in'       => 'EL TIPO DE TRAMO SELECCIONADO NO ES VÁLIDO.',
            'ord_ctr.integer'  => 'EL ORDEN DEBE SER UN NÚMERO ENTERO.',
            'lon_ctr.numeric'  => 'LA LONGITUD DEBE SER UN NÚMERO VÁLIDO.',
        ]);

        $data['nom_ctr']       = strtoupper(trim($data['nom_ctr']));
        $data['cod_rutas_ctr'] = $ruta->getKey();

        // Asignar el siguiente orden si no se especificó
        if (empty($data['ord_ctr'])) {
            $data['ord_ctr'] = ($ruta->tramos()->max('ord_ctr') ?? 0) + 1;
        }

        CalleTramo::create($data);

        return redirect()->route('rutas.edit', $ruta)
            ->with('success', 'TRAMO AGREGADO CORRECTAMENTE.');
    }

    // ─── Editar tramo ─────────────────────────────────────────

    public function update(Request $request, Ruta $ruta, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarEditable($ruta);
        $this->verificarPertenencia($ruta, $tramo);

        $data = $request->validate([
            'nom_ctr' => 'required|string|max:150',
            'tip_ctr' => 'required|in:' . implode(',', array_keys(CalleTramo::TIPOS)),
            'lon_ctr' => 'nullable|numeric|min:0|max:99999.99',
        ], [
            'nom_ctr.required' => 'EL NOMBRE DEL TRAMO ES OBLIGATORIO.',
            'nom_ctr.max'      => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'tip_ctr.required' => 'EL TIPO DE TRAMO ES OBLIGATORIO.',
            'tip_ctr.in'       => 'EL TIPO DE TRAMO SELECCIONADO NO ES VÁLIDO.',
            'lon_ctr.numeric'  => 'LA LONGITUD DEBE SER UN NÚMERO VÁLIDO.',
        ]);

        $data['nom_ctr'] = strtoupper(trim($data['nom_ctr']));

        $tramo->update($data);

        return redirect()->route('rutas.edit', $ruta)
            ->with('success', 'TRAMO ACTUALIZADO CORRECTAMENTE.');
    }

    // ─── Eliminar tramo ───────────────────────────────────────

    public function destroy(Ruta $ruta, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarEditable($ruta);
        $this->verificarPertenencia($ruta, $tramo);

        if ($tramo->condiciones()->exists() || $tramo->evaluaciones()->exists()) {
            return redirect()->back()
                ->with('error', 'NO SE PUEDE ELIMINAR UN TRAMO CON CONDICIONES O EVALUACIONES REGISTRADAS. ELIMÍNELAS PRIMERO.');
        }

        $tramo->delete();

        return redirect()->route('rutas.edit', $ruta)
            ->with('success', 'TRAMO ELIMINADO CORRECTAMENTE.');
    }

    // ─── Reordenar tramos (AJAX) ──────────────────────────────

    public function reordenar(Request $request, Ruta $ruta): JsonResponse
    {
        abort_unless(Auth::user()?->puedeEditarRuta(), 403);
        $this->verificarEditable($ruta);

        $request->validate([
            'orden'   => 'required|array|min:1',
            'orden.*' => 'integer|exists:calles_tramos,cod_calles_tramos',
        ]);

        foreach ($request->input('orden') as $posicion => $tramoId) {
            CalleTramo::where('cod_calles_tramos', $tramoId)
                ->where('cod_rutas_ctr', $ruta->getKey())
                ->whereNull('fbj_ctr')
                ->update(['ord_ctr' => $posicion + 1]);
        }

        return response()->json(['ok' => true, 'mensaje' => 'ORDEN ACTUALIZADO CORRECTAMENTE.']);
    }
}
