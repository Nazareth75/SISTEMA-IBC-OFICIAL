<?php

namespace App\Services;

use App\Models\Ruta;
use App\Services\Aco\ServicioEstadoRecomendacionAco;
use App\Services\Aco\ServicioRecomendacionRutaAco;
use App\Services\Aco\ResultadoRecomendacionAco;

/** Una recomendación compartida por la web y la API: Flutter no ejecuta ACO. */
class AsistenteRutaService
{
    public function __construct(
        private ServicioEstadoRecomendacionAco $estado,
        private ServicioRecomendacionRutaAco $aco,
    ) {}

    public function obtener(Ruta $ruta): array
    {
        $resultado = $this->obtenerResultado($ruta);
        if (!$resultado->success) return $resultado->toArray();
        $actual = $resultado->toArray();

        return $actual + [
            'origen' => $ruta->ori_rut,
            'destino' => $ruta->dst_rut,
            'demostracion' => $ruta->esDemo(),
            'resumen_audio' => $this->resumir($ruta, $actual),
            'instrucciones' => app(InstruccionesRutaService::class)->generar($actual['geometria_recomendada']),
            'tipo_guia' => 'APROXIMADA_DESDE_GEOMETRIA',
        ];
    }

    public function obtenerResultado(Ruta $ruta): ResultadoRecomendacionAco
    {
        $actual = $this->estado->obtenerRecomendacionActual($ruta);
        if (!$actual || !isset($actual['caminos_candidatos']) || $this->estado->estaDesactualizada($ruta)) {
            $resultado = $this->aco->recomendar($ruta);
            if (!$resultado->success) {
                return $resultado;
            }
            $this->estado->guardarRecomendacion($ruta, $resultado);
            return $resultado;
        }
        return ResultadoRecomendacionAco::desdeArray($actual);
    }

    public function resumir(Ruta $ruta, array $resultado): string
    {
        $texto = $ruta->esDemo() ? 'Datos simulados de demostración. ' : '';
        $texto .= "Ruta {$ruta->nom_rut}. Desde {$ruta->ori_rut} hasta {$ruta->dst_rut}. ";
        $nombres = array_values(array_unique(array_map(fn ($nombre) => trim(preg_replace(
            ['/^DEMO — LP \d+ — /u', '/\[.*?\]/u'], '', $nombre
        )), $resultado['tramos_seleccionados_nombres'])));
        $texto .= 'Se comparan '.$resultado['cantidad_alternativas'].' caminos. La recomendación pasa por '.
            implode(', ', array_slice($nombres,0,3)).(count($nombres)>3 ? ' y otros tramos. ' : '. ');
        $texto .= round($resultado['distancia_total']).' metros, riesgo estimado '.
            $resultado['nivel_riesgo_estimado'].'. ';
        $elegidos = $resultado['tramos_seleccionados_ids'];
        $afectados = $ruta->tramos->filter(fn ($tramo) => $tramo->obstaculos->contains(
            fn ($obstaculo) => in_array($obstaculo->est_obs, TramoRiskService::ESTADOS_OBSTACULO_VIGENTES, true)
        ));
        foreach ($afectados->take(2) as $tramo) {
            $tipos = $tramo->obstaculos->filter(fn ($o) => in_array(
                $o->est_obs, TramoRiskService::ESTADOS_OBSTACULO_VIGENTES, true
            ))->pluck('tip_obs')->unique()->implode(', ');
            $texto .= "En {$tramo->nom_ctr} se reportan {$tipos}. ";
        }
        if ($afectados->count()>2) $texto .= 'Además, otros '.($afectados->count()-2).' tramos tienen obstáculos reportados. ';
        $cuenta = $afectados->filter(fn ($t) => in_array($t->getKey(), $elegidos))->count();
        $texto .= $cuenta ? "La recomendación incluye {$cuenta} tramos con obstáculos reportados. " :
            'En los tramos recomendados no constan obstáculos activos. ';
        $texto .= 'Resumen previo al recorrido. La navegación del celular utiliza la ubicación durante el trayecto.';
        return $texto;
    }
}
