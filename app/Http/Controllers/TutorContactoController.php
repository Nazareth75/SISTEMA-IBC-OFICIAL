<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTutorContactoRequest;
use App\Http\Requests\UpdateTutorContactoRequest;
use App\Models\PersonaNoVidente;
use App\Models\TutorContacto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TutorContactoController extends Controller
{
    public function index(PersonaNoVidente $personaNoVidente): View
    {
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
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
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
        return view('tutores_contactos.create', [
            'persona' => $personaNoVidente,
        ]);
    }

    public function store(StoreTutorContactoRequest $request, PersonaNoVidente $personaNoVidente): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
        DB::transaction(function () use ($request, $personaNoVidente) {
            $data = $request->validated();
            $data['ape_tco'] = trim((string) ($data['app_tco'] ?? '') . ' ' . (string) ($data['apm_tco'] ?? ''));
            $data['cod_personas_no_videntes_tco'] = $personaNoVidente->cod_personas_no_videntes;

            if (($data['etl_tco'] ?? false) === true) {
                TutorContacto::where('cod_personas_no_videntes_tco', $personaNoVidente->cod_personas_no_videntes)
                    ->whereNull('fbj_tco')
                    ->update(['etl_tco' => false]);

                if ($request->hasFile('doc_ci_tco')) {
                    $data['doc_ci_tco'] = $request->file('doc_ci_tco')->store('tutores/documentos', 'public');
                }
                if ($request->hasFile('doc_legal_tco')) {
                    $data['doc_legal_tco'] = $request->file('doc_legal_tco')->store('tutores/documentos', 'public');
                }
            } else {
                $data['doc_ci_tco'] = null;
                $data['doc_legal_tco'] = null;
            }

            TutorContacto::create($data);
        });

        return redirect()
            ->route('personas-no-videntes.tutores.index', $personaNoVidente)
            ->with('success', 'Tutor/contacto registrado correctamente.');
    }

    public function edit(PersonaNoVidente $personaNoVidente, TutorContacto $tutore): View
    {
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
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

        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
        DB::transaction(function () use ($request, $personaNoVidente, $tutore) {
            $data = $request->validated();
            $data['ape_tco'] = trim((string) ($data['app_tco'] ?? '') . ' ' . (string) ($data['apm_tco'] ?? ''));

            if (($data['etl_tco'] ?? false) === true) {
                TutorContacto::where('cod_personas_no_videntes_tco', $personaNoVidente->cod_personas_no_videntes)
                    ->where('cod_tutores_contactos', '!=', $tutore->cod_tutores_contactos)
                    ->whereNull('fbj_tco')
                    ->update(['etl_tco' => false]);

                if ($request->hasFile('doc_ci_tco')) {
                    if ($tutore->doc_ci_tco) {
                        Storage::disk('public')->delete($tutore->doc_ci_tco);
                    }
                    $data['doc_ci_tco'] = $request->file('doc_ci_tco')->store('tutores/documentos', 'public');
                }
                if ($request->hasFile('doc_legal_tco')) {
                    if ($tutore->doc_legal_tco) {
                        Storage::disk('public')->delete($tutore->doc_legal_tco);
                    }
                    $data['doc_legal_tco'] = $request->file('doc_legal_tco')->store('tutores/documentos', 'public');
                }
            } else {
                if ($tutore->doc_ci_tco) {
                    Storage::disk('public')->delete($tutore->doc_ci_tco);
                }
                if ($tutore->doc_legal_tco) {
                    Storage::disk('public')->delete($tutore->doc_legal_tco);
                }
                $data['doc_ci_tco'] = null;
                $data['doc_legal_tco'] = null;
            }

            $tutore->update($data);
        });

        return redirect()
            ->route('personas-no-videntes.tutores.index', $personaNoVidente)
            ->with('success', 'Tutor/contacto actualizado correctamente.');
    }

    public function destroy(PersonaNoVidente $personaNoVidente, TutorContacto $tutore): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);
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

    public function descargarDocumentoBase(PersonaNoVidente $personaNoVidente): View
    {
        abort_unless(Auth::user()?->puedeGestionarTutores(), 403);

        $tutor = null;
        if (request()->filled('tutor_id')) {
            $tutor = TutorContacto::find(request()->get('tutor_id'));
        }

        return view('tutores_contactos.documento_base', [
            'persona' => $personaNoVidente,
            'tutor' => $tutor,
        ]);
    }
}