<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CalleTramo extends Model
{
    use SoftDeletes;

    protected $table      = 'calles_tramos';
    protected $primaryKey = 'cod_calles_tramos';
    public    $timestamps = false;
    const     DELETED_AT  = 'fbj_ctr';

    const TIPOS = [
        'ACERA'         => 'Acera',
        'CRUCE'         => 'Cruce',
        'PASO PEATONAL' => 'Paso Peatonal',
        'CALLE'         => 'Calle',
        'ESCALERA'      => 'Escalera',
        'RAMPA'         => 'Rampa',
        'OTRO'          => 'Otro',
    ];

    protected $fillable = [
        'cod_rutas_ctr',
        'nom_ctr',
        'tip_ctr',
        'ord_ctr',
        'lon_ctr',
        'geo_ctr',
        'act_ctr',
    ];

    protected $casts = [
        'lon_ctr' => 'decimal:2',
        'geo_ctr' => 'array',
        'act_ctr' => 'boolean',
        'fcr_ctr' => 'datetime',
        'fmo_ctr' => 'datetime',
        'fbj_ctr' => 'datetime',
    ];

    // ─── Accessors de compatibilidad con Pivot ─────────────────

    /**
     * Accesor de compatibilidad legacy.
     * @deprecated No utilizar para nuevas reglas ni para identificar la ruta en contextos N:M.
     */
    public function getRutaAttribute(): ?Ruta
    {
        return $this->rutas()->first() ?? $this->getRelationValue('ruta');
    }

    public function getOrdCtrAttribute(): ?int
    {
        if (isset($this->pivot->ord_rct)) {
            return (int) $this->pivot->ord_rct;
        }
        return isset($this->attributes['ord_ctr']) ? (int) $this->attributes['ord_ctr'] : null;
    }

    public function getCodRutasCtrAttribute(): ?int
    {
        if (isset($this->pivot->cod_rutas_rct)) {
            return (int) $this->pivot->cod_rutas_rct;
        }
        return isset($this->attributes['cod_rutas_ctr']) ? (int) $this->attributes['cod_rutas_ctr'] : null;
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function rutas(): BelongsToMany
    {
        return $this->belongsToMany(Ruta::class, 'ruta_calles_tramos', 'cod_calles_tramos_rct', 'cod_rutas_rct')
            ->withPivot(['cod_ruta_calles_tramos', 'ord_rct'])
            ->whereNull('rutas.fbj_rut')
            ->whereNull('ruta_calles_tramos.fbj_rct');
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'cod_rutas_ctr', 'cod_rutas');
    }

    public function condiciones(): HasMany
    {
        return $this->hasMany(CondicionEntorno::class, 'cod_calles_tramos_cen', 'cod_calles_tramos')
            ->whereNull('fbj_cen');
    }

    public function obstaculos(): HasMany
    {
        return $this->hasMany(Obstaculo::class, 'cod_calles_tramos_obs', 'cod_calles_tramos')
            ->whereNull('fbj_obs');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(EvaluacionRiesgo::class, 'cod_calles_tramos_eri', 'cod_calles_tramos')
            ->whereNull('fbj_eri')
            ->orderByDesc('fee_eri');
    }

    public function zonas(): HasMany
    {
        return $this->hasMany(ZonaRiesgo::class, 'cod_calles_tramos_zri', 'cod_calles_tramos')
            ->whereNull('fbj_zri');
    }

    // ─── Helpers Geométricos ──────────────────────────────────

    /**
     * Valida si un arreglo de coordenadas geo_ctr cumple con la estructura requerida.
     */
    public static function esGeoValido(mixed $geo): bool
    {
        if (!is_array($geo) || count($geo) < 2) {
            return false;
        }

        foreach ($geo as $punto) {
            if (!is_array($punto) || !isset($punto['lat'], $punto['lng'])) {
                return false;
            }
            if (!is_numeric($punto['lat']) || !is_numeric($punto['lng'])) {
                return false;
            }
            $lat = (float) $punto['lat'];
            $lng = (float) $punto['lng'];

            if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcula la longitud geodesica acumulada en metros sobre una poliline geo_ctr.
     */
    public static function calcularLongitudGeodesica(array $coords): float
    {
        $totalMetros = 0.0;
        $count = count($coords);

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $coords[$i];
            $p2 = $coords[$i + 1];

            if (isset($p1['lat'], $p1['lng'], $p2['lat'], $p2['lng'])) {
                $lat1 = (float) $p1['lat'];
                $lng1 = (float) $p1['lng'];
                $lat2 = (float) $p2['lat'];
                $lng2 = (float) $p2['lng'];

                $totalMetros += static::haversineMetros($lat1, $lng1, $lat2, $lng2);
            }
        }

        return round($totalMetros, 2);
    }

    private static function haversineMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radioTierra = 6371000.0; // Metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierra * $c;
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function ultimaEvaluacion(): ?EvaluacionRiesgo
    {
        return $this->evaluaciones()
            ->with('nivelSeguridad')
            ->latest('fee_eri')
            ->first();
    }

    public function obstaculosActivos(): int
    {
        return $this->obstaculos()
            ->whereIn('est_obs', ['REPORTADO', 'ACTIVO'])
            ->count();
    }

    public function perfilRiesgo(): array
    {
        return app(\App\Services\TramoRiskService::class)->obtenerPerfil($this);
    }
}

