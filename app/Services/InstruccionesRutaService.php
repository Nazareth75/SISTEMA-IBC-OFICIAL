<?php

namespace App\Services;

/** Maniobras aproximadas derivadas del trazado ACO; no valida cruces ni aceras. */
class InstruccionesRutaService
{
    public function generar(array $segmentos): array
    {
        $puntos = [];
        foreach ($segmentos as $segmento) {
            foreach ($segmento as $p) {
                if (!$puntos || $this->distancia(end($puntos), $p) > 0.2) $puntos[] = $p;
            }
        }
        if (count($puntos) < 2) return [];
        $instrucciones = [['texto' => 'Ruta iniciada. Continúa por el trazado recomendado.', 'distancia_m' => 0, 'punto' => $puntos[0]]];
        $acumulada = 0.0;
        $ultima = 0.0;
        for ($i = 1; $i < count($puntos) - 1; $i++) {
            $acumulada += $this->distancia($puntos[$i-1], $puntos[$i]);
            $giro = fmod($this->rumbo($puntos[$i], $puntos[$i+1]) - $this->rumbo($puntos[$i-1], $puntos[$i]) + 540, 360) - 180;
            if (abs($giro) >= 40 && $acumulada - $ultima >= 15) {
                $instrucciones[] = ['texto' => $giro > 0 ? 'Gira a la derecha.' : 'Gira a la izquierda.',
                    'distancia_m' => round($acumulada, 1), 'punto' => $puntos[$i]];
                $ultima = $acumulada;
            }
        }
        $acumulada += $this->distancia($puntos[count($puntos)-2], end($puntos));
        $instrucciones[] = ['texto' => 'Te aproximas al destino.', 'distancia_m' => round($acumulada, 1), 'punto' => end($puntos)];
        return $instrucciones;
    }

    private function rumbo(array $a, array $b): float
    {
        $lat1 = deg2rad($a['lat']); $lat2 = deg2rad($b['lat']); $dl = deg2rad($b['lng']-$a['lng']);
        return rad2deg(atan2(sin($dl)*cos($lat2), cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos($dl)));
    }

    private function distancia(array $a, array $b): float
    {
        $dlat = deg2rad($b['lat']-$a['lat']); $dlng = deg2rad($b['lng']-$a['lng']);
        return 12742000*asin(min(1, sqrt(sin($dlat/2)**2+cos(deg2rad($a['lat']))*cos(deg2rad($b['lat']))*sin($dlng/2)**2)));
    }
}
