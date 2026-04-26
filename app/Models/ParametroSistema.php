<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametroSistema extends Model
{
    protected $table      = 'parametros_sistema';
    protected $primaryKey = 'cod_parametros_sistema';

    const CREATED_AT = 'fcr_psi';
    const UPDATED_AT = 'fmo_psi';

    protected $fillable = [
        'cla_psi',
        'val_psi',
        'des_psi',
        'tip_psi',
        'edi_psi',
    ];

    protected $casts = [
        'edi_psi' => 'boolean',
        'fcr_psi' => 'datetime',
        'fmo_psi' => 'datetime',
    ];

    public static function valor(string $clave, mixed $defecto = null): mixed
    {
        $parametro = static::where('cla_psi', $clave)->first();
        return $parametro?->val_psi ?? $defecto;
    }
}
