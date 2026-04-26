<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelSeguridad extends Model
{
    protected $table      = 'niveles_seguridad';
    protected $primaryKey = 'cod_niveles_seguridad';

    const CREATED_AT = 'fcr_nse';
    const UPDATED_AT = 'fmo_nse';

    const CRITICO_NIVEL = 1;

    protected $fillable = [
        'nom_nse',
        'niv_nse',
        'des_nse',
        'col_nse',
    ];

    protected $casts = [
        'niv_nse' => 'integer',
        'fcr_nse' => 'datetime',
        'fmo_nse' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(EvaluacionRiesgo::class, 'cod_niveles_seguridad_eri', 'cod_niveles_seguridad')
            ->whereNull('fbj_eri');
    }

    public function zonas(): HasMany
    {
        return $this->hasMany(ZonaRiesgo::class, 'cod_niveles_seguridad_zri', 'cod_niveles_seguridad')
            ->whereNull('fbj_zri');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function esCritico(): bool
    {
        return $this->niv_nse === self::CRITICO_NIVEL;
    }

    public function colorHex(): string
    {
        return $this->col_nse ?? '#6b7280';
    }
}
