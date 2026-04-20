<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionBeneficio extends Model
{
    protected $table = 'asignacion_beneficios';
    protected $primaryKey = 'cod_asignacion_beneficios';

    public $timestamps = true;

    const CREATED_AT = 'fcr_abe';
    const UPDATED_AT = 'fmo_abe';

    protected $fillable = [
        'cod_afiliaciones_abe',
        'cod_beneficios_sociales_abe',
        'fas_abe',
        'ffi_abe',
        'est_abe',
        'obs_abe',
        'cod_usuarios_abe',
        'fbj_abe',
    ];

    protected $casts = [
        'fas_abe' => 'date',
        'ffi_abe' => 'date',
        'fcr_abe' => 'datetime',
        'fmo_abe' => 'datetime',
        'fbj_abe' => 'datetime',
    ];

    /**
     * Estados de asignación
     */
    public const ESTADOS = [
        'ACTIVO' => 'Activo',
        'SUSPENDIDO' => 'Suspendido',
        'FINALIZADO' => 'Finalizado',
    ];

    /**
     * Colores para badges según estado
     */
    public const COLORES_ESTADO = [
        'ACTIVO' => 'success',
        'SUSPENDIDO' => 'warning',
        'FINALIZADO' => 'danger',
    ];

    /**
     * Relación: Pertenece a una Afiliación
     */
    public function afiliacion(): BelongsTo
    {
        return $this->belongsTo(
            Afiliacion::class,
            'cod_afiliaciones_abe',
            'cod_afiliaciones'
        );
    }

    /**
     * Relación: Pertenece a un Beneficio Social
     */
    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(
            BeneficioSocial::class,
            'cod_beneficios_sociales_abe',
            'cod_beneficios_sociales'
        );
    }

    /**
     * Relación: Usuario que registró la asignación
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cod_usuarios_abe',
            'cod_usuarios'
        );
    }

    /**
     * Relación: A través de Afiliación, obtener la Persona No Vidente
     */
    public function personaNoVidente()
    {
        return $this->afiliacion->persona;
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('est_abe', $estado);
    }

    /**
     * Scope: Filtrar asignaciones activas
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('est_abe', 'ACTIVO');
    }

    /**
     * Scope: Filtrar asignaciones suspendidas
     */
    public function scopeSuspendidos(Builder $query): Builder
    {
        return $query->where('est_abe', 'SUSPENDIDO');
    }

    /**
     * Scope: Filtrar asignaciones finalizadas
     */
    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->where('est_abe', 'FINALIZADO');
    }

    /**
     * Scope: Filtrar por tipo de beneficio
     */
    public function scopePorTipoBeneficio(Builder $query, string $tipo): Builder
    {
        return $query->whereHas('beneficio', function ($q) use ($tipo) {
            $q->where('tip_bso', $tipo);
        });
    }

    /**
     * Scope: Búsqueda por nombre o CI del afiliado
     */
    public function scopeBuscador(Builder $query, string $termino): Builder
    {
        return $query->whereHas('afiliacion.persona', function ($q) use ($termino) {
            $q->where('nom_pnv', 'like', '%' . $termino . '%')
                ->orWhere('ape_pnv', 'like', '%' . $termino . '%')
                ->orWhere('cii_pnv', 'like', '%' . $termino . '%');
        });
    }

    /**
     * Scope: Incluir relaciones importantes
     */
    public function scopeConRelaciones(Builder $query): Builder
    {
        return $query->with([
            'afiliacion.persona',
            'beneficio',
            'usuarioRegistro',
        ]);
    }

    /**
     * Scope: Obtener solo asignaciones no dadas de baja
     */
    public function scopeActuales(Builder $query): Builder
    {
        return $query->whereNull('fbj_abe');
    }

    /**
     * Verificar si la asignación está activa
     */
    public function estaActiva(): bool
    {
        return $this->est_abe === 'ACTIVO' && is_null($this->fbj_abe);
    }

    /**
     * Verificar si la asignación está suspendida
     */
    public function estaSuspendida(): bool
    {
        return $this->est_abe === 'SUSPENDIDO';
    }

    /**
     * Verificar si la asignación está finalizada
     */
    public function estaFinalizada(): bool
    {
        return $this->est_abe === 'FINALIZADO';
    }

    /**
     * Verificar si está vencida (fecha fin menor a hoy)
     */
    public function estaVencida(): bool
    {
        return $this->ffi_abe && Carbon::parse($this->ffi_abe)->isPast();
    }

    /**
     * Cambiar estado de la asignación
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        return $this->update(['est_abe' => $nuevoEstado]);
    }

    /**
     * Activar la asignación
     */
    public function activar(): bool
    {
        return $this->cambiarEstado('ACTIVO');
    }

    /**
     * Suspender la asignación
     */
    public function suspender(): bool
    {
        return $this->cambiarEstado('SUSPENDIDO');
    }

    /**
     * Finalizar la asignación
     */
    public function finalizar(): bool
    {
        return $this->cambiarEstado('FINALIZADO');
    }

    /**
     * Dar de baja la asignación
     */
    public function darDeBaja(): bool
    {
        return $this->update(['fbj_abe' => now()]);
    }

    /**
     * Obtener el nombre del estado en formato legible
     */
    public function getNombreEstado(): string
    {
        return self::ESTADOS[$this->est_abe] ?? 'Desconocido';
    }

    /**
     * Obtener el color del estado
     */
    public function getColorEstado(): string
    {
        return self::COLORES_ESTADO[$this->est_abe] ?? 'secondary';
    }

    /**
     * Verificar si el afiliado ya tiene activo este beneficio
     */
    public static function tieneBeneficioActivo(int $codigoAfiliacion, int $codigoBeneficio): bool
    {
        return self::where('cod_afiliaciones_abe', $codigoAfiliacion)
            ->where('cod_beneficios_sociales_abe', $codigoBeneficio)
            ->where('est_abe', 'ACTIVO')
            ->whereNull('fbj_abe')
            ->exists();
    }

    /**
     * Obtener la duración de la asignación en días
     */
    public function getDuracionDias(): ?int
    {
        if (!$this->ffi_abe) {
            return null;
        }

        return Carbon::parse($this->fas_abe)->diffInDays(Carbon::parse($this->ffi_abe));
    }

    /**
     * Obtener días restantes si la asignación tiene fecha fin
     */
    public function getDiasRestantes(): ?int
    {
        if (!$this->ffi_abe || $this->estaFinalizada()) {
            return null;
        }

        $diasRestantes = now()->diffInDays(Carbon::parse($this->ffi_abe), false);

        return max(0, $diasRestantes);
    }
}
