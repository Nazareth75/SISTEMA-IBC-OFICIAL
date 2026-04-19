<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonaNoVidente extends Model
{
    protected $table = 'personas_no_videntes';
    protected $primaryKey = 'cod_personas_no_videntes';

    public $timestamps = true;

    const CREATED_AT = 'fcr_pnv';
    const UPDATED_AT = 'fmo_pnv';

    protected $fillable = [
        'ide_pnv',
        'nom_pnv',
        'ape_pnv',
        'cii_pnv',
        'ext_pnv',
        'fen_pnv',
        'gen_pnv',
        'tel_pnv',
        'cor_pnv',
        'dir_pnv',
        'ciu_pnv',
        'gdi_pnv',
        'cau_pnv',
        'aut_pnv',
        'fot_pnv',
        'est_pnv',
        'obs_pnv',
        'cod_usuarios_pnv',
        'fbj_pnv',
    ];

    protected $casts = [
        'fen_pnv' => 'date',
        'fcr_pnv' => 'datetime',
        'fmo_pnv' => 'datetime',
        'fbj_pnv' => 'datetime',
    ];

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_pnv', 'cod_usuarios');
    }

    public function tutoresContactos(): HasMany
    {
        return $this->hasMany(TutorContacto::class, 'cod_personas_no_videntes_tco', 'cod_personas_no_videntes');
    }

    public function tutorLegal()
    {
        return $this->hasOne(TutorContacto::class, 'cod_personas_no_videntes_tco', 'cod_personas_no_videntes')
            ->where('etl_tco', true)
            ->whereNull('fbj_tco');
    }

    public function afiliaciones(): HasMany
    {
        return $this->hasMany(Afiliacion::class, 'cod_personas_no_videntes_afi', 'cod_personas_no_videntes');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNull('fbj_pnv');
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        if (!$texto) {
            return $query;
        }

        return $query->where(function ($q) use ($texto) {
            $q->where('nom_pnv', 'like', "%{$texto}%")
                ->orWhere('ape_pnv', 'like', "%{$texto}%")
                ->orWhere('cii_pnv', 'like', "%{$texto}%")
                ->orWhere('ide_pnv', 'like', "%{$texto}%");
        });
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nom_pnv} {$this->ape_pnv}");
    }

    public function tieneAfiliacionActiva(): bool
    {
        return $this->afiliaciones()
            ->where('est_afi', 'ACTIVA')
            ->whereNull('fbj_afi')
            ->exists();
    }

    public function puedeSerAfiliada(): bool
    {
        return $this->est_pnv === 'ACTIVO' && is_null($this->fbj_pnv);
    }

    public function scopeWithTrashedFake($query)
    {
        return $query;
    }
}