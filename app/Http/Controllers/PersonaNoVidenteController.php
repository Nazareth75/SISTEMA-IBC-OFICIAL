<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaNoVidenteRequest;
use App\Http\Requests\UpdatePersonaNoVidenteRequest;
use App\Models\PersonaNoVidente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PersonaNoVidenteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->get('buscar'));
        $estado = trim((string) $request->get('estado'));
        $grado = trim((string) $request->get('grado'));
        $ciudad = trim((string) $request->get('ciudad'));

        $personas = PersonaNoVidente::query()
            ->activas()
            ->with('tutorLegal')
            ->buscar($buscar)
            ->when($estado !== '', fn ($q) => $q->where('est_pnv', $estado))
            ->when($grado !== '', fn ($q) => $q->where('gdi_pnv', $grado))
            ->when($ciudad !== '', fn ($q) => $q->where('ciu_pnv', $ciudad))
            ->orderBy('cod_personas_no_videntes', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('personas_no_videntes.index', [
            'personas' => $personas,
            'filtros' => compact('buscar', 'estado', 'grado', 'ciudad'),
            'estados' => ['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EGRESADO'],
            'grados' => ['TOTAL', 'PARCIAL', 'BAJA VISIÓN'],
            'ciudades' => ['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO'],
        ]);
    }

    public function create(): View
    {
        return view('personas_no_videntes.create', [
            'generos' => ['MASCULINO', 'FEMENINO', 'OTRO'],
            'grados' => ['TOTAL', 'PARCIAL', 'BAJA VISIÓN'],
            'autonomias' => ['INDEPENDIENTE', 'SEMI-DEPENDIENTE', 'DEPENDIENTE'],
            'ciudades' => ['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO'],
            'extensiones' => ['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD'],
        ]);
    }

    public function store(StorePersonaNoVidenteRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $data['ide_pnv'] = $this->generarCodigo();
            $data['cod_usuarios_pnv'] = auth()->id();
            $data['est_pnv'] = $data['est_pnv'] ?? 'PENDIENTE';

            if ($request->hasFile('fot_pnv')) {
                $data['fot_pnv'] = $request->file('fot_pnv')->store('personas_no_videntes', 'public');
            }

            PersonaNoVidente::create($data);
        });

        return redirect()
            ->route('personas-no-videntes.index')
            ->with('success', 'Persona no vidente registrada correctamente.');
    }

    public function show(PersonaNoVidente $personaNoVidente): View
    {
        $personaNoVidente->load([
            'tutoresContactos' => fn ($q) => $q->activos()->orderByDesc('etl_tco')->orderBy('nom_tco'),
            'tutorLegal',
            'afiliaciones',
            'usuarioRegistro.role',
        ]);

        return view('personas_no_videntes.show', [
            'persona' => $personaNoVidente,
        ]);
    }

    public function edit(PersonaNoVidente $personaNoVidente): View
    {
        return view('personas_no_videntes.edit', [
            'persona' => $personaNoVidente,
            'estados' => ['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EGRESADO'],
            'generos' => ['MASCULINO', 'FEMENINO', 'OTRO'],
            'grados' => ['TOTAL', 'PARCIAL', 'BAJA VISIÓN'],
            'autonomias' => ['INDEPENDIENTE', 'SEMI-DEPENDIENTE', 'DEPENDIENTE'],
            'ciudades' => ['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'CHUQUISACA', 'BENI', 'PANDO'],
            'extensiones' => ['LP', 'CB', 'SC', 'OR', 'PT', 'TJ', 'CH', 'BE', 'PD'],
        ]);
    }

    public function update(UpdatePersonaNoVidenteRequest $request, PersonaNoVidente $personaNoVidente): RedirectResponse
    {
        DB::transaction(function () use ($request, $personaNoVidente) {
            $data = $request->validated();

            if ($request->hasFile('fot_pnv')) {
                if ($personaNoVidente->fot_pnv && Storage::disk('public')->exists($personaNoVidente->fot_pnv)) {
                    Storage::disk('public')->delete($personaNoVidente->fot_pnv);
                }

                $data['fot_pnv'] = $request->file('fot_pnv')->store('personas_no_videntes', 'public');
            }

            $personaNoVidente->update($data);
        });

        return redirect()
            ->route('personas-no-videntes.show', $personaNoVidente)
            ->with('success', 'Persona no vidente actualizada correctamente.');
    }

    public function destroy(PersonaNoVidente $personaNoVidente): RedirectResponse
    {
        if ($personaNoVidente->tieneAfiliacionActiva()) {
            return redirect()
                ->route('personas-no-videntes.index')
                ->with('error', 'No se puede eliminar la persona porque tiene una afiliación activa.');
        }

        $personaNoVidente->update([
            'fbj_pnv' => now(),
        ]);

        return redirect()
            ->route('personas-no-videntes.index')
            ->with('success', 'Persona no vidente eliminada correctamente.');
    }

    private function generarCodigo(): string
    {
        $ultimo = PersonaNoVidente::withTrashedFake()
            ->orderByDesc('cod_personas_no_videntes')
            ->value('ide_pnv');

        if (!$ultimo) {
            return 'PNV-001';
        }

        $numero = (int) str_replace('PNV-', '', $ultimo);

        return 'PNV-' . str_pad((string) ($numero + 1), 3, '0', STR_PAD_LEFT);
    }
}