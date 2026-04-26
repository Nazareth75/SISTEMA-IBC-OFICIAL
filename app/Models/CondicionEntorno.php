<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicionEntorno extends Model
{
    use SoftDeletes;

    protected $table      = 'condiciones_entorno';
    protected $primaryKey = 'cod_condiciones_entorno';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_cen';

    const TIPOS = [
        'PAVIMENTO'    => 'Pavimento',
        'ILUMINACIÓN'  => 'Iluminación',
        'SEÑALÉTICA'   => 'Señalética',
        'ANCHO VEREDA' => 'Ancho de Vereda',
        'PENDIENTE'    => 'Pendiente',
        'OTRO'         => 'Otro',
    ];

    const NIVELES = [
        'BUENO'   => 'Bueno',
        'REGULAR' => 'Regular',
        'MALO'    => 'Malo',
    ];

    const COLORES_NIVEL = [
        'BUENO'   => 'bg-green-100 text-green-800',
        'REGULAR' => 'bg-yellow-100 text-yellow-800',
        'MALO'    => 'bg-red-100 text-red-800',
    ];

    protected $fillable = [
        'cod_calles_tramos_cen',
        'tip_cen',
        'des_cen',
        'niv_cen',
    ];

    protected $casts = [
        'fcr_cen' => 'datetime',
        'fmo_cen' => 'datetime',
        'fbj_cen' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function tramo(): BelongsTo
    {
        return $this->belongsTo(CalleTramo::class, 'cod_calles_tramos_cen', 'cod_calles_tramos');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function colorNivel(): string
    {
        return self::COLORES_NIVEL[$this->niv_cen] ?? 'bg-gray-100 text-gray-800';
    }
}
