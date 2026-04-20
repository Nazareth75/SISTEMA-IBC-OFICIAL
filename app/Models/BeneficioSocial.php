<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeneficioSocial extends Model
{
    protected $table = 'beneficios_sociales';
    protected $primaryKey = 'cod_beneficios_sociales';

    public $timestamps = true;

    const CREATED_AT = 'fcr_bso';
    const UPDATED_AT = 'fmo_bso';

    protected $fillable = [
        'nom_bso',
        'tip_bso',
        'des_bso',
        'act_bso',
        'fbj_bso',
    ];

    protected $casts = [
        'act_bso' => 'boolean',
        'fcr_bso' => 'datetime',
        'fmo_bso' => 'datetime',
        'fbj_bso' => 'datetime',
    ];

    /**
     * Tipos de beneficios disponibles
     */
    public const TIPOS = [
        'ECONÓMICO' => 'Económico',
        'MATERIAL' => 'Material',
        'SERVICIO' => 'Servicio',
        'EDUCATIVO' => 'Educativo',
        'OTRO' => 'Otro',
    ];

    /**
     * Relación: Un beneficio tiene muchas asignaciones
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(
            AsignacionBeneficio::class,
            'cod_beneficios_sociales_abe',
            'cod_beneficios_sociales'
        );
    }

    /**
     * Scope: Filtrar beneficios activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('act_bso', true)
            ->whereNull('fbj_bso');
    }

    /**
     * Scope: Filtrar beneficios inactivos
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('act_bso', false);
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopePorTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tip_bso', $tipo);
    }

    /**
     * Scope: Buscar por nombre
     */
    public function scopeBuscador(Builder $query, string $termino): Builder
    {
        return $query->where('nom_bso', 'like', '%' . $termino . '%')
            ->orWhere('des_bso', 'like', '%' . $termino . '%');
    }

    /**
     * Verificar si el beneficio está activo
     */
    public function estaActivo(): bool
    {
        return $this->act_bso && is_null($this->fbj_bso);
    }

    /**
     * Verificar si el beneficio está dado de baja
     */
    public function estaBaja(): bool
    {
        return !is_null($this->fbj_bso);
    }

    /**
     * Obtener el nombre del tipo en formato legible
     */
    public function getNombreTipo(): string
    {
        return self::TIPOS[$this->tip_bso] ?? 'Desconocido';
    }

    /**
     * Contar asignaciones activas de este beneficio
     */
    public function contarAsignacionesActivas(): int
    {
        return $this->asignaciones()
            ->where('est_abe', 'ACTIVO')
            ->count();
    }

    /**
     * Desactivar el beneficio
     */
    public function desactivar(): bool
    {
        return $this->update(['act_bso' => false]);
    }

    /**
     * Activar el beneficio
     */
    public function activar(): bool
    {
        return $this->update(['act_bso' => true, 'fbj_bso' => null]);
    }

    /**
     * Dar de baja el beneficio
     */
    public function darDeBaja(): bool
    {
        return $this->update(['fbj_bso' => now()]);
    }
}
