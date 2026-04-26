<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluacionRiesgo extends Model
{
    use SoftDeletes;

    protected $table      = 'evaluaciones_riesgo';
    protected $primaryKey = 'cod_evaluaciones_riesgo';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_eri';

    protected $fillable = [
        'cod_calles_tramos_eri',
        'cod_niveles_seguridad_eri',
        'met_eri',
        'obs_eri',
        'cod_usuarios_eri',
        'fee_eri',
    ];

    protected $casts = [
        'fee_eri' => 'date',
        'fcr_eri' => 'datetime',
        'fmo_eri' => 'datetime',
        'fbj_eri' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function tramo(): BelongsTo
    {
        return $this->belongsTo(CalleTramo::class, 'cod_calles_tramos_eri', 'cod_calles_tramos');
    }

    public function nivelSeguridad(): BelongsTo
    {
        return $this->belongsTo(NivelSeguridad::class, 'cod_niveles_seguridad_eri', 'cod_niveles_seguridad');
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_eri', 'cod_usuarios');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function fechaFormateada(): string
    {
        return $this->fee_eri?->format('d/m/Y') ?? '—';
    }
}
