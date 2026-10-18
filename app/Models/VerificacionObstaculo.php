<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa una verificación colaborativa de un obstáculo vial realizada por un voluntario.
 *
 * Responsabilidad ingenieril:
 * Almacena la evidencia empírica (respuesta, coordenadas opcionales, foto, observación)
 * para alimentar el servicio de consenso algorítmico sin otorgar permisos administrativos
 * directos a los voluntarios para mutar el estado oficial del obstáculo.
 */
class VerificacionObstaculo extends Model
{
    protected $table = 'verificaciones_obstaculos';

    public const RESPUESTAS = [
        'SIGUE_PRESENTE'     => 'Sigue presente',
        'YA_NO_ESTA'         => 'Ya no está',
        'NO_PUEDE_VERIFICAR' => 'No puedo verificar',
    ];

    public const CANALES = [
        'WEB'             => 'Web',
        'APP'             => 'App Móvil',
        'BASTON_ASISTIDO' => 'Bastón Asistido',
    ];

    public const COLORES_RESPUESTA = [
        'SIGUE_PRESENTE'     => 'bg-rose-100 text-rose-800 border-rose-300',
        'YA_NO_ESTA'         => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'NO_PUEDE_VERIFICAR' => 'bg-slate-100 text-slate-800 border-slate-300',
    ];

    protected $fillable = [
        'cod_obstaculo',
        'cod_usuario',
        'respuesta',
        'latitud',
        'longitud',
        'precision_gps',
        'foto',
        'observacion',
        'canal',
    ];

    protected $casts = [
        'latitud'       => 'float',
        'longitud'      => 'float',
        'precision_gps' => 'float',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function obstaculo(): BelongsTo
    {
        return $this->belongsTo(Obstaculo::class, 'cod_obstaculo', 'cod_obstaculos');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'cod_usuarios');
    }

    public function etiquetaRespuesta(): string
    {
        return self::RESPUESTAS[$this->respuesta] ?? $this->respuesta;
    }

    public function badgeClase(): string
    {
        return self::COLORES_RESPUESTA[$this->respuesta] ?? 'bg-gray-100 text-gray-800 border-gray-300';
    }
}
