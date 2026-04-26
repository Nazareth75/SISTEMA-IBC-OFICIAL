<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipacionCapacitacion extends Model
{
    use SoftDeletes;

    protected $table = 'participaciones_capacitacion';
    protected $primaryKey = 'cod_participaciones_capacitacion';
    const DELETED_AT = 'fbj_pca';
    public $timestamps = false;

    const ESTADOS = [
        'INSCRITO'   => 'Inscrito',
        'EN CURSO'   => 'En Curso',
        'APROBADO'   => 'Aprobado',
        'REPROBADO'  => 'Reprobado',
        'ABANDONÓ'   => 'Abandonó',
    ];

    const COLORES_ESTADO = [
        'INSCRITO'   => 'bg-yellow-100 text-yellow-800',
        'EN CURSO'   => 'bg-blue-100 text-blue-800',
        'APROBADO'   => 'bg-green-100 text-green-800',
        'REPROBADO'  => 'bg-red-100 text-red-800',
        'ABANDONÓ'   => 'bg-gray-100 text-gray-800',
    ];

    protected $fillable = [
        'cod_personas_no_videntes_pca',
        'cod_capacitaciones_pca',
        'fei_pca',
        'est_pca',
        'obs_pca',
    ];

    protected $casts = [
        'fei_pca' => 'date',
        'fcr_pca' => 'datetime',
        'fmo_pca' => 'datetime',
        'fbj_pca' => 'datetime',
    ];

    // --- Relaciones ---

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(
            Capacitacion::class,
            'cod_capacitaciones_pca',
            'cod_capacitaciones'
        );
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(
            PersonaNoVidente::class,
            'cod_personas_no_videntes_pca',
            'cod_personas_no_videntes'
        );
    }

    // --- Scopes ---

    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('est_pca', $estado);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereIn('est_pca', ['INSCRITO', 'EN CURSO']);
    }

    // --- Helpers ---

    public static function yaInscrita(int $personaId, int $capacitacionId): bool
    {
        return static::where('cod_personas_no_videntes_pca', $personaId)
            ->where('cod_capacitaciones_pca', $capacitacionId)
            ->exists();
    }

    public static function yaInscritaActiva(int $personaId, int $capacitacionId): bool
    {
        return static::where('cod_personas_no_videntes_pca', $personaId)
            ->where('cod_capacitaciones_pca', $capacitacionId)
            ->whereIn('est_pca', ['INSCRITO', 'EN CURSO'])
            ->exists();
    }
}
