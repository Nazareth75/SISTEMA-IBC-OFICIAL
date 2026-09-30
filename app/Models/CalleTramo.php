<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalleTramo extends Model
{
    use SoftDeletes;

    protected $table      = 'calles_tramos';
    protected $primaryKey = 'cod_calles_tramos';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_ctr';

    const TIPOS = [
        'ACERA'         => 'Acera',
        'CRUCE'         => 'Cruce',
        'PASO PEATONAL' => 'Paso Peatonal',
        'CALLE'         => 'Calle',
        'ESCALERA'      => 'Escalera',
        'RAMPA'         => 'Rampa',
        'OTRO'          => 'Otro',
    ];

    protected $fillable = [
        'cod_rutas_ctr',
        'nom_ctr',
        'tip_ctr',
        'ord_ctr',
        'lon_ctr',
        'act_ctr',
    ];

    protected $casts = [
        'lon_ctr' => 'decimal:2',
        'act_ctr' => 'boolean',
        'fcr_ctr' => 'datetime',
        'fmo_ctr' => 'datetime',
        'fbj_ctr' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'cod_rutas_ctr', 'cod_rutas');
    }

    public function condiciones(): HasMany
    {
        return $this->hasMany(CondicionEntorno::class, 'cod_calles_tramos_cen', 'cod_calles_tramos')
            ->whereNull('fbj_cen');
    }

    public function obstaculos(): HasMany
    {
        return $this->hasMany(Obstaculo::class, 'cod_calles_tramos_obs', 'cod_calles_tramos')
            ->whereNull('fbj_obs');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(EvaluacionRiesgo::class, 'cod_calles_tramos_eri', 'cod_calles_tramos')
            ->whereNull('fbj_eri')
            ->orderByDesc('fee_eri');
    }

    public function zonas(): HasMany
    {
        return $this->hasMany(ZonaRiesgo::class, 'cod_calles_tramos_zri', 'cod_calles_tramos')
            ->whereNull('fbj_zri');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function ultimaEvaluacion(): ?EvaluacionRiesgo
    {
        return $this->evaluaciones()
            ->with('nivelSeguridad')
            ->latest('fee_eri')
            ->first();
    }

    public function obstaculosActivos(): int
    {
        return $this->obstaculos()
            ->whereIn('est_obs', ['REPORTADO', 'ACTIVO'])
            ->count();
    }

    public function perfilRiesgo(): array
    {
        return app(\App\Services\TramoRiskService::class)->obtenerPerfil($this);
    }
}

