<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\TipoCapacitacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CapacitacionController extends Controller
{
    private const TRANSICIONES = [
        'PLANIFICADA' => ['EN CURSO', 'CANCELADA'],
        'EN CURSO'    => ['FINALIZADA', 'CANCELADA'],
    ];

    private function soloAdmin(): void
    {
        if (!auth()->user()?->esAdministrador()) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->soloAdmin();

        $query = Capacitacion::with([
            'tipoCapacitacion',
            'responsable',
        ]);

        if ($request->filled('buscar')) {
            $query->buscador($request->get('buscar'));
        }

        if ($request->filled('tipo')) {
            $query->where('cod_tipos_capacitacion_cap', $request->get('tipo'));
        }

        if ($request->filled('estado')) {
            $query->porEstado($request->get('estado'));
        }

        $capacitaciones = $query
            ->orderBy('fcr_cap', 'desc')
            ->paginate(10)
            ->withQueryString();

        $tipos = TipoCapacitacion::activos()
            ->orderBy('nom_tca')
            ->get();

        $estados = Capacitacion::ESTADOS;

        return view('capacitaciones.index', compact(
            'capacitaciones',
            'tipos',
            'estados'
        ));
    }

    public function create(): View
    {
        $this->soloAdmin();

        $tipos = TipoCapacitacion::activos()
            ->orderBy('nom_tca')
            ->get();

        $usuarios = User::where('est_usu', 'ACTIVO')
            ->whereHas('role', function ($query) {
                $query->whereIn('nom_rol', ['ADMINISTRADOR', 'TECNICO']);
            })
            ->orderBy('ape_usu')
            ->orderBy('nom_usu')
            ->get();

        $estados = Capacitacion::ESTADOS;

        return view('capacitaciones.create', compact(
            'tipos',
            'usuarios',
            'estados'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'nom_cap'                    => 'required|string|max:150',
            'obj_cap'                    => 'nullable|string|max:300',
            'cod_tipos_capacitacion_cap' => 'required|integer|exists:tipos_capacitacion,cod_tipos_capacitacion',
            'fei_cap'                    => 'nullable|date|date_format:Y-m-d',
            'fef_cap'                    => 'nullable|date|date_format:Y-m-d|after_or_equal:fei_cap',
            'lug_cap'                    => 'nullable|string|max:200',
            'cod_usuarios_cap'           => 'nullable|integer|exists:usuarios,cod_usuarios',
        ], [
            'nom_cap.required'                    => 'EL NOMBRE DE LA CAPACITACIÓN ES OBLIGATORIO.',
            'nom_cap.max'                         => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'obj_cap.max'                         => 'EL OBJETIVO NO PUEDE EXCEDER 300 CARACTERES.',
            'cod_tipos_capacitacion_cap.required' => 'DEBE SELECCIONAR UN TIPO DE CAPACITACIÓN.',
            'cod_tipos_capacitacion_cap.exists'   => 'EL TIPO DE CAPACITACIÓN SELECCIONADO NO ES VÁLIDO.',
            'fei_cap.date_format'                 => 'LA FECHA DE INICIO DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.date_format'                 => 'LA FECHA DE FIN DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.after_or_equal'              => 'LA FECHA DE FIN DEBE SER IGUAL O POSTERIOR A LA FECHA DE INICIO.',
            'lug_cap.max'                         => 'EL LUGAR NO PUEDE EXCEDER 200 CARACTERES.',
            'cod_usuarios_cap.exists'             => 'EL RESPONSABLE SELECCIONADO NO ES VÁLIDO.',
        ]);

        $tipo = TipoCapacitacion::findOrFail($request->cod_tipos_capacitacion_cap);

        if (!$tipo->act_tca) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'NO SE PUEDE USAR UN TIPO DE CAPACITACIÓN INACTIVO.');
        }

        Capacitacion::create([
            'ide_cap'                    => Capacitacion::generarCodigo(),
            'nom_cap'                    => $request->nom_cap,
            'obj_cap'                    => $request->obj_cap,
            'cod_tipos_capacitacion_cap' => $request->cod_tipos_capacitacion_cap,
            'fei_cap'                    => $request->fei_cap,
            'fef_cap'                    => $request->fef_cap,
            'lug_cap'                    => $request->lug_cap,
            'cod_usuarios_cap'           => $request->cod_usuarios_cap ?? Auth::id(),
            'est_cap'                    => 'PLANIFICADA',
        ]);

        return redirect()
            ->route('capacitaciones.index')
            ->with('success', 'CAPACITACIÓN REGISTRADA CORRECTAMENTE.');
    }

    public function show(Capacitacion $capacitacion): View
    {
        $this->soloAdmin();

        $capacitacion->load([
            'tipoCapacitacion',
            'responsable',
            'participaciones' => fn ($query) => $query
                ->with('persona')
                ->orderBy('fcr_pca', 'desc'),
        ]);

        return view('capacitaciones.show', compact('capacitacion'));
    }

    public function edit(Capacitacion $capacitacion): View
    {
        $this->soloAdmin();

        $tipos = TipoCapacitacion::activos()
            ->orderBy('nom_tca')
            ->get();

        $usuarios = User::where('est_usu', 'ACTIVO')
            ->whereHas('role', function ($query) {
                $query->whereIn('nom_rol', ['ADMINISTRADOR', 'TECNICO']);
            })
            ->orderBy('ape_usu')
            ->orderBy('nom_usu')
            ->get();

        $estados = Capacitacion::ESTADOS;

        return view('capacitaciones.edit', compact(
            'capacitacion',
            'tipos',
            'usuarios',
            'estados'
        ));
    }

    public function update(Request $request, Capacitacion $capacitacion): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'nom_cap'                    => 'required|string|max:150',
            'obj_cap'                    => 'nullable|string|max:300',
            'cod_tipos_capacitacion_cap' => 'required|integer|exists:tipos_capacitacion,cod_tipos_capacitacion',
            'fei_cap'                    => 'nullable|date|date_format:Y-m-d',
            'fef_cap'                    => 'nullable|date|date_format:Y-m-d|after_or_equal:fei_cap',
            'lug_cap'                    => 'nullable|string|max:200',
            'cod_usuarios_cap'           => 'nullable|integer|exists:usuarios,cod_usuarios',
        ], [
            'nom_cap.required'                    => 'EL NOMBRE DE LA CAPACITACIÓN ES OBLIGATORIO.',
            'nom_cap.max'                         => 'EL NOMBRE NO PUEDE EXCEDER 150 CARACTERES.',
            'obj_cap.max'                         => 'EL OBJETIVO NO PUEDE EXCEDER 300 CARACTERES.',
            'cod_tipos_capacitacion_cap.required' => 'DEBE SELECCIONAR UN TIPO DE CAPACITACIÓN.',
            'cod_tipos_capacitacion_cap.exists'   => 'EL TIPO DE CAPACITACIÓN SELECCIONADO NO ES VÁLIDO.',
            'fei_cap.date_format'                 => 'LA FECHA DE INICIO DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.date_format'                 => 'LA FECHA DE FIN DEBE TENER FORMATO YYYY-MM-DD.',
            'fef_cap.after_or_equal'              => 'LA FECHA DE FIN DEBE SER IGUAL O POSTERIOR A LA FECHA DE INICIO.',
            'lug_cap.max'                         => 'EL LUGAR NO PUEDE EXCEDER 200 CARACTERES.',
            'cod_usuarios_cap.exists'             => 'EL RESPONSABLE SELECCIONADO NO ES VÁLIDO.',
        ]);

        $tipo = TipoCapacitacion::findOrFail($request->cod_tipos_capacitacion_cap);

        if (!$tipo->act_tca) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'NO SE PUEDE USAR UN TIPO DE CAPACITACIÓN INACTIVO.');
        }

        $capacitacion->update([
            'nom_cap'                    => $request->nom_cap,
            'obj_cap'                    => $request->obj_cap,
            'cod_tipos_capacitacion_cap' => $request->cod_tipos_capacitacion_cap,
            'fei_cap'                    => $request->fei_cap,
            'fef_cap'                    => $request->fef_cap,
            'lug_cap'                    => $request->lug_cap,
            'cod_usuarios_cap'           => $request->cod_usuarios_cap,
        ]);

        return redirect()
            ->route('capacitaciones.index')
            ->with('success', 'CAPACITACIÓN ACTUALIZADA CORRECTAMENTE.');
    }

    public function destroy(Capacitacion $capacitacion): RedirectResponse
    {
        $this->soloAdmin();

        if ($capacitacion->participaciones()->activas()->exists()) {
            return redirect()
                ->route('capacitaciones.index')
                ->with('error', 'NO SE PUEDE ELIMINAR: LA CAPACITACIÓN TIENE PARTICIPACIONES ACTIVAS (INSCRITO O EN CURSO).');
        }

        $capacitacion->delete();

        return redirect()
            ->route('capacitaciones.index')
            ->with('success', 'CAPACITACIÓN ELIMINADA CORRECTAMENTE.');
    }

    public function cambiarEstado(Request $request, Capacitacion $capacitacion): RedirectResponse
    {
        $this->soloAdmin();

        $request->validate([
            'nuevo_estado' => 'required|in:' . implode(',', array_keys(Capacitacion::ESTADOS)),
        ], [
            'nuevo_estado.required' => 'DEBE INDICAR EL NUEVO ESTADO.',
            'nuevo_estado.in'       => 'EL ESTADO INDICADO NO ES VÁLIDO.',
        ]);

        $estadoActual = $capacitacion->est_cap;
        $nuevoEstado  = $request->get('nuevo_estado');
        $permitidos   = self::TRANSICIONES[$estadoActual] ?? [];

        if (empty($permitidos)) {
            return redirect()
                ->back()
                ->with('error', 'EL ESTADO ACTUAL (' . $estadoActual . ') NO PERMITE CAMBIOS DE ESTADO.');
        }

        if (!in_array($nuevoEstado, $permitidos, true)) {
            return redirect()
                ->back()
                ->with('error', 'TRANSICIÓN NO PERMITIDA: ' . $estadoActual . ' → ' . $nuevoEstado . '. ESTADOS VÁLIDOS: ' . implode(', ', $permitidos) . '.');
        }

        $capacitacion->update([
            'est_cap' => $nuevoEstado,
        ]);

        return redirect()
            ->back()
            ->with('success', 'ESTADO ACTUALIZADO DE ' . $estadoActual . ' A ' . $nuevoEstado . ' CORRECTAMENTE.');
    }
}