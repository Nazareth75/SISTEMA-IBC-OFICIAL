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

    public function tieneRol(...$roles): bool
    {
        $rolUsuario = strtoupper($this->role?->nom_rol ?? '');
        $rolesNormalizados = array_map(static fn ($rol) => strtoupper((string) $rol), $roles);

        // Si el usuario tiene el rol buscado directamente, tiene acceso
        if (in_array($rolUsuario, $rolesNormalizados, true)) {
            return true;
        }

        // SUPERADMINISTRADOR, ADMINISTRADOR y ADMINISTRADOR_NO_VIDENTE son roles de administración con acceso total,
        // pero ADMINISTRADOR/SUPERADMINISTRADOR no deben acceder a funciones exclusivas de ADMINISTRADOR_NO_VIDENTE
        if (in_array($rolUsuario, ['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ADMINISTRADOR_NO_VIDENTE'], true)) {
            if ($rolUsuario === 'ADMINISTRADOR_NO_VIDENTE') {
                return true;
            }
            if (!in_array('ADMINISTRADOR_NO_VIDENTE', $rolesNormalizados, true)) {
                return true;
            }
        }

        return false;
    }

    public function esSuperadministrador(): bool
    {
        return strtoupper($this->role?->nom_rol ?? '') === 'SUPERADMINISTRADOR';
    }

    public function personasRegistradas(): HasMany
    {
        return $this->hasMany(PersonaNoVidente::class, 'cod_usuarios_pnv', 'cod_usuarios');
    }

    public function asignacionesBeneficiosRegistradas(): HasMany
    {
        return $this->hasMany(AsignacionBeneficio::class, 'cod_usuarios_abe', 'cod_usuarios');
    }

    public function tramitesRegistrados(): HasMany
    {
        return $this->hasMany(TramiteInstitucion::class, 'cod_usuarios_tin', 'cod_usuarios');
    }

    public function accesosSaludRegistrados(): HasMany
    {
        return $this->hasMany(AccesoSalud::class, 'cod_usuarios_asa', 'cod_usuarios');
    }

    public function capacitacionesResponsable(): HasMany
    {
        return $this->hasMany(
            Capacitacion::class,
            'cod_usuarios_cap',
            'cod_usuarios'
        );
    }

    public function esAdministrador(): bool
    {
        return $this->tieneRol('ADMINISTRADOR');
    }

    public function esTecnico(): bool
    {
        return $this->tieneRol('TECNICO');
    }

    public function esVoluntario(): bool
    {
        return strtoupper($this->role?->nom_rol ?? '') === 'VOLUNTARIO';
    }


    public function puedeGestionarUsuarios(): bool
    {
        return $this->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR');
    }

    public function puedeGestionarRutas(): bool
    {
        return $this->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO');
    }

    public function puedeValidarObstaculos(): bool
    {
        return $this->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO');
    }

    public function puedeReportarObstaculos(): bool
    {
        return $this->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO', 'VOLUNTARIO');
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

    public function puedeCrearAfiliacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEditarAfiliacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEliminarAfiliacion(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeGestionarTiposAfiliado(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeCrearCapacitacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEditarCapacitacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEliminarCapacitacion(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeGestionarTiposCapacitacion(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeInscribirParticipanteCapacitacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEditarParticipacionCapacitacion(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEliminarParticipacionCapacitacion(): bool
    {
        return $this->esAdministrador();
    }

    // ─── Permisos M7 — Rutas Seguras ─────────────────────────

    public function puedeCrearRuta(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEditarRuta(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeEnviarRutaARevision(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeAprobarRuta(): bool
    {
        return $this->esAdministrador();
    }

    public function puedePublicarRuta(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeArchivarRuta(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeEliminarRuta(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeGestionarObstaculos(): bool
    {
        return $this->esAdministrador() || $this->esTecnico();
    }

    public function puedeGestionarNivelesSeguridad(): bool
    {
        return $this->esAdministrador();
    }

    public function puedeVerificarObstaculos(): bool
    {
        return $this->esVoluntario() || $this->esTecnico() || $this->esAdministrador();
    }
}