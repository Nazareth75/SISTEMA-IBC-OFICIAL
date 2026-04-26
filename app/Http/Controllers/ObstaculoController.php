<?php

namespace App\Http\Controllers;

use App\Models\CalleTramo;
use App\Models\Obstaculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ObstaculoController extends Controller
{
    // ─── Pantalla 4: Gestión de obstáculos ────────────────────

    public function index(Request $request): View
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(), 403);

        $estado = trim((string) $request->get('estado', ''));
        $tipo   = trim((string) $request->get('tipo', ''));
        $buscar = trim((string) $request->get('buscar', ''));

        $obstaculos = Obstaculo::with(['tramo.ruta', 'reportadoPor'])
            ->whereNull('fbj_obs')
            ->when($estado !== '', fn ($q) => $q->where('est_obs', $estado))
            ->when($tipo !== '', fn ($q) => $q->where('tip_obs', $tipo))
            ->when($buscar !== '', fn ($q) => $q->whereHas('tramo', function ($q) use ($buscar) {
                $q->where('nom_ctr', 'like', "%{$buscar}%")
                  ->orWhereHas('ruta', fn ($q2) =>
                      $q2->where('nom_rut', 'like', "%{$buscar}%")
                         ->orWhere('ide_rut', 'like', "%{$buscar}%")
                  );
            }))
            ->orderByDesc('fcr_obs')
            ->paginate(10)
            ->withQueryString();

        return view('obstaculos.index', [
            'obstaculos' => $obstaculos,
            'filtros'    => compact('estado', 'tipo', 'buscar'),
            'estados'    => array_keys(Obstaculo::ESTADOS),
            'tipos'      => array_keys(Obstaculo::TIPOS),
        ]);
    }

    // ─── Reportar obstáculo (desde detalle de ruta) ───────────

    public function store(Request $request, CalleTramo $tramo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(), 403);

        $data = $request->validate([
            'tip_obs' => 'required|in:' . implode(',', array_keys(Obstaculo::TIPOS)),
            'des_obs' => 'required|string|max:250',
            'fot_obs' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'tip_obs.required' => 'EL TIPO DE OBSTÁCULO ES OBLIGATORIO.',
            'tip_obs.in'       => 'EL TIPO SELECCIONADO NO ES VÁLIDO.',
            'des_obs.required' => 'LA DESCRIPCIÓN DEL OBSTÁCULO ES OBLIGATORIA.',
            'des_obs.max'      => 'LA DESCRIPCIÓN NO PUEDE EXCEDER 250 CARACTERES.',
            'fot_obs.image'    => 'EL ARCHIVO DEBE SER UNA IMAGEN.',
            'fot_obs.mimes'    => 'FORMATO PERMITIDO: JPG, JPEG, PNG, WEBP.',
            'fot_obs.max'      => 'LA IMAGEN NO PUEDE SUPERAR 2 MB.',
        ]);

        $data['des_obs']               = strtoupper(trim($data['des_obs']));
        $data['est_obs']               = 'REPORTADO';
        $data['ori_obs']               = $this->resolverOrigen();
        $data['cod_calles_tramos_obs'] = $tramo->getKey();
        $data['cod_usuarios_obs']      = Auth::id();

        if ($request->hasFile('fot_obs')) {
            $data['fot_obs'] = $request->file('fot_obs')
                ->store('uploads/obstaculos', 'public');
        }

        // RN-05 se aplica automáticamente en Obstaculo::boot() → created
        Obstaculo::create($data);

        return redirect()->route('rutas.show', $tramo->ruta)
            ->with('success', 'OBSTÁCULO REPORTADO CORRECTAMENTE. SI LA RUTA ESTABA PUBLICADA, PASÓ A DESACTUALIZADA.');
    }

    // ─── Confirmar obstáculo: REPORTADO → ACTIVO ──────────────

    public function confirmar(Obstaculo $obstaculo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(), 403);

        if ($obstaculo->est_obs !== 'REPORTADO') {
            return redirect()->back()
                ->with('error', 'SOLO SE PUEDEN CONFIRMAR OBSTÁCULOS EN ESTADO REPORTADO.');
        }

        $obstaculo->confirmar();

        return redirect()->back()
            ->with('success', 'OBSTÁCULO CONFIRMADO Y MARCADO COMO ACTIVO.');
    }

    // ─── Resolver obstáculo: → RESUELTO ───────────────────────

    public function resolver(Obstaculo $obstaculo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(), 403);

        if ($obstaculo->est_obs === 'RESUELTO') {
            return redirect()->back()
                ->with('error', 'EL OBSTÁCULO YA ESTÁ MARCADO COMO RESUELTO.');
        }

        $obstaculo->resolver();

        return redirect()->back()
            ->with('success', 'OBSTÁCULO MARCADO COMO RESUELTO.');
    }

    // ─── Rechazar obstáculo: reporte inválido → soft delete ───

    public function rechazar(Obstaculo $obstaculo): RedirectResponse
    {
        abort_unless(Auth::user()?->puedeGestionarObstaculos(), 403);

        if ($obstaculo->est_obs !== 'REPORTADO') {
            return redirect()->back()
                ->with('error', 'SOLO SE PUEDEN RECHAZAR OBSTÁCULOS EN ESTADO REPORTADO.');
        }

        // Eliminar foto si existe
        if ($obstaculo->fot_obs) {
            Storage::disk('public')->delete($obstaculo->fot_obs);
        }

        $obstaculo->delete();

        return redirect()->back()
            ->with('success', 'REPORTE DE OBSTÁCULO RECHAZADO Y ELIMINADO.');
    }

    // ─── Helper privado ───────────────────────────────────────

    private function resolverOrigen(): string
    {
        $user = Auth::user();

        if ($user?->esAdministrador() || $user?->esTecnico()) {
            return 'TECNICO';
        }

        return 'VOLUNTARIO';
    }
}
