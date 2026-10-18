<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obstaculo extends Model
{
    use SoftDeletes;

    protected $table      = 'obstaculos';
    protected $primaryKey = 'cod_obstaculos';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_obs';

    const TIPOS = [
        'POSTE'    => 'Poste',
        'ESCALÓN'  => 'Escalón',
        'OBRA'     => 'Obra',
        'VEHÍCULO' => 'Vehículo',
        'BACHE'    => 'Bache',
        'VENDEDOR' => 'Vendedor',
        'OTRO'     => 'Otro',
    ];

    const ESTADOS = [
        'REPORTADO' => 'Reportado',
        'ACTIVO'    => 'Activo',
        'RESUELTO'  => 'Resuelto',
    ];

    const ORIGENES = [
        'TECNICO'    => 'Técnico',
        'VOLUNTARIO' => 'Voluntario',
        'AFILIADO'   => 'Afiliado',
    ];

    const COLORES_ESTADO = [
        'REPORTADO' => 'bg-yellow-100 text-yellow-800',
        'ACTIVO'    => 'bg-red-100 text-red-800',
        'RESUELTO'  => 'bg-green-100 text-green-800',
    ];

    protected $fillable = [
        'cod_calles_tramos_obs',
        'tip_obs',
        'des_obs',
        'est_obs',
        'ori_obs',
        'cod_usuarios_obs',
        'fot_obs',
    ];

    protected $casts = [
        'fcr_obs' => 'datetime',
        'fmo_obs' => 'datetime',
        'fbj_obs' => 'datetime',
    ];

    // ─── RN-05: desactualizar rutas al reportar obstáculo ────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Obstaculo $obstaculo) {
            $tramo = $obstaculo->tramo;
            if (!$tramo) {
                return;
            }

            // Obtener todas las rutas asociadas a través de la pivote
            $rutas = $tramo->rutas;

            // Soporte fallback por si existe relación legacy
            if ($rutas->isEmpty() && $tramo->ruta) {
                $rutas = collect([$tramo->ruta]);
            }

            foreach ($rutas as $ruta) {
                if ($ruta->est_rut === 'PUBLICADA') {
                    $ruta->desactualizar();
                }
            }

            event(new \App\Events\ObstaculoCambioEstado(
                $obstaculo,
                '',
                (string) $obstaculo->est_obs,
                $obstaculo->cod_usuarios_obs
            ));
        });

        static::updated(function (Obstaculo $obstaculo) {
            if ($obstaculo->wasChanged('est_obs')) {
                event(new \App\Events\ObstaculoCambioEstado(
                    $obstaculo,
                    (string) $obstaculo->getOriginal('est_obs'),
                    (string) $obstaculo->est_obs,
                    \Illuminate\Support\Facades\Auth::id()
                ));
            }
        });

        static::deleted(function (Obstaculo $obstaculo) {
            event(new \App\Events\ObstaculoCambioEstado(
                $obstaculo,
                (string) $obstaculo->est_obs,
                'ELIMINADO',
                \Illuminate\Support\Facades\Auth::id()
            ));
        });
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function tramo(): BelongsTo
    {
        return $this->belongsTo(CalleTramo::class, 'cod_calles_tramos_obs', 'cod_calles_tramos');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_obs', 'cod_usuarios');
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(ReporteObstaculo::class, 'cod_obstaculos_rpo', 'cod_obstaculos');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function colorEstado(): string
    {
        return self::COLORES_ESTADO[$this->est_obs] ?? 'bg-gray-100 text-gray-800';
    }

    public function confirmar(): void
    {
        $this->update(['est_obs' => 'ACTIVO']);
    }

    public function resolver(): void
    {
        $this->update(['est_obs' => 'RESUELTO']);
    }
}
