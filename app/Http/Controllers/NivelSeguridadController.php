<?php

namespace App\Http\Controllers;

use App\Models\NivelSeguridad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NivelSeguridadController extends Controller
{
    // ─── Pantalla 5: Catálogo de niveles ──────────────────────

    public function index(): View
    {
        $niveles = NivelSeguridad::orderBy('niv_nse')->get();

        return view('niveles_seguridad.index', compact('niveles'));
    }

    // ─── Editar nivel (solo Admin) ────────────────────────────

    public function edit(NivelSeguridad $nivelSeguridad): View
    {
        abort_unless(Auth::user()?->puedeGestionarNivelesSeguridad(), 403);

        return view('niveles_seguridad.edit', compact('nivelSeguridad'));
    }

    public function update(Request $request, NivelSeguridad $nivelSeguridad): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarNivelesSeguridad(), 403);

        $data = $request->validate([
            'des_nse' => 'nullable|string|max:200',
            'col_nse' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'col_nse.required' => 'EL COLOR ES OBLIGATORIO.',
            'col_nse.regex'    => 'EL COLOR DEBE SER UN CÓDIGO HEXADECIMAL VÁLIDO (EJ: #2d6a27).',
            'des_nse.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 200 CARACTERES.',
        ]);

        $nivelSeguridad->update($data);

        return redirect()->route('niveles-seguridad.index')
            ->with('success', "NIVEL \"{$nivelSeguridad->nom_nse}\" ACTUALIZADO CORRECTAMENTE.");
    }
}
