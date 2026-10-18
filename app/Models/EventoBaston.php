<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventoBaston extends Model {
    protected $table = 'eventos_baston';
    protected $guarded = ['id'];
    protected $casts = ['datos' => 'array', 'fecha_evento' => 'datetime'];
}
