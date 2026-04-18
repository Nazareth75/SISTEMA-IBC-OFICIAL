<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'cod_roles';

    const CREATED_AT = 'fcr_rol';
    const UPDATED_AT = 'fmo_rol';

    protected $fillable = [
        'nom_rol',
        'des_rol',
        'act_rol',
    ];

    protected $casts = [
        'act_rol' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'cod_roles_usu', 'cod_roles');
    }
}