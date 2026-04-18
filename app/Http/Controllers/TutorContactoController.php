<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTutorContactoRequest;
use App\Http\Requests\UpdateTutorContactoRequest;
use App\Models\PersonaNoVidente;
use App\Models\TutorContacto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TutorContactoController extends Controller
{
    public function index(PersonaNoVidente $personaNoVidente): View
    {
        $personaNoVidente->load([
            'tutoresContactos' => fn ($q) => $q->orderByDesc('etl_tco')
                ->orderByRaw('CASE WHEN fbj_tco IS NULL THEN 0 ELSE 1 END')
                ->orderByDesc('cod_tutores_contactos'),
        ]);

        return view('tutores_contactos.index', [
            'persona' => $personaNoVidente,
        ]);
    }

    public function create(PersonaNoVidente $personaNoVidente): View
    {
        return view('tutores_contactos.create', [
            'persona' => $personaNoVidente,
        ]);
    }

    public function store(StoreTutorContactoRequest $request, PersonaNoVidente $personaNoVidente): RedirectResponse
    {
        DB::transaction(function () use ($request, $personaNoVidente) {
            $data = $request->validated();
            $data['cod_personas_no_videntes_tco'] = $personaNoVidente->cod_personas_no_videntes;

            if (($data['etl_tco'] ?? false) === true) {
                TutorContacto::where('cod_personas_no_videntes_tco', $personaNoVidente->cod_personas_no_videntes)
                    ->whereNull('fbj_tco')
                    ->update(['etl_tco' => false]);
            }

            TutorContacto::create($data);
        });

        return redirect()
            ->route('personas-no-videntes.tutores.index', $personaNoVidente)
            ->with('success', 'Tutor/contacto registrado correctamente.');
    }

    public function edit(PersonaNoVidente $personaNoVidente, TutorContacto $tutore): View
    {
        abort_unless(
            $tutore->cod_personas_no_videntes_tco === $personaNoVidente->cod_personas_no_videntes,
            404
        );

        return view('tutores_contactos.edit', [
            'persona' => $personaNoVidente,
            'tutor' => $tutore,
        ]);
    }

    public function update(
        UpdateTutorContactoRequest $request,
        PersonaNoVidente $personaNoVidente,
        TutorContacto $tutore
    ): RedirectResponse {
        abort_unless(
            $tutore->cod_personas_no_videntes_tco === $personaNoVidente->cod_personas_no_videntes,
            404
        );

        DB::transaction(function () use ($request, $personaNoVidente, $tutore) {
            $data = $request->validated();

            if (($data['etl_tco'] ?? false) === true) {
                TutorContacto::where('cod_personas_no_videntes_tco', $personaNoVidente->cod_personas_no_videntes)
                    ->where('cod_tutores_contactos', '!=', $tutore->cod_tutores_contactos)
                    ->whereNull('fbj_tco')
                    ->update(['etl_tco' => false]);
            }

            $tutore->update($data);
        });

        return redirect()
            ->route('personas-no-videntes.tutores.index', $personaNoVidente)
            ->with('success', 'Tutor/contacto actualizado correctamente.');
    }

    public function destroy(PersonaNoVidente $personaNoVidente, TutorContacto $tutore): RedirectResponse
    {
        abort_unless(
            $tutore->cod_personas_no_videntes_tco === $personaNoVidente->cod_personas_no_videntes,
            404
        );

        $tutore->update([
            'fbj_tco' => now(),
            'etl_tco' => false,
        ]);

        return redirect()
            ->route('personas-no-videntes.tutores.index', $personaNoVidente)
            ->with('success', 'Tutor/contacto eliminado correctamente.');
    }
}