<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteObstaculo extends Model
{
    protected $table = 'reportes_obstaculos';

    const METODOS_DETECCION = [
        'MANUAL' => 'Manual (Web / App)',
        'BASTON' => 'Bastón Inteligente (IoT)',
        'IA' => 'Reconocimiento IA',
    ];

    protected $fillable = [
        'codigo',
        'tipo_obstaculo',
        'descripcion',
        'direccion_referencia',
        'latitud',
        'longitud',
        'foto',
        'nivel_riesgo',
        'metodo_deteccion',
        'prioridad',
        'estado',
        'reportado_por',
        'validado_por',
        'cod_obstaculos_rpo',
        'fecha_reporte',
        'fecha_validacion',
        'fecha_resolucion',
        'observacion_validacion',
    ];

    protected $casts = [
        'fecha_reporte' => 'datetime',
        'fecha_validacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Priority/state changes alter risk even when the obstacle state stays unchanged.
        $invalidar = static function (self $reporte): void {
            $ids = array_filter(array_unique([$reporte->cod_obstaculos_rpo,
                $reporte->getOriginal('cod_obstaculos_rpo')]));
            foreach (Obstaculo::whereIn('cod_obstaculos', $ids)->with('tramo.rutas')->get() as $obstaculo) {
                foreach ($obstaculo->tramo?->rutas ?? [] as $ruta) {
                    app(\App\Services\Aco\ServicioEstadoRecomendacionAco::class)
                        ->marcarDesactualizadaPorModificacion($ruta, 'Cambio de prioridad, estado o vinculacion de evidencia del tramo.');
                }
            }
        };
        static::saved(static function (self $reporte) use ($invalidar): void {
            if ($reporte->wasRecentlyCreated || $reporte->wasChanged(['prioridad','estado','cod_obstaculos_rpo'])) $invalidar($reporte);
        });
        static::deleted($invalidar);
    }

    public function reportadoPor()
    {
        return $this->belongsTo(User::class, 'reportado_por', 'cod_usuarios');
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por', 'cod_usuarios');
    }

    public function obstaculo()
    {
        return $this->belongsTo(Obstaculo::class, 'cod_obstaculos_rpo', 'cod_obstaculos');
    }
}
