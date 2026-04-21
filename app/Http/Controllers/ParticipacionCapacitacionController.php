<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\ParticipacionCapacitacion;
use App\Models\PersonaNoVidente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipacionCapacitacionController extends Controller
{
    private const TRANSICIONES = [
        'INSCRITO' => ['EN CURSO', 'ABANDONÓ'],
        'EN CURSO'  => ['APROBADO', 'REPROBADO', 'ABANDONÓ'],
    ];

    private function soloAdmin(): void
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'cod_personas_no_videntes_pca' => 'required|integer|exists:personas_no_videntes,cod_personas_no_videntes',
            'cod_capacitaciones_pca'       => 'required|integer|exists:capacitaciones,cod_capacitaciones',
            'fei_pca'                      => 'required|date|date_format:Y-m-d',
            'obs_pca'                      => 'nullable|string|max:500',
        ], [
            'cod_personas_no_videntes_pca.required' => 'DEBE SELECCIONAR UNA PERSONA.',
            'cod_personas_no_videntes_pca.exists'   => 'LA PERSONA SELECCIONADA NO EXISTE.',
            'cod_capacitaciones_pca.required'       => 'DEBE INDICAR LA CAPACITACIÓN.',
            'cod_capacitaciones_pca.exists'         => 'LA CAPACITACIÓN INDICADA NO EXISTE.',
            'fei_pca.required'                      => 'LA FECHA DE INSCRIPCIÓN ES OBLIGATORIA.',
            'fei_pca.date_format'                   => 'LA FECHA DE INSCRIPCIÓN DEBE TENER FORMATO YYYY-MM-DD.',
            'obs_pca.max'                           => 'LAS OBSERVACIONES NO PUEDEN EXCEDER 500 CARACTERES.',
        ]);

        $persona      = PersonaNoVidente::findOrFail($request->cod_personas_no_videntes_pca);
        $capacitacion = Capacitacion::findOrFail($request->cod_capacitaciones_pca);

        if ($persona->est_pnv !== 'ACTIVO') {
            return redirect()->back()->withInput()
                ->with('error', 'LA PERSONA NO ESTÁ EN ESTADO ACTIVO. SOLO SE PUEDEN INSCRIBIR PERSONAS ACTIVAS.');
        }

        if (!$capacitacion->estaDisponible()) {
            return redirect()->back()->withInput()
                ->with('error', 'LA CAPACITACIÓN NO ESTÁ DISPONIBLE. SOLO SE ACEPTAN INSCRIPCIONES EN CAPACITACIONES PLANIFICADAS O EN CURSO.');
        }

        if (ParticipacionCapacitacion::yaInscrita(
            $persona->cod_personas_no_videntes,
            $capacitacion->cod_capacitaciones
        )) {
            return redirect()->back()->withInput()
                ->with('error', 'LA PERSONA YA TIENE UNA PARTICIPACIÓN ACTIVA EN ESTA CAPACITACIÓN.');
        }

        ParticipacionCapacitacion::create([
            'cod_personas_no_videntes_pca' => $persona->cod_personas_no_videntes,
            'cod_capacitaciones_pca'       => $capacitacion->cod_capacitaciones,
            'fei_pca'                      => $request->fei_pca,
            'est_pca'                      => 'INSCRITO',
            'obs_pca'                      => $request->obs_pca,
        ]);

        return redirect()->route('capacitaciones.show', $capacitacion)
            ->with('success', 'PARTICIPACIÓN REGISTRADA CORRECTAMENTE.');
    }

    public function update(Request $request, ParticipacionCapacitacion $participacion): RedirectResponse
    {
        $this->soloAdmin();

        $estadoActual = $participacion->est_pca;
        $permitidos   = self::TRANSICIONES[$estadoActual] ?? [];

        $request->validate([
            'est_pca' => 'required|in:' . implode(',', array_keys(ParticipacionCapacitacion::ESTADOS)),
            'obs_pca' => 'nullable|string|max:500',
        ], [
            'est_pca.required' => 'DEBE INDICAR EL NUEVO ESTADO.',
            'est_pca.in'       => 'EL ESTADO INDICADO NO ES VÁLIDO.',
            'obs_pca.max'      => 'LAS OBSERVACIONES NO PUEDEN EXCEDER 500 CARACTERES.',
        ]);

        $nuevoEstado = $request->est_pca;

        if (empty($permitidos)) {
            return redirect()->back()
                ->with('error', 'EL ESTADO ACTUAL (' . $estadoActual . ') NO PERMITE NINGÚN CAMBIO DE ESTADO.');
        }

        if (!in_array($nuevoEstado, $permitidos)) {
            return redirect()->back()
                ->with('error', 'TRANSICIÓN NO PERMITIDA: ' . $estadoActual . ' → ' . $nuevoEstado . '. DESDE AQUÍ SOLO SE PUEDE PASAR A: ' . implode(', ', $permitidos) . '.');
        }

        $participacion->update([
            'est_pca' => $nuevoEstado,
            'obs_pca' => $request->obs_pca ?? $participacion->obs_pca,
        ]);

        return redirect()->back()
            ->with('success', 'ESTADO DE PARTICIPACIÓN ACTUALIZADO A ' . $nuevoEstado . ' CORRECTAMENTE.');
    }

    public function destroy(ParticipacionCapacitacion $participacion): RedirectResponse
    {
        $this->soloAdmin();

        $capacitacion = $participacion->capacitacion;

        $participacion->delete();

        return redirect()->route('capacitaciones.show', $capacitacion)
            ->with('success', 'PARTICIPACIÓN ELIMINADA CORRECTAMENTE.');
    }

    public function buscarPersonas(Request $request): JsonResponse
    {
        try {
            $termino = trim((string) $request->get('q', ''));

            $query = PersonaNoVidente::where('est_pnv', 'ACTIVO');

            if (!empty($termino)) {
                $query->where(function ($q) use ($termino) {
                    $q->where('nom_pnv', 'like', "%{$termino}%")
                      ->orWhere('ape_pnv', 'like', "%{$termino}%")
                      ->orWhere('cii_pnv', 'like', "%{$termino}%");
                });
            }

            $personas = $query->orderBy('ape_pnv')->limit(15)->get()
                ->map(fn ($p) => [
                    'id'   => $p->cod_personas_no_videntes,
                    'text' => strtoupper("{$p->nom_pnv} {$p->ape_pnv}") . " ({$p->cii_pnv})",
                ])
                ->values();

            return response()->json(['results' => $personas]);

        } catch (\Exception $e) {
            return response()->json(['results' => [], 'error' => 'ERROR AL BUSCAR PERSONAS.'], 500);
        }
    }
}
