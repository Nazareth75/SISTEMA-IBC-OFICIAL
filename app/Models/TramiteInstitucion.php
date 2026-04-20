<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TramiteInstitucion extends Model
{
    protected $table = 'tramites_instituciones';
    protected $primaryKey = 'cod_tramites_instituciones';
    public $timestamps = false;

    protected $fillable = [
        'cod_afiliaciones_tin',
        'cod_instituciones_externas_tin',
        'tti_tin',
        'des_tin',
        'fei_tin',
        'fer_tin',
        'est_tin',
        'obs_tin',
        'cod_usuarios_tin',
    ];

    protected $casts = [
        'fei_tin' => 'date',
        'fer_tin' => 'date',
        'fcr_tin' => 'datetime',
        'fmo_tin' => 'datetime',
        'fbj_tin' => 'datetime',
    ];

    // Constantes de tipos y estados
    const TIPOS = [
        'SALUD' => 'Salud',
        'BENEFICIO SOCIAL' => 'Beneficio Social',
        'CAPACITACIÓN' => 'Capacitación',
        'LEGAL' => 'Legal',
        'ADMINISTRATIVO' => 'Administrativo',
        'OTRO' => 'Otro',
    ];

    const ESTADOS = [
        'PENDIENTE' => 'Pendiente',
        'EN PROCESO' => 'En Proceso',
        'RESUELTO' => 'Resuelto',
        'RECHAZADO' => 'Rechazado',
        'ABANDONADO' => 'Abandonado',
    ];

    const COLORES_ESTADO = [
        'PENDIENTE' => 'yellow',
        'EN PROCESO' => 'blue',
        'RESUELTO' => 'green',
        'RECHAZADO' => 'red',
        'ABANDONADO' => 'gray',
    ];

    /**
     * Relación: Un trámite pertenece a una afiliación
     */
    public function afiliacion(): BelongsTo
    {
        return $this->belongsTo(
            Afiliacion::class,
            'cod_afiliaciones_tin',
            'cod_afiliaciones'
        );
    }

    /**
     * Relación: Un trámite pertenece a una institución
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(
            InstitucionExterna::class,
            'cod_instituciones_externas_tin',
            'cod_instituciones_externas'
        );
    }

    /**
     * Relación: Un trámite fue registrado por un usuario
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cod_usuarios_tin',
            'cod_usuarios'
        );
    }

    /**
     * Scope: Trámites activos (no resueltos, no rechazados, no abandonados)
     */
    public function scopeActivos($query): Builder
    {
        return $query->whereIn('est_tin', ['PENDIENTE', 'EN PROCESO'])
            ->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites por estado
     */
    public function scopePorEstado($query, string $estado): Builder
    {
        return $query->where('est_tin', $estado)->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites pendientes
     */
    public function scopePendientes($query): Builder
    {
        return $query->where('est_tin', 'PENDIENTE')->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites en proceso
     */
    public function scopeEnProceso($query): Builder
    {
        return $query->where('est_tin', 'EN PROCESO')->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites resueltos
     */
    public function scopeResueltos($query): Builder
    {
        return $query->where('est_tin', 'RESUELTO')->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites rechazados
     */
    public function scopeRechazados($query): Builder
    {
        return $query->where('est_tin', 'RECHAZADO')->whereNull('fbj_tin');
    }

    /**
     * Scope: Trámites por tipo
     */
    public function scopePorTipo($query, string $tipo): Builder
    {
        return $query->where('tti_tin', $tipo)->whereNull('fbj_tin');
    }

    /**
     * Scope: Buscador por afiliado o institución
     */
    public function scopeBuscador($query, ?string $termino): Builder
    {
        if (!$termino) {
            return $query;
        }

        return $query->where(function ($q) use ($termino) {
            $q->whereHas('afiliacion.persona', function ($sub) use ($termino) {
                $sub->where('nom_pnv', 'like', "%{$termino}%")
                    ->orWhere('ape_pnv', 'like', "%{$termino}%")
                    ->orWhere('cii_pnv', 'like', "%{$termino}%");
            })
                ->orWhereHas('institucion', function ($sub) use ($termino) {
                    $sub->where('nom_iex', 'like', "%{$termino}%");
                });
        });
    }

    /**
     * Scope: Con relaciones cargadas
     */
    public function scopeConRelaciones($query): Builder
    {
        return $query->with(['afiliacion.persona', 'institucion', 'usuarioRegistro']);
    }

    /**
     * Scope: Trámites actuales (no eliminados)
     */
    public function scopeActuales($query): Builder
    {
        return $query->whereNull('fbj_tin');
    }

    /**
     * Obtener nombre del tipo en formato legible
     */
    public function getNombreTipo(): string
    {
        return self::TIPOS[$this->tti_tin] ?? $this->tti_tin;
    }

    /**
     * Obtener nombre del estado en formato legible
     */
    public function getNombreEstado(): string
    {
        return self::ESTADOS[$this->est_tin] ?? $this->est_tin;
    }

    /**
     * Obtener color del estado para badges
     */
    public function getColorEstado(): string
    {
        return self::COLORES_ESTADO[$this->est_tin] ?? 'gray';
    }

    /**
     * Verificar si el trámite está pendiente
     */
    public function estaPendiente(): bool
    {
        return $this->est_tin === 'PENDIENTE' && !$this->fbj_tin;
    }

    /**
     * Verificar si el trámite está en proceso
     */
    public function estaEnProceso(): bool
    {
        return $this->est_tin === 'EN PROCESO' && !$this->fbj_tin;
    }

    /**
     * Verificar si el trámite está resuelto
     */
    public function estaResuelto(): bool
    {
        return $this->est_tin === 'RESUELTO' && !$this->fbj_tin;
    }

    /**
     * Verificar si el trámite está rechazado
     */
    public function estaRechazado(): bool
    {
        return $this->est_tin === 'RECHAZADO' && !$this->fbj_tin;
    }

    /**
     * Verificar si el trámite está abandonado
     */
    public function estaAbandonado(): bool
    {
        return $this->est_tin === 'ABANDONADO' && !$this->fbj_tin;
    }

    /**
     * Verificar si el trámite está eliminado (borrado lógico)
     */
    public function estaEliminado(): bool
    {
        return !is_null($this->fbj_tin);
    }

    /**
     * Cambiar estado del trámite
     * RN-07: Un trámite RESUELTO no puede volver a PENDIENTE
     */
    public function cambiarEstado(string $nuevoEstado, ?string $observaciones = null): bool
    {
        // RN-07: Validar transición de estado
        if ($this->est_tin === 'RESUELTO' && in_array($nuevoEstado, ['PENDIENTE'])) {
            throw new \Exception('Un trámite RESUELTO no puede volver a PENDIENTE.');
        }

        // Si cambio a RESUELTO, establecer fecha de resolución automáticamente
        $datos = [
            'est_tin' => $nuevoEstado,
            'fmo_tin' => now(),
        ];

        if ($nuevoEstado === 'RESUELTO' && !$this->fer_tin) {
            $datos['fer_tin'] = now()->toDateString();
        }

        if ($observaciones) {
            $datos['obs_tin'] = $observaciones;
        }

        $this->update($datos);
        return true;
    }

    /**
     * Cambiar a PENDIENTE
     */
    public function marcarPendiente(): void
    {
        $this->cambiarEstado('PENDIENTE');
    }

    /**
     * Cambiar a EN PROCESO
     */
    public function marcarEnProceso(): void
    {
        $this->cambiarEstado('EN PROCESO');
    }

    /**
     * Cambiar a RESUELTO
     */
    public function marcarResuelto(): void
    {
        $this->cambiarEstado('RESUELTO');
    }

    /**
     * Cambiar a RECHAZADO
     */
    public function marcarRechazado(): void
    {
        $this->cambiarEstado('RECHAZADO');
    }

    /**
     * Cambiar a ABANDONADO
     */
    public function marcarAbandonado(): void
    {
        $this->cambiarEstado('ABANDONADO');
    }

    /**
     * Obtener días transcurridos desde inicio
     */
    public function getDiasTranscurridos(): int
    {
        return Carbon::parse($this->fei_tin)->diffInDays(now());
    }

    /**
     * Obtener días desde resolución
     */
    public function getDiasDesdeResolucion(): ?int
    {
        if (!$this->fer_tin) {
            return null;
        }
        return Carbon::parse($this->fer_tin)->diffInDays(now());
    }

    /**
     * Dar de baja (borrado lógico)
     */
    public function darDeBaja(): void
    {
        $this->update([
            'fbj_tin' => now(),
        ]);
    }
}
