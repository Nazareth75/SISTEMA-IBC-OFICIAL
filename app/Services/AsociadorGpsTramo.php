<?php
namespace App\Services;
use App\Models\{CalleTramo, Ruta};

class AsociadorGpsTramo
{
    public function asociar(Ruta $ruta, float $lat, float $lng, ?float $precision): ?CalleTramo
    {
        $tolerancia = (float) config('movilidad.tolerancia_gps_m', 20);
        // La falta de precisión impide validar una asociación, no el registro del evento.
        if ($precision === null || $precision > $tolerancia) return null;
        $mejor = null; $menor = $tolerancia;
        $escalaX = 111320*cos(deg2rad($lat)); $escalaY = 111320;
        foreach ($ruta->tramos as $tramo) {
            $geo = $tramo->geo_ctr;
            if (!CalleTramo::esGeoValido($geo)) continue;
            for ($i=1; $i<count($geo); $i++) {
                $ax=($geo[$i-1]['lng']-$lng)*$escalaX; $ay=($geo[$i-1]['lat']-$lat)*$escalaY;
                $bx=($geo[$i]['lng']-$lng)*$escalaX; $by=($geo[$i]['lat']-$lat)*$escalaY;
                $dx=$bx-$ax; $dy=$by-$ay; $l=$dx*$dx+$dy*$dy;
                $t=$l>0 ? max(0,min(1,-($ax*$dx+$ay*$dy)/$l)) : 0;
                $distancia=hypot($ax+$t*$dx,$ay+$t*$dy);
                if ($distancia <= $menor) {$menor=$distancia; $mejor=$tramo;}
            }
        }
        return $mejor;
    }
}
