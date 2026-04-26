<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZonaRiesgo extends Model
{
    use SoftDeletes;

    protected $table      = 'zonas_riesgo';
    protected $primaryKey = 'cod_zonas_riesgo';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_zri';

    protected $fillable = [
        'nom_zri',
        'cod_calles_tramos_zri',
        'cod_niveles_seguridad_zri',
        'des_zri',
        'act_zri',
    ];

    protected $casts = [
        'act_zri' => 'boolean',
        'fcr_zri' => 'datetime',
        'fmo_zri' => 'datetime',
        'fbj_zri' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function tramo(): BelongsTo
    {
        return $this->belongsTo(CalleTramo::class, 'cod_calles_tramos_zri', 'cod_calles_tramos');
    }

    public function nivelSeguridad(): BelongsTo
    {
        return $this->belongsTo(NivelSeguridad::class, 'cod_niveles_seguridad_zri', 'cod_niveles_seguridad');
    }
}
