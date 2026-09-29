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

    public function reportadoPor()
    {
        return $this->belongsTo(User::class, 'reportado_por', 'cod_usuarios');
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por', 'cod_usuarios');
    }
}
