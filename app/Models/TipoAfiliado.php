<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAfiliado extends Model
{
    protected $table = 'tipos_afiliado';
    protected $primaryKey = 'cod_tipos_afiliado';

    public $timestamps = true;

    const CREATED_AT = 'fcr_taf';
    const UPDATED_AT = 'fmo_taf';

    protected $fillable = [
        'nom_taf',
        'des_taf',
        'act_taf',
        'fbj_taf',
    ];

    protected $casts = [
        'act_taf' => 'boolean',
        'fcr_taf' => 'datetime',
        'fmo_taf' => 'datetime',
        'fbj_taf' => 'datetime',
    ];

    public function afiliaciones(): HasMany
    {
        return $this->hasMany(Afiliacion::class, 'cod_tipos_afiliado_afi', 'cod_tipos_afiliado');
    }

    public function scopeActivos($query)
    {
        return $query->where('act_taf', true)->whereNull('fbj_taf');
    }
}