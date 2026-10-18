<?php

namespace App\Services\Aco;

use App\Models\Obstaculo;
use App\Models\VerificacionObstaculo;
use Illuminate\Support\Collection;

/**
 * Servicio encargado de agregar evidencias colaborativas y determinar el consenso
 * de resolución comunitaria sobre obstáculos peatonales.
 *
 * Responsabilidad ingenieril:
 * - Filtra y deduplica verificaciones por voluntario (toma estrictamente la más reciente de cada usuario).
 * - Identifica situaciones de consenso armónico ("CANDIDATO A RESOLUCIÓN").
 * - Detecta situaciones de discrepancia ("EVIDENCIA EN CONFLICTO").
 * - Aplica el umbral mínimo configurable definido en config('ibc.min_verificaciones_resolucion').
 */
class ServicioConsensoVerificacionObstaculo
{
    /**
     * Evalúa el estado de consenso para un obstáculo determinado.
     *
     * @param Obstaculo $obstaculo
     * @return array<string, mixed>
     */
    public function evaluarConsenso(Obstaculo $obstaculo): array
    {
        $minRequerido = (int) config('ibc.min_verificaciones_resolucion', 2);

        // Obtener todas las verificaciones ordenadas cronológicamente (más recientes primero)
        $todas = $obstaculo->verificaciones()
            ->with('usuario')
            ->orderByDesc('created_at')
            ->get();

        $totalRegistros = $todas->count();

        if ($obstaculo->est_obs === 'RESUELTO') {
            return [
                'obstaculo_id'              => $obstaculo->getKey(),
                'estado_actual'             => 'RESUELTO',
                'estado_consenso'           => 'YA_RESUELTO',
                'es_candidato_resolucion'   => false,
                'min_requerido'             => $minRequerido,
                'total_verificaciones'      => $totalRegistros,
                'usuarios_unicos'           => 0,
                'conteo_ya_no_esta'         => 0,
                'conteo_sigue_presente'     => 0,
                'conteo_no_puede_verificar' => 0,
                'motivo'                    => 'El obstáculo ya fue validado y marcado como RESUELTO.',
                'verificaciones_deduplicadas' => collect(),
                'ultimas_verificaciones'    => $todas->take(5),
            ];
        }

        // Deduplicación por usuario: tomar únicamente el voto más reciente de cada voluntario único
        $porUsuario = $todas->groupBy('cod_usuario')->map(function (Collection $grupo) {
            return $grupo->first(); // El primero es el más reciente debido al orderByDesc('created_at')
        });

        $usuariosUnicos = $porUsuario->count();

        // Conteo por respuesta válida
        $conteoYaNoEsta = $porUsuario->filter(fn ($v) => $v->respuesta === 'YA_NO_ESTA')->count();
        $conteoContinua = $porUsuario->filter(fn ($v) => $v->respuesta === 'SIGUE_PRESENTE')->count();
        $conteoIncierto = $porUsuario->filter(fn ($v) => $v->respuesta === 'NO_PUEDE_VERIFICAR')->count();

        // 1. Detección de Conflicto: al menos un usuario afirma que no está y otro afirma que sigue presente
        if ($conteoYaNoEsta > 0 && $conteoContinua > 0) {
            return [
                'obstaculo_id'              => $obstaculo->getKey(),
                'estado_actual'             => $obstaculo->est_obs,
                'estado_consenso'           => 'EN_CONFLICTO',
                'es_candidato_resolucion'   => false,
                'min_requerido'             => $minRequerido,
                'total_verificaciones'      => $totalRegistros,
                'usuarios_unicos'           => $usuariosUnicos,
                'conteo_ya_no_esta'         => $conteoYaNoEsta,
                'conteo_sigue_presente'     => $conteoContinua,
                'conteo_no_puede_verificar' => $conteoIncierto,
                'motivo'                    => "EVIDENCIA EN CONFLICTO: {$conteoYaNoEsta} voluntario(s) indican que ya no está, pero {$conteoContinua} indica(n) que sigue presente. Requiere inspección técnica presencial.",
                'verificaciones_deduplicadas' => $porUsuario->values(),
                'ultimas_verificaciones'    => $todas->take(5),
            ];
        }

        // 2. Consenso para Resolución: cumple el umbral sin conflicto
        if ($conteoYaNoEsta >= $minRequerido && $conteoContinua === 0) {
            return [
                'obstaculo_id'              => $obstaculo->getKey(),
                'estado_actual'             => $obstaculo->est_obs,
                'estado_consenso'           => 'CANDIDATO_RESOLUCION',
                'es_candidato_resolucion'   => true,
                'min_requerido'             => $minRequerido,
                'total_verificaciones'      => $totalRegistros,
                'usuarios_unicos'           => $usuariosUnicos,
                'conteo_ya_no_esta'         => $conteoYaNoEsta,
                'conteo_sigue_presente'     => $conteoContinua,
                'conteo_no_puede_verificar' => $conteoIncierto,
                'motivo'                    => "CANDIDATO A RESOLUCIÓN: {$conteoYaNoEsta} voluntarios independientes han verificado que el obstáculo ya no está.",
                'verificaciones_deduplicadas' => $porUsuario->values(),
                'ultimas_verificaciones'    => $todas->take(5),
            ];
        }

        // 3. Confirmación de Peligro Vigente: voluntarios verifican que continúa
        if ($conteoContinua > 0) {
            return [
                'obstaculo_id'              => $obstaculo->getKey(),
                'estado_actual'             => $obstaculo->est_obs,
                'estado_consenso'           => 'PELIGRO_CONFIRMADO',
                'es_candidato_resolucion'   => false,
                'min_requerido'             => $minRequerido,
                'total_verificaciones'      => $totalRegistros,
                'usuarios_unicos'           => $usuariosUnicos,
                'conteo_ya_no_esta'         => $conteoYaNoEsta,
                'conteo_sigue_presente'     => $conteoContinua,
                'conteo_no_puede_verificar' => $conteoIncierto,
                'motivo'                    => "PELIGRO CONFIRMADO: {$conteoContinua} voluntario(s) confirmaron recientemente que el obstáculo continúa presente.",
                'verificaciones_deduplicadas' => $porUsuario->values(),
                'ultimas_verificaciones'    => $todas->take(5),
            ];
        }

        // 4. Evidencia Insuficiente
        return [
            'obstaculo_id'              => $obstaculo->getKey(),
            'estado_actual'             => $obstaculo->est_obs,
            'estado_consenso'           => 'INSUFICIENTE',
            'es_candidato_resolucion'   => false,
            'min_requerido'             => $minRequerido,
            'total_verificaciones'      => $totalRegistros,
            'usuarios_unicos'           => $usuariosUnicos,
            'conteo_ya_no_esta'         => $conteoYaNoEsta,
            'conteo_sigue_presente'     => $conteoContinua,
            'conteo_no_puede_verificar' => $conteoIncierto,
            'motivo'                    => "EVIDENCIA INSUFICIENTE: Se registran {$conteoYaNoEsta} de {$minRequerido} verificaciones requeridas de 'YA NO ESTÁ'.",
            'verificaciones_deduplicadas' => $porUsuario->values(),
            'ultimas_verificaciones'    => $todas->take(5),
        ];
    }

    /**
     * Obtiene los obstáculos activos que han alcanzado la condición de CANDIDATO_RESOLUCION
     * o se encuentran EN_CONFLICTO para la bandeja técnica.
     *
     * @return Collection
     */
    public function obtenerObstaculosParaBandejaTecnica(): Collection
    {
        $obstaculosActivos = Obstaculo::with(['tramo.rutas', 'verificaciones.usuario'])
            ->where('est_obs', 'ACTIVO')
            ->whereHas('verificaciones')
            ->get();

        return $obstaculosActivos->map(function (Obstaculo $obs) {
            $evaluacion = $this->evaluarConsenso($obs);
            return [
                'obstaculo'  => $obs,
                'evaluacion' => $evaluacion,
            ];
        })->filter(function ($item) {
            // Mostrar en bandeja si es candidato a resolución o está en conflicto
            return in_array($item['evaluacion']['estado_consenso'], ['CANDIDATO_RESOLUCION', 'EN_CONFLICTO', 'PELIGRO_CONFIRMADO', 'INSUFICIENTE']);
        })->sortByDesc(function ($item) {
            // Priorizar candidatos a resolución y luego conflictos
            if ($item['evaluacion']['estado_consenso'] === 'CANDIDATO_RESOLUCION') return 3;
            if ($item['evaluacion']['estado_consenso'] === 'EN_CONFLICTO') return 2;
            return 1;
        })->values();
    }
}
