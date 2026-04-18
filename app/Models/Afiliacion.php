<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_afi', 'cod_usuarios');
    }
}