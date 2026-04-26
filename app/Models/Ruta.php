<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruta extends Model
{
    use SoftDeletes;

    protected $table      = 'rutas';
    protected $primaryKey = 'cod_rutas';

    const CREATED_AT = 'fcr_rut';
    const UPDATED_AT = 'fmo_rut';
    const DELETED_AT = 'fbj_rut';

    const ESTADOS = [
        'BORRADOR'       => 'Borrador',
        'EN REVISIÓN'    => 'En Revisión',
        'APROBADA'       => 'Aprobada',
        'PUBLICADA'      => 'Publicada',
        'DESACTUALIZADA' => 'Desactualizada',
        'ARCHIVADA'      => 'Archivada',
    ];

    const COLORES_ESTADO = [
        'BORRADOR'       => 'bg-gray-100 text-gray-800',
        'EN REVISIÓN'    => 'bg-yellow-100 text-yellow-800',
        'APROBADA'       => 'bg-blue-100 text-blue-800',
        'PUBLICADA'      => 'bg-green-100 text-green-800',
        'DESACTUALIZADA' => 'bg-orange-100 text-orange-800',
        'ARCHIVADA'      => 'bg-red-100 text-red-800',
    ];

    const TRANSICIONES = [
        'BORRADOR'       => ['EN REVISIÓN'],
        'EN REVISIÓN'    => ['APROBADA', 'BORRADOR'],
        'APROBADA'       => ['PUBLICADA', 'ARCHIVADA'],
        'PUBLICADA'      => ['DESACTUALIZADA', 'ARCHIVADA'],
        'DESACTUALIZADA' => ['EN REVISIÓN', 'ARCHIVADA'],
    ];

    protected $fillable = [
        'ide_rut',
        'nom_rut',
        'des_rut',
        'ori_rut',
        'dst_rut',
        'geo_rut',
        'dis_rut',
        'tim_rut',
        'est_rut',
        'cod_usuarios_rut_cre',
        'cod_usuarios_rut_apr',
        'fap_rut',
        'obs_rut',
    ];

    protected $casts = [
        'geo_rut' => 'array',
        'dis_rut' => 'decimal:2',
        'fap_rut' => 'datetime',
        'fcr_rut' => 'datetime',
        'fmo_rut' => 'datetime',
        'fbj_rut' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_rut_cre', 'cod_usuarios');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuarios_rut_apr', 'cod_usuarios');
    }

    public function tramos(): HasMany
    {
        return $this->hasMany(CalleTramo::class, 'cod_rutas_ctr', 'cod_rutas')
            ->whereNull('fbj_ctr')
            ->orderBy('ord_ctr');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNull('fbj_rut');
    }

    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('est_rut', $estado);
    }

    public function scopeBuscador(Builder $query, ?string $termino): Builder
    {
        if (!$termino) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termino) {
            $q->where('nom_rut', 'like', "%{$termino}%")
              ->orWhere('ori_rut', 'like', "%{$termino}%")
              ->orWhere('dst_rut', 'like', "%{$termino}%")
              ->orWhere('ide_rut', 'like', "%{$termino}%");
        });
    }

    // ─── Flujo de estados ────────────────────────────────────

    public function enviarARevision(): void
    {
        $this->update(['est_rut' => 'EN REVISIÓN']);
    }

    public function aprobar(int $usuarioId): void
    {
        $this->update([
            'est_rut'              => 'APROBADA',
            'cod_usuarios_rut_apr' => $usuarioId,
            'fap_rut'              => now(),
        ]);
    }

    public function publicar(): void
    {
        $this->update(['est_rut' => 'PUBLICADA']);
    }

    public function desactualizar(): void
    {
        if ($this->est_rut === 'PUBLICADA') {
            $this->update(['est_rut' => 'DESACTUALIZADA']);
        }
    }

    public function archivar(): void
    {
        $this->update(['est_rut' => 'ARCHIVADA']);
    }

    // ─── Lógica de negocio ───────────────────────────────────

    /**
     * RN-03: No publicar si algún tramo tiene nivel CRÍTICO (niv_nse = 1).
     * RN-04: Nivel mínimo viene de parametros_sistema.umbral_seguridad_ruta.
     */
    public function puedePublicarse(): bool
    {
        $umbral = (int) ParametroSistema::valor('umbral_seguridad_ruta', 3);

        $tramos = $this->tramos()->with(['evaluaciones' => function ($q) {
            $q->with('nivelSeguridad')->latest('fee_eri')->limit(1);
        }])->get();

        foreach ($tramos as $tramo) {
            $ultimaEval = $tramo->evaluaciones->first();

            if (!$ultimaEval) {
                continue;
            }

            if ($ultimaEval->nivelSeguridad->niv_nse < $umbral) {
                return false;
            }
        }

        return true;
    }

    public function nivelSeguridadGeneral(): ?NivelSeguridad
    {
        $minNivel = null;

        foreach ($this->tramos as $tramo) {
            $eval = $tramo->evaluaciones()
                ->with('nivelSeguridad')
                ->latest('fee_eri')
                ->first();

            if (!$eval) {
                continue;
            }

            if ($minNivel === null || $eval->nivelSeguridad->niv_nse < $minNivel->niv_nse) {
                $minNivel = $eval->nivelSeguridad;
            }
        }

        return $minNivel;
    }

    // ─── Helpers ─────────────────────────────────────────────

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()
            ->whereNotNull('ide_rut')
            ->orderByDesc('cod_rutas')
            ->value('ide_rut');

        if (!$ultimo) {
            return 'RUT-001';
        }

        $numero = (int) substr($ultimo, 4);
        return 'RUT-' . str_pad($numero + 1, 3, '0', STR_PAD_LEFT);
    }

    public function colorEstado(): string
    {
        return self::COLORES_ESTADO[$this->est_rut] ?? 'bg-gray-100 text-gray-800';
    }

    public function transicionesPosibles(): array
    {
        return self::TRANSICIONES[$this->est_rut] ?? [];
    }
}
