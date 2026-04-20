<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Afiliacion extends Model
{
    protected $table = 'afiliaciones';
    protected $primaryKey = 'cod_afiliaciones';

    public $timestamps = true;

    const CREATED_AT = 'fcr_afi';
    const UPDATED_AT = 'fmo_afi';

    protected $fillable = [
        'ide_afi',
        'cod_personas_no_videntes_afi',
        'cod_tipos_afiliado_afi',
        'fia_afi',
        'fve_afi',
        'est_afi',
        'obs_afi',
        'cod_usuarios_afi',
        'fbj_afi',
    ];

    protected $casts = [
        'fia_afi' => 'date',
        'fve_afi' => 'date',
        'fcr_afi' => 'datetime',
        'fmo_afi' => 'datetime',
        'fbj_afi' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaNoVidente::class, 'cod_personas_no_videntes_afi', 'cod_personas_no_videntes');
    }

    public function tipoAfiliado(): BelongsTo
    {
        return $this->belongsTo(TipoAfiliado::class, 'cod_tipos_afiliado_afi', 'cod_tipos_afiliado');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_afi', 'cod_usuarios');
    }

    public function asignacionesBeneficios(): HasMany
    {
        return $this->hasMany(
            AsignacionBeneficio::class,
            'cod_afiliaciones_abe',
            'cod_afiliaciones'
        );
    }

    public function tramitesInstituciones(): HasMany
    {
        return $this->hasMany(
            TramiteInstitucion::class,
            'cod_afiliaciones_tin',
            'cod_afiliaciones'
        );
    }

    public function accesosSalud(): HasMany
    {
        return $this->hasMany(
            AccesoSalud::class,
            'cod_afiliaciones_asa',
            'cod_afiliaciones'
        );
    }

    public function scopeActivas($query): Builder
    {
        return $query->where('est_afi', 'ACTIVA')->whereNull('fbj_afi');
    }

    public function scopeNoEliminadas($query): Builder
    {
        return $query->whereNull('fbj_afi');
    }

    public function scopeBuscar($query, ?string $texto): Builder
    {
        if (!$texto) {
            return $query;
        }

        return $query->where(function ($q) use ($texto) {
            $q->where('ide_afi', 'like', "%{$texto}%")
                ->orWhereHas('persona', function ($sub) use ($texto) {
                    $sub->where('nom_pnv', 'like', "%{$texto}%")
                        ->orWhere('ape_pnv', 'like', "%{$texto}%")
                        ->orWhere('cii_pnv', 'like', "%{$texto}%");
                });
        });
    }

    public function actualizarEstadoPorVencimiento(): void
    {
        if (
            $this->fve_afi &&
            $this->est_afi === 'ACTIVA' &&
            Carbon::parse($this->fve_afi)->isPast() &&
            !Carbon::parse($this->fve_afi)->isToday()
        ) {
            $this->updateQuietly([
                'est_afi' => 'VENCIDA',
            ]);
        }
    }
}