<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccesoSalud extends Model
{
    protected $table = 'accesos_salud';
    protected $primaryKey = 'cod_accesos_salud';
    public $timestamps = false;

    protected $fillable = [
        'cod_afiliaciones_asa',
        'cod_instituciones_externas_asa',
        'fea_asa',
        'mot_asa',
        'est_asa',
        'obs_asa',
        'cod_usuarios_asa',
    ];

    protected $casts = [
        'fea_asa' => 'date',
        'fcr_asa' => 'datetime',
        'fmo_asa' => 'datetime',
        'fbj_asa' => 'datetime',
    ];

    // Constantes de estados
    const ESTADOS = [
        'ACTIVO' => 'Activo',
        'FINALIZADO' => 'Finalizado',
        'SUSPENDIDO' => 'Suspendido',
    ];

    const COLORES_ESTADO = [
        'ACTIVO' => 'green',
        'FINALIZADO' => 'gray',
        'SUSPENDIDO' => 'yellow',
    ];

    /**
     * Relación: Un acceso pertenece a una afiliación
     */
    public function afiliacion(): BelongsTo
    {
        return $this->belongsTo(
            Afiliacion::class,
            'cod_afiliaciones_asa',
            'cod_afiliaciones'
        );
    }

    /**
     * Relación: Un acceso es de una institución
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(
            InstitucionExterna::class,
            'cod_instituciones_externas_asa',
            'cod_instituciones_externas'
        );
    }

    /**
     * Relación: Un acceso fue registrado por un usuario
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cod_usuarios_asa',
            'cod_usuarios'
        );
    }

    /**
     * Scope: Accesos activos
     */
    public function scopeActivos($query): Builder
    {
        return $query->where('est_asa', 'ACTIVO')->whereNull('fbj_asa');
    }

    /**
     * Scope: Accesos finalizados
     */
    public function scopeFinalizados($query): Builder
    {
        return $query->where('est_asa', 'FINALIZADO')->whereNull('fbj_asa');
    }

    /**
     * Scope: Accesos suspendidos
     */
    public function scopeSuspendidos($query): Builder
    {
        return $query->where('est_asa', 'SUSPENDIDO')->whereNull('fbj_asa');
    }

    /**
     * Scope: Por estado
     */
    public function scopePorEstado($query, string $estado): Builder
    {
        return $query->where('est_asa', $estado)->whereNull('fbj_asa');
    }

    /**
     * Scope: Accesos no eliminados
     */
    public function scopeActuales($query): Builder
    {
        return $query->whereNull('fbj_asa');
    }

    /**
     * Scope: Con relaciones cargadas
     */
    public function scopeConRelaciones($query): Builder
    {
        return $query->with(['afiliacion.persona', 'institucion', 'usuarioRegistro']);
    }

    /**
     * Obtener nombre del estado
     */
    public function getNombreEstado(): string
    {
        return self::ESTADOS[$this->est_asa] ?? $this->est_asa;
    }

    /**
     * Obtener color del estado para badges
     */
    public function getColorEstado(): string
    {
        return self::COLORES_ESTADO[$this->est_asa] ?? 'gray';
    }

    /**
     * Verificar si el acceso está activo
     */
    public function estaActivo(): bool
    {
        return $this->est_asa === 'ACTIVO' && !$this->fbj_asa;
    }

    /**
     * Verificar si el acceso está finalizado
     */
    public function estaFinalizado(): bool
    {
        return $this->est_asa === 'FINALIZADO' && !$this->fbj_asa;
    }

    /**
     * Verificar si el acceso está suspendido
     */
    public function estaSuspendido(): bool
    {
        return $this->est_asa === 'SUSPENDIDO' && !$this->fbj_asa;
    }

    /**
     * Verificar si está eliminado
     */
    public function estaEliminado(): bool
    {
        return !is_null($this->fbj_asa);
    }

    /**
     * Cambiar estado del acceso
     */
    public function cambiarEstado(string $nuevoEstado): void
    {
        $this->update([
            'est_asa' => $nuevoEstado,
            'fmo_asa' => now(),
        ]);
    }

    /**
     * Marcar como activo
     */
    public function marcarActivo(): void
    {
        $this->cambiarEstado('ACTIVO');
    }

    /**
     * Marcar como finalizado
     */
    public function marcarFinalizado(): void
    {
        $this->cambiarEstado('FINALIZADO');
    }

    /**
     * Marcar como suspendido
     */
    public function marcarSuspendido(): void
    {
        $this->cambiarEstado('SUSPENDIDO');
    }

    /**
     * Obtener días desde la fecha de acceso
     */
    public function getDiasDesdeAcceso(): int
    {
        return Carbon::parse($this->fea_asa)->diffInDays(now());
    }

    /**
     * Dar de baja (borrado lógico)
     */
    public function darDeBaja(): void
    {
        $this->update([
            'fbj_asa' => now(),
        ]);
    }
}
