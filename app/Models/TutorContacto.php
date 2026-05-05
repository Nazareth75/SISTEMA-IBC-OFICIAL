<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorContacto extends Model
{
    protected $table = 'tutores_contactos';
    protected $primaryKey = 'cod_tutores_contactos';

    public $timestamps = true;

    const CREATED_AT = 'fcr_tco';
    const UPDATED_AT = 'fmo_tco';

    protected $fillable = [
        'cod_personas_no_videntes_tco',
        'nom_tco',
        'app_tco',
        'apm_tco',
        'ape_tco',
        'par_tco',
        'tel_tco',
        'cor_tco',
        'etl_tco',
        'fbj_tco',
    ];

    protected $casts = [
        'etl_tco' => 'boolean',
        'fcr_tco' => 'datetime',
        'fmo_tco' => 'datetime',
        'fbj_tco' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaNoVidente::class, 'cod_personas_no_videntes_tco', 'cod_personas_no_videntes');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNull('fbj_tco');
    }

    public function getNombreCompletoAttribute(): string
    {
        $apellidos = trim((string) $this->app_tco . ' ' . (string) $this->apm_tco);
        if ($apellidos === '') {
            $apellidos = (string) $this->ape_tco;
        }

        return trim((string) $this->nom_tco . ' ' . $apellidos);
    }
}