<?php
namespace App\Services\Aco;

/** Enumera caminos del mismo grafo dirigido. No solicita otro routing engine. */
class CaminosVisualesAco
{
    public function generar(AnalisisAcoRuta $analisis, Grafo $grafo, ResultadoAco $resultado, ConfiguracionAco $cfg): array
    {
        $adyacencia=[];$aristasCanonicas=[];
        // El grafo existente identifica aristas por origen->destino; su mapeo
        // de geometría conserva la última entrada para esa misma clave.
        foreach($analisis->aristas as $arista) $aristasCanonicas[$arista['origen'].'->'.$arista['destino']]=$arista;
        foreach($aristasCanonicas as $arista) $adyacencia[$arista['origen']][]=$arista;
        $caminos=[];
        $costo=new CalculadorCosto($cfg->wDistance,$cfg->wRisk);
        $visitar=function($nodo,$nodos,$aristas) use (&$visitar,&$caminos,$adyacencia,$analisis,$grafo,$resultado,$costo) {
            if(count($caminos)>=ConstructorGrafoRuta::LIMITE_EXPLORACION_CAMINOS) return;
            if($nodo===$analisis->nodoDestinoId) {
                $edges=array_map(fn($a)=>$grafo->getEdge($a['origen'],$a['destino']),$aristas);
                $metricas=$costo->calculatePathMetrics($edges,$grafo->getMaxDistance());
                $distancia=$metricas['totalDistance'];$ponderada=0;
                foreach($aristas as $i=>$a) $ponderada+=($resultado->pheromones[$a['origen'].'->'.$a['destino']]??0)*$edges[$i]->getDistance();
                $caminos[]=[
                    'nodos'=>$nodos,'tramos_ids'=>array_column($aristas,'tramo_id'),
                    'tramos_nombres'=>array_column($aristas,'nombre'),
                    'geometria'=>array_column($aristas,'geo'),
                    'distancia'=>$distancia,'riesgo'=>$metricas['totalRisk'],'costo'=>$metricas['totalCost'],
                    'feromona_media_ponderada'=>$distancia>0?$ponderada/$distancia:0,
                    'recomendada'=>$nodos===$resultado->path,
                ];return;
            }
            foreach($adyacencia[$nodo]??[] as $arista) {
                if(in_array($arista['destino'],$nodos,true)) continue;
                $visitar($arista['destino'],[...$nodos,$arista['destino']],[...$aristas,$arista]);
            }
        };
        $visitar($analisis->nodoOrigenId,[$analisis->nodoOrigenId],[]);
        // Nunca perder la solución del motor aunque la enumeración alcance su límite.
        if(!collect($caminos)->contains('recomendada',true)) {
            $seleccion=[];
            for($i=1;$i<count($resultado->path);$i++) {
                foreach($adyacencia[$resultado->path[$i-1]]??[] as $a) {
                    if($a['destino']===$resultado->path[$i]) {$seleccion[]=$a;break;}
                }
            }
            if(count($seleccion)===count($resultado->path)-1) {
                // Solo la ruta recomendada; no sustituirla por la menor distancia.
                $ponderada=0;
                foreach($seleccion as $a) $ponderada+=($resultado->pheromones[$a['origen'].'->'.$a['destino']]??0)*$grafo->getEdge($a['origen'],$a['destino'])->getDistance();
                $caminos[]=['nodos'=>$resultado->path,'tramos_ids'=>array_column($seleccion,'tramo_id'),
                    'tramos_nombres'=>array_column($seleccion,'nombre'),'geometria'=>array_column($seleccion,'geo'),
                    'distancia'=>$resultado->totalDistance,'riesgo'=>$resultado->totalRisk,'costo'=>$resultado->totalCost,
                    'feromona_media_ponderada'=>$resultado->totalDistance>0?$ponderada/$resultado->totalDistance:0,
                    'recomendada'=>true];
            }
        }
        usort($caminos,fn($a,$b)=>($b['recomendada']<=>$a['recomendada'])?:($a['costo']<=>$b['costo']));
        $max=max(array_column($caminos,'feromona_media_ponderada')?:[1]);
        foreach($caminos as $i=>&$camino) {
            $camino['nombre']=$camino['recomendada']?'RUTA RECOMENDADA':'ALTERNATIVA '.($i+1);
            $camino['refuerzo_relativo']=$max>0?$camino['feromona_media_ponderada']/$max:0;
        }
        return $caminos;
    }
}
