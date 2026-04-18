<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'cod_usuarios';

    const CREATED_AT = 'fcr_usu';
    const UPDATED_AT = 'fmo_usu';

    protected $fillable = [
        'ide_usu',
        'nom_usu',
        'ape_usu',
        'cii_usu',
        'cor_usu',
        'con_usu',
        'tel_usu',
        'fot_usu',
        'cod_roles_usu',
        'est_usu',
        'tok_usu',
        'uac_usu',
        'fbj_usu',
    ];

    protected $hidden = [
        'con_usu',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'uac_usu' => 'datetime',
            'fcr_usu' => 'datetime',
            'fmo_usu' => 'datetime',
            'fbj_usu' => 'datetime',
        ];
    }

    public function getAuthPassword()
    {
        return $this->con_usu;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'cod_roles_usu', 'cod_roles');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nom_usu} {$this->ape_usu}";
    }

    public function personasRegistradas(): HasMany
    {
        return $this->hasMany(PersonaNoVidente::class, 'cod_usuarios_pnv', 'cod_usuarios');
    }

    public function esAdministrador(): bool
    {
        return strtoupper($this->role?->nom_rol ?? '') === 'ADMINISTRADOR';
    }

    public function esTecnico(): bool
    {
        return strtoupper($this->role?->nom_rol ?? '') === 'TECNICO';
    }

    public function esTutor(): bool
    {
        $rol = strtoupper($this->role?->nom_rol ?? '');
        return in_array($rol, ['TUTOR', 'VOLUNTARIO'], true);
    }

    public function puedeCrearPersonaNoVidente(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEditarPersonaNoVidente(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeCambiarEstadoPersonaNoVidente(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeEliminarPersonaNoVidente(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeGestionarTutores(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }
}