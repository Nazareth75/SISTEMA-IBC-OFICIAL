<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capacitacion extends Model
{
    use SoftDeletes;

    protected $table = 'capacitaciones';
    protected $primaryKey = 'cod_capacitaciones';
    const DELETED_AT = 'fbj_cap';
    public $timestamps = false;

    const ESTADOS = [
        'PLANIFICADA' => 'Planificada',
        'EN CURSO'    => 'En Curso',
        'FINALIZADA'  => 'Finalizada',
        'CANCELADA'   => 'Cancelada',
    ];

    const COLORES_ESTADO = [
        'PLANIFICADA' => 'bg-yellow-100 text-yellow-800',
        'EN CURSO'    => 'bg-blue-100 text-blue-800',
        'FINALIZADA'  => 'bg-green-100 text-green-800',
        'CANCELADA'   => 'bg-red-100 text-red-800',
    ];

    protected $fillable = [
        'ide_cap',
        'nom_cap',
        'obj_cap',
        'cod_tipos_capacitacion_cap',
        'fei_cap',
        'fef_cap',
        'lug_cap',
        'cod_usuarios_cap',
        'est_cap',
    ];

    protected $casts = [
        'fei_cap' => 'date',
        'fef_cap' => 'date',
        'fcr_cap' => 'datetime',
        'fmo_cap' => 'datetime',
        'fbj_cap' => 'datetime',
    ];

    // --- Mutators ---

    public function setNomCapAttribute(string $value): void
    {
        $this->attributes['nom_cap'] = mb_strtoupper(trim($value), 'UTF-8');
    }

    public function setLugCapAttribute(?string $value): void
    {
        $this->attributes['lug_cap'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    // --- Scopes ---

    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('est_cap', $estado);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereIn('est_cap', ['PLANIFICADA', 'EN CURSO']);
    }

    public function scopeBuscador(Builder $query, ?string $texto): Builder
    {
        if (!$texto) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($texto) {
            $q->where('nom_cap', 'like', '%' . $texto . '%')
              ->orWhere('ide_cap', 'like', '%' . $texto . '%');
        });
    }

    // --- Relaciones ---

    public function tipoCapacitacion(): BelongsTo
    {
        return $this->belongsTo(
            TipoCapacitacion::class,
            'cod_tipos_capacitacion_cap',
            'cod_tipos_capacitacion'
        );
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cod_usuarios_cap',
            'cod_usuarios'
        );
    }

    public function participaciones(): HasMany
    {
        return $this->hasMany(
            ParticipacionCapacitacion::class,
            'cod_capacitaciones_pca',
            'cod_capacitaciones'
        );
    }

    // --- Helpers ---

    public function estaDisponible(): bool
    {
        return in_array($this->est_cap, ['PLANIFICADA', 'EN CURSO']);
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()
            ->whereNotNull('ide_cap')
            ->orderBy('cod_capacitaciones', 'desc')
            ->value('ide_cap');

        if (!$ultimo) {
            return 'CAP-001';
        }

        $numero = (int) substr($ultimo, 4);
        return 'CAP-' . str_pad($numero + 1, 3, '0', STR_PAD_LEFT);
    }
}
