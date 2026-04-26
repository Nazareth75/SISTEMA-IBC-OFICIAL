<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoCapacitacion extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_capacitacion';
    protected $primaryKey = 'cod_tipos_capacitacion';
    const DELETED_AT = 'fbj_tca';
    public $timestamps = false;

    protected $fillable = [
        'nom_tca',
        'des_tca',
        'act_tca',
    ];

    protected $casts = [
        'act_tca'  => 'boolean',
        'fcr_tca'  => 'datetime',
        'fmo_tca'  => 'datetime',
        'fbj_tca'  => 'datetime',
    ];

    public function setNomTcaAttribute(string $value): void
    {
        $this->attributes['nom_tca'] = mb_strtoupper(trim($value), 'UTF-8');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('act_tca', true);
    }

    public function scopeBuscador(Builder $query, ?string $texto): Builder
    {
        if (!$texto) {
            return $query;
        }

        return $query->where('nom_tca', 'like', '%' . $texto . '%');
    }

    public function capacitaciones(): HasMany
    {
        return $this->hasMany(
            Capacitacion::class,
            'cod_tipos_capacitacion_cap',
            'cod_tipos_capacitacion'
        );
    }
}
