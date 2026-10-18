<?php
namespace App\Services;

use App\Services\Aco\{Grafo,Arista,AnalisisAcoRuta,ConfiguracionAco,ServicioAco,ServicioRutaReferencia,CaminosVisualesAco};

/** Read-only road preview. No official route, obstacle or risk record is created. */
class VistaPreviaMerced
{
    public const ID = 2147483647;
    public function obtener(): array
    {
        return cache()->remember('previa-merced-v2', 3600, function () {
            $datos=json_decode(file_get_contents(database_path('data/ibc-merced-previa.json')),true,512,JSON_THROW_ON_ERROR);
            $grafo=new Grafo(); $aristas=[];
            foreach($datos['tramos'] as $i=>$t) {
                $grafo->addEdge(new Arista((string)$t['inicio'],(string)$t['fin'],$t['distancia_metros'],0));
                $aristas[]=['origen'=>(string)$t['inicio'],'destino'=>(string)$t['fin'],'tramo_id'=>$i+1,'nombre'=>$t['nombre'],'geo'=>$t['geometria']];
            }
            $origen=(string)$datos['tramos'][0]['inicio'];
            $destino=(string)$datos['tramos'][count($datos['tramos'])-1]['fin'];
            // Unknown risk is excluded from optimization, never presented as zero/low risk.
            $cfg=new ConfiguracionAco(ants:60,iterations:100,seed:42,wDistance:1,wRisk:0);
            $resultado=app(ServicioAco::class)->solve($grafo,$origen,$destino,$cfg);
            if(!$resultado->success) throw new \RuntimeException('Vista previa sin camino completo.');
            $analisis=new AnalisisAcoRuta(['nodoOrigenId'=>$origen,'nodoDestinoId'=>$destino,'aristas'=>$aristas]);
            $caminos=app(CaminosVisualesAco::class)->generar($analisis,$grafo,$resultado,$cfg);
            $referencia=app(ServicioRutaReferencia::class)->solve($grafo,$origen,$destino,1,0);
            $geometria=[];$ids=[];$nombres=[];
            foreach($caminos as &$c) {
                $c['riesgo']=null;
                $c['mas_corta']=abs($c['distancia']-$referencia->totalDistance)<0.01;
                if($c['recomendada']) {
                    $geometria=$c['geometria'];$ids=$c['tramos_ids'];$nombres=$c['tramos_nombres'];
                }
            }
            unset($c);
            $metros=round($resultado->totalDistance);
            return ['success'=>true,'ruta_id'=>self::ID,'ruta_nombre'=>$datos['nombre'],
                'origen'=>$datos['origen'],'destino'=>$datos['destino'],'vista_previa'=>true,'demostracion'=>false,
                'riesgo_evaluado'=>false,'nivel_riesgo_estimado'=>'SIN EVALUAR','fecha_calculo'=>now()->toIso8601String(),
                'cantidad_alternativas'=>count($caminos),'cantidad_tramos'=>count($aristas),'caminos_candidatos'=>$caminos,
                'distancia_total'=>$resultado->totalDistance,'distancia_mas_corta'=>$referencia->totalDistance,
                'tramos_seleccionados_ids'=>$ids,'tramos_seleccionados_nombres'=>$nombres,
                'geometria_recomendada'=>$geometria,'instrucciones'=>app(InstruccionesRutaService::class)->generar($geometria),
                'resumen_audio'=>"Vista previa desde IBC, calle Colombia, al Hospital La Merced. Se comparan ".count($caminos)." caminos. El camino ACO mide {$metros} metros. El mas corto de la red comparada mide ".round($referencia->totalDistance)." metros. Riesgo sin evaluar. No es una ruta segura validada. Pasa por ".implode(', ',array_slice(array_unique($nombres),0,6)).'.',
                'advertencia'=>$datos['advertencia'],'tipo_guia'=>'APROXIMADA_DESDE_GEOMETRIA'];
        });
    }
}
