<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstitucionExterna extends Model
{
    protected $table = 'instituciones_externas';
    protected $primaryKey = 'cod_instituciones_externas';
    public $timestamps = false;

    protected $fillable = [
        'nom_iex',
        'tip_iex',
        'ciu_iex',
        'tel_iex',
        'cor_iex',
        'act_iex',
    ];

    protected $casts = [
        'act_iex' => 'boolean',
        'fcr_iex' => 'datetime',
        'fmo_iex' => 'datetime',
        'fbj_iex' => 'datetime',
    ];

    // Constantes de tipos
    const TIPOS = [
        'HOSPITAL' => 'Hospital',
        'CLÍNICA' => 'Clínica',
        'CENTRO DE SALUD' => 'Centro de Salud',
        'SEDE LEGAL' => 'Sede Legal',
        'OFICINA ADMINISTRATIVA' => 'Oficina Administrativa',
        'OTRO' => 'Otro',
    ];

    /**
     * Relación: Una institución tiene muchos trámites
     */
    public function tramites(): HasMany
    {
        return $this->hasMany(
            TramiteInstitucion::class,
            'cod_instituciones_externas_tin',
            'cod_instituciones_externas'
        );
    }

    /**
     * Relación: Una institución tiene muchos accesos de salud
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(
            AccesoSalud::class,
            'cod_instituciones_externas_asa',
            'cod_instituciones_externas'
        );
    }

    /**
     * Scope: Instituciones activas
     */
    public function scopeActivas($query): Builder
    {
        return $query->where('act_iex', true)->whereNull('fbj_iex');
    }

    /**
     * Scope: Instituciones inactivas
     */
    public function scopeInactivas($query): Builder
    {
        return $query->where('act_iex', false);
    }

    /**
     * Scope: Instituciones no eliminadas
     */
    public function scopeNoEliminadas($query): Builder
    {
        return $query->whereNull('fbj_iex');
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopePorTipo($query, string $tipo): Builder
    {
        return $query->where('tip_iex', $tipo);
    }

    /**
     * Scope: Buscador por nombre o ciudad
     */
    public function scopeBuscador($query, ?string $termino): Builder
    {
        if (!$termino) {
            return $query;
        }

        return $query->where(function ($q) use ($termino) {
            $q->where('nom_iex', 'like', "%{$termino}%")
                ->orWhere('ciu_iex', 'like', "%{$termino}%");
        });
    }

    /**
     * Obtener nombre del tipo en formato legible
     */
    public function getNombreTipo(): string
    {
        return self::TIPOS[$this->tip_iex] ?? $this->tip_iex;
    }

    /**
     * Verificar si la institución está activa
     */
    public function estaActiva(): bool
    {
        return $this->act_iex && !$this->fbj_iex;
    }

    /**
     * Verificar si la institución está eliminada (borrado lógico)
     */
    public function estaEliminada(): bool
    {
        return !is_null($this->fbj_iex);
    }

    /**
     * Contar trámites activos (no resueltos, no rechazados, no abandonados)
     */
    public function contarTramitesActivos(): int
    {
        return $this->tramites()
            ->whereIn('est_tin', ['PENDIENTE', 'EN PROCESO'])
            ->whereNull('fbj_tin')
            ->count();
    }

    /**
     * Contar trámites resueltos
     */
    public function contarTramitesResueltos(): int
    {
        return $this->tramites()
            ->where('est_tin', 'RESUELTO')
            ->whereNull('fbj_tin')
            ->count();
    }

    /**
     * Contar trámites rechazados
     */
    public function contarTramitesRechazados(): int
    {
        return $this->tramites()
            ->where('est_tin', 'RECHAZADO')
            ->whereNull('fbj_tin')
            ->count();
    }

    /**
     * Activar institución
     */
    public function activar(): void
    {
        $this->update([
            'act_iex' => true,
            'fbj_iex' => null,
        ]);
    }

    /**
     * Desactivar institución
     */
    public function desactivar(): void
    {
        $this->update([
            'act_iex' => false,
        ]);
    }

    /**
     * Dar de baja (borrado lógico)
     */
    public function darDeBaja(): void
    {
        $this->update([
            'fbj_iex' => now(),
        ]);
    }

    /**
     * Obtener formato de teléfono para mostrar
     */
    public function getTeléfonoFormato(): string
    {
        return $this->tel_iex ?? 'No registrado';
    }

    /**
     * Obtener formato de email para mostrar
     */
    public function getEmailFormato(): string
    {
        return $this->cor_iex ?? 'No registrado';
    }

    /**
     * Obtener formato de ciudad para mostrar
     */
    public function getCiudadFormato(): string
    {
        return $this->ciu_iex ?? 'Sin especificar';
    }
}
