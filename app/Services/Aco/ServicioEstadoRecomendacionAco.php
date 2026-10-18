<?php

namespace App\Services\Aco;

use App\Events\ObstaculoCambioEstado;
use App\Models\Ruta;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio encargado de gestionar el ciclo de vida, persistencia en caché,
 * estado de vigencia (desactualización) y trazabilidad histórica de las recomendaciones ACO.
 *
 * Responsabilidad ingenieril:
 * Mantiene la separación de responsabilidades entre el modelo relacional (Ruta, Obstaculo)
 * y el motor metaheurístico, registrando los cambios inducidos por la resolución de obstáculos
 * para su posterior auditoría y visualización ante comités de evaluación técnica.
 */
class ServicioEstadoRecomendacionAco
{
    private const TTL_SEGUNDOS = 86400; // 24 horas

    public function cacheKeyActual(int $rutaId): string
    {
        return "aco_rec_actual_{$rutaId}";
    }

    public function cacheKeyAnterior(int $rutaId): string
    {
        return "aco_rec_anterior_{$rutaId}";
    }

    public function cacheKeyDesactualizada(int $rutaId): string
    {
        return "aco_rec_desact_{$rutaId}";
    }

    public function cacheKeyHistorial(int $rutaId): string
    {
        return "aco_rec_historial_{$rutaId}";
    }

    /**
     * Invalida la recomendación vigente de una ruta debido a la mutación de un obstáculo.
     */
    public function marcarDesactualizada(Ruta $ruta, ObstaculoCambioEstado $evento): void
    {
        $rutaId = (int) $ruta->getKey();
        $motivo = sprintf(
            "El obstáculo '%s' en el tramo '%s' cambió de estado %s a %s (%s).",
            $evento->obstaculo->tip_obs,
            $evento->obstaculo->tramo?->nom_ctr ?? 'Tramo asociado',
            $evento->estadoAnterior ?: 'NUEVO',
            $evento->estadoNuevo,
            $evento->fecha
        );

        Cache::put($this->cacheKeyDesactualizada($rutaId), [
            'desactualizada' => true,
            'motivo'         => $motivo,
            'fecha'          => $evento->fecha,
            'obstaculo_id'   => $evento->obstaculo->getKey(),
            'estado_previo'  => $evento->estadoAnterior,
            'estado_nuevo'   => $evento->estadoNuevo,
        ], self::TTL_SEGUNDOS);
    }

    /**
     * Invalida la recomendación vigente de una ruta debido a modificaciones estructurales (origen, destino, geometría, tramos).
     */
    public function marcarDesactualizadaPorModificacion(Ruta $ruta, string $motivo): void
    {
        $rutaId = (int) $ruta->getKey();
        Cache::put($this->cacheKeyDesactualizada($rutaId), [
            'desactualizada' => true,
            'motivo'         => $motivo,
            'fecha'          => now()->format('Y-m-d H:i:s'),
            'tipo'           => 'MODIFICACION_ESTRUCTURAL',
        ], self::TTL_SEGUNDOS);
    }

    /**
     * Purga todas las entradas de caché asociadas a la ruta dada (actual, anterior, desactualizada, historial).
     */
    public function limpiarEstado(Ruta $ruta): void
    {
        $rutaId = (int) $ruta->getKey();
        Cache::forget($this->cacheKeyActual($rutaId));
        Cache::forget($this->cacheKeyAnterior($rutaId));
        Cache::forget($this->cacheKeyDesactualizada($rutaId));
        Cache::forget($this->cacheKeyHistorial($rutaId));
    }

    /**
     * Consulta si la recomendación actual de la ruta requiere recálculo por cambios en los tramos.
     */
    public function estaDesactualizada(Ruta $ruta): bool
    {
        $info = Cache::get($this->cacheKeyDesactualizada((int) $ruta->getKey()));
        return is_array($info) && !empty($info['desactualizada']);
    }

    /**
     * Retorna los detalles de por qué la recomendación quedó desactualizada.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerInfoDesactualizacion(Ruta $ruta): ?array
    {
        return Cache::get($this->cacheKeyDesactualizada((int) $ruta->getKey()));
    }

    /**
     * Registra una nueva recomendación calculada y actualiza la trazabilidad histórica de cambios.
     */
    public function guardarRecomendacion(Ruta $ruta, ResultadoRecomendacionAco $nueva): void
    {
        $rutaId = (int) $ruta->getKey();
        $anteriorArray = Cache::get($this->cacheKeyActual($rutaId));

        if ($anteriorArray && is_array($anteriorArray) && !empty($anteriorArray['success'])) {
            // Guardar como recomendación anterior
            Cache::put($this->cacheKeyAnterior($rutaId), $anteriorArray, self::TTL_SEGUNDOS);

            // Determinar si hubo un cambio efectivo en la ruta o costos
            $nodosAnteriores = $anteriorArray['nodos_seleccionados'] ?? [];
            $nodosNuevos     = $nueva->nodosSeleccionados;
            $cambioRuta      = ($nodosAnteriores !== $nodosNuevos);

            $historial = [
                'hay_cambio'           => true,
                'cambio_ruta_efectivo' => $cambioRuta,
                'motivo'               => 'Se actualizó la información de riesgo de uno o más tramos.',
                'fecha_cambio'         => now()->format('Y-m-d H:i:s'),
                'anterior'             => [
                    'nodos'           => $nodosAnteriores,
                    'tramos_ids'      => $anteriorArray['tramos_seleccionados_ids'] ?? [],
                    'distancia'       => $anteriorArray['distancia_total'] ?? 0.0,
                    'riesgo'          => $anteriorArray['riesgo_total'] ?? 0.0,
                    'costo'           => $anteriorArray['costo_total'] ?? 0.0,
                    'nivel_riesgo'    => $anteriorArray['nivel_riesgo_estimado'] ?? 'N/A',
                    'fecha'           => $anteriorArray['fecha_calculo'] ?? null,
                ],
                'actual'               => [
                    'nodos'           => $nueva->nodosSeleccionados,
                    'tramos_ids'      => $nueva->tramosSeleccionadosIds,
                    'distancia'       => round($nueva->distanciaTotal, 1),
                    'riesgo'          => round($nueva->riesgoTotal, 4),
                    'costo'           => round($nueva->costoTotal, 4),
                    'nivel_riesgo'    => $nueva->nivelRiesgoEstimado,
                    'fecha'           => $nueva->fechaCalculo,
                ],
                'delta_distancia'      => round($nueva->distanciaTotal - ($anteriorArray['distancia_total'] ?? 0.0), 1),
                'delta_riesgo'         => round($nueva->riesgoTotal - ($anteriorArray['riesgo_total'] ?? 0.0), 4),
                'delta_costo'          => round($nueva->costoTotal - ($anteriorArray['costo_total'] ?? 0.0), 4),
                'm7_reduccion_riesgo'  => $nueva->m7,
                'm8_incremento_dist'   => $nueva->m8,
            ];

            Cache::put($this->cacheKeyHistorial($rutaId), $historial, self::TTL_SEGUNDOS);
        }

        // Persistir la nueva como actual
        Cache::put($this->cacheKeyActual($rutaId), $nueva->toArray(), self::TTL_SEGUNDOS);

        // Limpiar bandera de desactualización
        Cache::forget($this->cacheKeyDesactualizada($rutaId));
    }

    /**
     * Obtiene el arreglo serializado de la recomendación almacenada actualmente.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerRecomendacionActual(Ruta $ruta): ?array
    {
        return Cache::get($this->cacheKeyActual((int) $ruta->getKey()));
    }

    /**
     * Obtiene la recomendación inmediatamente anterior al último recálculo.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerRecomendacionAnterior(Ruta $ruta): ?array
    {
        return Cache::get($this->cacheKeyAnterior((int) $ruta->getKey()));
    }

    /**
     * Obtiene el registro de contraste entre la recomendación previa y la actual.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerHistorialCambio(Ruta $ruta): ?array
    {
        return Cache::get($this->cacheKeyHistorial((int) $ruta->getKey()));
    }
}
