{{-- ── SECCIÓN: MOTOR DE RECOMENDACIÓN PEATONAL ACO ──────────────────────── --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-slate-200" id="contenedor-seccion-aco">
    {{-- Encabezado Administrativo --}}
    <div class="px-6 py-4 border-b flex flex-col md:flex-row md:items-center justify-between gap-3" style="background-color: #f8fafc">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-sm" style="background-color:#2d6a27">
                <i class="fas fa-route text-lg"></i>
            </span>
            <div>
                <h3 class="font-bold text-gray-800 text-base uppercase tracking-wide flex items-center gap-2">
                    RUTA RECOMENDADA
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 normal-case">
                        Optimización Bioinspirada
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 uppercase">
                    EVALUACIÓN MULTIOBJETIVO DE DISTANCIA Y SEGURIDAD PEATONAL
                </p>
            </div>
        </div>

        {{-- Badge de Estado General --}}
        <div class="flex items-center gap-2">
            @if($recomendacionAco && $recomendacionAco->success)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-sm">
                    <i class="fas fa-check-circle"></i> RECOMENDACIÓN ACTIVA
                </span>
            @elseif($analisisAco->esOptimizable)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-sm">
                    <i class="fas fa-clock"></i> LISTO PARA CALCULAR
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 shadow-sm">
                    <i class="fas fa-info-circle"></i> {{ $analisisAco->etiquetaEstado }}
                </span>
            @endif
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- Alerta Reactiva: Recomendación Desactualizada por Cambio en Obstáculos --}}
        @if(!empty($recomendacionDesactualizada))
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm" id="alerta-desactualizada-aco">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 text-lg mt-0.5"></i>
                        <div>
                            <h5 class="text-xs font-black uppercase text-amber-900 tracking-wide flex items-center gap-1.5">
                                RECOMENDACIÓN DESACTUALIZADA
                                <span class="bg-amber-200 text-amber-900 text-[10px] px-1.5 py-0.2 rounded font-bold">Cambio detectado</span>
                            </h5>
                            <p class="text-xs text-amber-800 mt-0.5" id="texto-motivo-desactualizacion">
                                {{ $infoDesactualizacion['motivo'] ?? 'Se actualizó la información de riesgo de uno o más tramos de la ruta.' }}
                            </p>
                        </div>
                    </div>
                    @if(Auth::user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'))
                        <button type="button"
                            onclick="ejecutarRecalculoAco()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white shadow uppercase bg-amber-600 hover:bg-amber-700 transition self-start sm:self-auto">
                            <i class="fas fa-sync-alt"></i> Recalcular Ahora
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Historial de Trazabilidad: Último Cambio de Recomendación --}}
        @if(!empty($historialCambioAco) && !empty($historialCambioAco['hay_cambio']))
            <div class="bg-indigo-50/70 border border-indigo-200 rounded-xl p-4 shadow-sm" id="seccion-historial-cambio-aco">
                <div class="flex items-center justify-between gap-2 border-b border-indigo-100 pb-2 mb-3">
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-900 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-600"></i> ÚLTIMO CAMBIO DE RECOMENDACIÓN
                    </span>
                    <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-100 px-2.5 py-0.5 rounded-full" id="aco-historial-fecha">
                        {{ $historialCambioAco['fecha_cambio'] ?? now()->format('Y-m-d H:i') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs mb-2">
                    <div class="bg-white p-3 rounded-lg border border-slate-200">
                        <p class="font-bold text-slate-500 uppercase text-[10px]">RECOMENDACIÓN ANTERIOR</p>
                        <p class="font-bold text-slate-800 mt-1" id="aco-historial-ant-camino">
                            Nodos: {{ implode(' → ', $historialCambioAco['anterior']['nodos'] ?? []) }}
                        </p>
                        <div class="flex items-center gap-3 mt-1.5 text-slate-600 font-mono text-[11px]">
                            <span>Distancia: <strong id="aco-historial-ant-dist">{{ $historialCambioAco['anterior']['distancia'] ?? 0 }} m</strong></span>
                            <span>Riesgo: <strong id="aco-historial-ant-riesgo">{{ $historialCambioAco['anterior']['riesgo'] ?? 0 }}</strong></span>
                            <span>Costo: <strong id="aco-historial-ant-costo">{{ $historialCambioAco['anterior']['costo'] ?? 0 }}</strong></span>
                        </div>
                    </div>

                    <div class="bg-emerald-50/80 p-3 rounded-lg border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-emerald-800 uppercase text-[10px]">RECOMENDACIÓN ACTUAL</p>
                            @if(!empty($historialCambioAco['cambio_ruta_efectivo']))
                                <span class="bg-emerald-200 text-emerald-900 px-1.5 py-0.2 rounded font-black text-[9px] uppercase tracking-wide">
                                    ¡NUEVA TRAYECTORIA ÓPTIMA!
                                </span>
                            @endif
                        </div>
                        <p class="font-bold text-slate-900 mt-1" id="aco-historial-act-camino">
                            Nodos: {{ implode(' → ', $historialCambioAco['actual']['nodos'] ?? []) }}
                        </p>
                        <div class="flex items-center gap-3 mt-1.5 text-slate-700 font-mono text-[11px]">
                            <span>Distancia: <strong id="aco-historial-act-dist">{{ $historialCambioAco['actual']['distancia'] ?? 0 }} m</strong></span>
                            <span>Riesgo: <strong id="aco-historial-act-riesgo">{{ $historialCambioAco['actual']['riesgo'] ?? 0 }}</strong></span>
                            <span>Costo: <strong id="aco-historial-act-costo">{{ $historialCambioAco['actual']['costo'] ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-slate-600 italic mt-1.5" id="aco-historial-motivo">
                    <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                    Motivo: "{{ $historialCambioAco['motivo'] ?? 'Se actualizó la información de riesgo de uno o más tramos tras la resolución de obstáculos.' }}"
                </p>
            </div>
        @endif

        {{-- ── 1. VISTA PRINCIPAL PARA EL ADMINISTRADOR ──────────────────────── --}}
        @if($recomendacionAco && $recomendacionAco->success)
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-white border-2 border-emerald-500/40 rounded-xl p-5 shadow-sm" id="tarjeta-recomendacion-activa">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-emerald-100 pb-4 mb-4">
                    <div>
                        <span class="text-[10px] font-black tracking-widest uppercase text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">
                            RECOMENDACIÓN VIGENTE
                        </span>
                        <h4 class="text-lg font-black text-slate-900 uppercase mt-1 flex items-center gap-2">
                            RUTA RECOMENDADA ACTUALMENTE
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Último cálculo registrado: <strong id="aco-fecha-calculo">{{ $recomendacionAco->fechaCalculo }}</strong>
                        </p>
                    </div>

                    {{-- Botón de Recálculo Inmediato --}}
                    @if(Auth::user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'))
                        <div class="flex items-center gap-2">
                            <button type="button"
                                id="btn-recalcular-aco"
                                onclick="ejecutarRecalculoAco()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold text-white shadow transition hover:opacity-95"
                                style="background-color: #2d6a27">
                                <i class="fas fa-sync-alt" id="icono-btn-recalcular"></i>
                                <span id="texto-btn-recalcular">RECALCULAR RECOMENDACIÓN</span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Métricas de la Recomendación para Administradores --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center mb-4">
                    <div class="bg-white p-3.5 rounded-lg border border-emerald-100 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">DISTANCIA</p>
                        <p class="text-xl font-black text-slate-900 font-mono mt-0.5" id="aco-distancia">
                            {{ number_format($recomendacionAco->distanciaTotal, 1) }} M
                        </p>
                        <p class="text-[10px] text-slate-500 mt-0.5">
                            @if($recomendacionAco->m8 !== null && $recomendacionAco->m8 > 0)
                                +{{ round($recomendacionAco->m8, 1) }}% vs más corta
                            @else
                                Trayecto directo
                            @endif
                        </p>
                    </div>

                    <div class="bg-white p-3.5 rounded-lg border border-emerald-100 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">RIESGO ESTIMADO</p>
                        @php
                            $badgeNivel = match($recomendacionAco->nivelRiesgoEstimado) {
                                'BAJO'    => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'MEDIO'   => 'bg-amber-100 text-amber-800 border-amber-300',
                                'ALTO'    => 'bg-orange-100 text-orange-800 border-orange-300',
                                default   => 'bg-rose-100 text-rose-800 border-rose-300',
                            };
                        @endphp
                        <div class="mt-1">
                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-black uppercase border {{ $badgeNivel }}" id="aco-nivel-riesgo">
                                {{ $recomendacionAco->nivelRiesgoEstimado }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">
                            @if($recomendacionAco->m7 !== null && $recomendacionAco->m7 > 0)
                                -{{ round($recomendacionAco->m7, 1) }}% de peligro
                            @else
                                Sin peligro crítico
                            @endif
                        </p>
                    </div>

                    <div class="bg-white p-3.5 rounded-lg border border-emerald-100 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OBSTÁCULOS ACTIVOS</p>
                        <p class="text-xl font-black font-mono mt-0.5 {{ $recomendacionAco->obstaculosActivosCount > 0 ? 'text-rose-600' : 'text-emerald-700' }}" id="aco-obstaculos-activos">
                            {{ $recomendacionAco->obstaculosActivosCount }}
                        </p>
                        <p class="text-[10px] text-slate-500 mt-0.5">En tramos de la red</p>
                    </div>

                    <div class="bg-white p-3.5 rounded-lg border border-emerald-100 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ALTERNATIVAS</p>
                        <p class="text-xl font-black text-slate-900 font-mono mt-0.5" id="aco-alternativas">
                            {{ $recomendacionAco->cantidadAlternativas }}
                        </p>
                        <p class="text-[10px] text-slate-500 mt-0.5">Caminos evaluados</p>
                    </div>
                </div>

                {{-- Explicación en Lenguaje Natural --}}
                <div class="bg-white/90 border border-emerald-200 rounded-lg p-3.5 text-xs text-slate-700">
                    <p class="font-bold uppercase text-slate-900 mb-1 flex items-center gap-1.5">
                        <i class="fas fa-question-circle text-emerald-700"></i>
                        ¿POR QUÉ SE RECOMIENDA ESTA RUTA?
                    </p>
                    <p class="leading-relaxed text-slate-800 font-medium" id="aco-motivo-recomendacion">
                        "{{ $recomendacionAco->motivoRecomendacion }}"
                    </p>
                </div>
            </div>
        @elseif($analisisAco->esOptimizable)
            {{-- Ruta lista pero aún no calculada --}}
            <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 text-center shadow-sm">
                <div class="text-3xl text-amber-500 mb-2">🧭</div>
                <h4 class="text-sm font-bold text-amber-900 uppercase">RECOMENDACIÓN LISTA PARA SER CALCULADA</h4>
                <p class="text-xs text-amber-700 max-w-xl mx-auto mt-1">
                    Esta ruta cuenta con {{ $analisisAco->cantidadAlternativas }} caminos alternativos y datos cuantitativos de seguridad.
                    Haga clic para ejecutar el motor de optimización bioinspirado y generar la ruta recomendada.
                </p>
                @if(Auth::user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'))
                    <button type="button"
                        id="btn-recalcular-aco"
                        onclick="ejecutarRecalculoAco()"
                        class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-lg text-xs font-bold text-white shadow uppercase"
                        style="background-color: #2d6a27">
                        <i class="fas fa-play" id="icono-btn-recalcular"></i>
                        <span id="texto-btn-recalcular">CALCULAR RUTA RECOMENDADA</span>
                    </button>
                @endif
            </div>
        @else
            {{-- Ruta no optimizable --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-600">
                <div class="flex items-start gap-2.5">
                    <i class="fas fa-info-circle text-slate-400 text-base mt-0.5"></i>
                    <div>
                        <p class="font-bold uppercase text-slate-800">
                            OPTIMIZACIÓN ACO NO DISPONIBLE ACTUALMENTE PARA ESTA RUTA
                        </p>
                        <p class="mt-0.5">
                            @if(!$analisisAco->tieneTramos())
                                La ruta no posee tramos viales asociados.
                            @elseif($analisisAco->esLineal)
                                La ruta corresponde a una trayectoria en serie única sin bifurcaciones alternativas.
                            @else
                                {{ implode(' ', $analisisAco->motivosBloqueo) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 2. LEYENDA DEL MAPA Y CAPAS CARTOGRÁFICAS ────────────────────── --}}
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-xs shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="font-bold text-slate-700 uppercase text-[11px]">LEYENDA:</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-700">
                        <span class="w-4 h-1.5 bg-emerald-600 rounded inline-block"></span>
                        <strong class="text-emerald-900">Ruta recomendada</strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="w-4 h-1 border-b-2 border-dashed border-amber-600 inline-block"></span>
                        <span>Ruta más corta</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                        <span class="w-3 h-0.5 bg-slate-400 inline-block"></span>
                        <span>Otras alternativas</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600 inline-block"></span>
                        <span>Obstáculo activo</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block text-white text-[8px] flex items-center justify-center">✓</span>
                        <span>Obstáculo resuelto</span>
                    </span>
                </div>

                {{-- Toggle de Capa Histórica de Obstáculos Resueltos --}}
                <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] text-slate-600 select-none">
                    <input type="checkbox" id="chk-capa-resueltos" onchange="toggleCapaObstaculosResueltos(this.checked)" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span>Mostrar obstáculos resueltos (histórico)</span>
                </label>
            </div>
        </div>

        {{-- ── 3. DETALLES TÉCNICOS ACO (Acordeón Colapsable) ──────────────── --}}
        <details class="group bg-slate-50 rounded-lg border border-slate-200 overflow-hidden" id="acordeon-detalles-tecnicos">
            <summary class="px-5 py-3.5 cursor-pointer font-bold text-xs text-slate-700 uppercase flex items-center justify-between hover:bg-slate-100 transition select-none">
                <span class="flex items-center gap-2">
                    <i class="fas fa-sliders-h text-slate-500"></i>
                    VER DETALLES TÉCNICOS Y BENCHMARKING DE ACO
                </span>
                <span class="text-[10px] text-slate-400 font-mono uppercase group-open:rotate-180 transition-transform">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </summary>

            <div class="p-5 border-t border-slate-200 space-y-5 bg-white">

                {{-- Parámetros e Hiperparámetros del Algoritmo --}}
                <div>
                    <h5 class="text-xs font-bold text-slate-800 uppercase mb-2 flex items-center gap-1.5">
                        <i class="fas fa-atom text-slate-500"></i> PARÁMETROS DEL MOTOR METAHEURÍSTICO
                    </h5>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-center text-xs">
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">ALFA (FEROMONA)</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">1.0</p>
                        </div>
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">BETA (HEURÍSTICA)</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">2.0</p>
                        </div>
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">RHO (EVAPORACIÓN)</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">0.20</p>
                        </div>
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">Q (DEPÓSITO)</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">100.0</p>
                        </div>
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">HORMIGAS</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">20</p>
                        </div>
                        <div class="p-2.5 rounded border border-slate-200 bg-slate-50">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">ITERACIONES</span>
                            <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">25</p>
                        </div>
                    </div>
                </div>

                {{-- Función de Costo Canónica --}}
                <div class="bg-slate-50 p-3 rounded border border-slate-200 text-xs">
                    <p class="font-bold text-slate-800 uppercase mb-1">
                        FUNCIÓN CANÓNICA DE COSTO MULTIOBJETIVO
                    </p>
                    <p class="font-mono text-slate-700">
                        C(e) = wD · D_norm(e) + wR · R_norm(e) &nbsp;|&nbsp; wD = 0.50, wR = 0.50
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Garantía metodológica: El algoritmo determinista de referencia (Dijkstra) ejecuta exactamente la misma función de costo.
                    </p>
                </div>

                {{-- Tabla de Métricas M1 - M8 --}}
                @if($recomendacionAco && !empty($recomendacionAco->metricas))
                    <div>
                        <h5 class="text-xs font-bold text-slate-800 uppercase mb-2 flex items-center gap-1.5">
                            <i class="fas fa-chart-bar text-slate-500"></i> CUADRO DE MÉTRICAS FORMALES (M1 — M8)
                        </h5>
                        <div class="overflow-x-auto border border-slate-200 rounded">
                            <table class="min-w-full divide-y divide-slate-200 text-xs text-left" id="tabla-metricas-m">
                                <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="px-3 py-2 text-center w-12">MÉTRICA</th>
                                        <th class="px-3 py-2">CONCEPTO</th>
                                        <th class="px-3 py-2 text-right">VALOR</th>
                                        <th class="px-3 py-2 text-center">UNIDAD</th>
                                        <th class="px-3 py-2">DESCRIPCIÓN METODOLÓGICA</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($recomendacionAco->metricas as $cod => $m)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-3 py-2 text-center font-mono font-bold text-slate-700">{{ $cod }}</td>
                                            <td class="px-3 py-2 font-bold text-slate-800 uppercase">{{ $m['nombre'] }}</td>
                                            <td class="px-3 py-2 text-right font-mono font-black text-slate-900" id="metrica-val-{{ $cod }}">
                                                {{ $m['valor'] !== null ? $m['valor'] : 'N/A' }}
                                            </td>
                                            <td class="px-3 py-2 text-center text-slate-500 font-mono">{{ $m['unidad'] }}</td>
                                            <td class="px-3 py-2 text-slate-500 text-[11px]">{{ $m['descripcion'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Checklist de Preparación --}}
                <div>
                    <h5 class="text-xs font-bold text-slate-800 uppercase mb-2 flex items-center gap-1.5">
                        <i class="fas fa-check-square text-slate-500"></i> CHECKLIST TOPOLÓGICO Y DE SEGURIDAD
                    </h5>
                    <div class="space-y-1.5">
                        @foreach($analisisAco->checklist as $item)
                            <div class="flex items-center justify-between p-2 rounded border text-xs {{ $item['cumple'] ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/40 border-rose-100' }}">
                                <span class="font-bold uppercase text-[11px] {{ $item['cumple'] ? 'text-emerald-900' : 'text-rose-900' }}">
                                    {!! $item['cumple'] ? '<i class="fas fa-check text-emerald-600 mr-1.5"></i>' : '<i class="fas fa-times text-rose-500 mr-1.5"></i>' !!}
                                    {{ $item['titulo'] }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-500">{{ $item['detalle'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tabla Topológica de Tramos Físicos --}}
                @if($analisisAco->tieneTramos())
                    <div>
                        <h5 class="text-xs font-bold text-slate-800 uppercase mb-2 flex items-center gap-1.5">
                            <i class="fas fa-network-wired text-slate-500"></i> TABLA DE TRAMOS Y RIESGO VIAL ({{ count($analisisAco->tramosDetalle) }})
                        </h5>
                        <div class="overflow-x-auto border border-slate-200 rounded max-h-72">
                            <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                                <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-center">ORD</th>
                                        <th class="px-3 py-2 text-center">ID</th>
                                        <th class="px-3 py-2">NOMBRE</th>
                                        <th class="px-3 py-2 text-right">DISTANCIA</th>
                                        <th class="px-3 py-2 text-center">NODOS</th>
                                        <th class="px-3 py-2 text-center">EVALUACIÓN</th>
                                        <th class="px-3 py-2 text-center">OBSTÁCULOS</th>
                                        <th class="px-3 py-2 text-center">COBERTURA</th>
                                        <th class="px-3 py-2 text-center">ESTADO ACO</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($analisisAco->tramosDetalle as $t)
                                        <tr class="hover:bg-slate-50 cursor-pointer" onclick="destacarTramoEnMapa({{ $t['id'] }})">
                                            <td class="px-3 py-1.5 text-center font-mono font-bold">{{ $t['orden'] ?? '—' }}</td>
                                            <td class="px-3 py-1.5 text-center font-mono text-slate-500">{{ $t['id'] }}</td>
                                            <td class="px-3 py-1.5 font-bold uppercase text-slate-800">{{ $t['nombre'] }}</td>
                                            <td class="px-3 py-1.5 text-right font-mono font-bold">{{ number_format($t['longitud'], 1) }} M</td>
                                            <td class="px-3 py-1.5 text-center font-mono text-[10px] text-slate-700 bg-slate-50">{{ $t['nodo_inicial'] }} → {{ $t['nodo_final'] }}</td>
                                            <td class="px-3 py-1.5 text-center text-[10px] font-bold {{ $t['estado_evaluacion'] === 'NO_EVALUADO' ? 'text-slate-400' : 'text-emerald-700' }}">
                                                {{ $t['estado_evaluacion'] === 'NO_EVALUADO' ? 'NO EVALUADO' : $t['nivel_seguridad'] }}
                                            </td>
                                            <td class="px-3 py-1.5 text-center font-mono font-bold {{ $t['obstaculos_count'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                                {{ $t['obstaculos_count'] > 0 ? $t['obstaculos_count'] : '0' }}
                                            </td>
                                            <td class="px-3 py-1.5 text-center">
                                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded border {{ $t['cobertura_datos'] === 'COMPLETO' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($t['cobertura_datos'] === 'PARCIAL' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                                    {{ $t['cobertura_datos'] }}
                                                </span>
                                            </td>
                                            {{-- CORRECCIÓN CONCEPTUAL FASE 5B (Sección 19): NUNCA MOSTRAR "Sin riesgo" SI COBERTURA ES SIN_DATOS --}}
                                            <td class="px-3 py-1.5 text-center">
                                                @if($t['cobertura_datos'] === 'SIN_DATOS')
                                                    <span class="text-[10px] font-bold text-amber-700 uppercase" title="La ausencia de datos no equivale a riesgo cero">
                                                        <i class="fas fa-exclamation-circle mr-0.5"></i> RIESGO NO EVALUADO
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-bold text-emerald-700 uppercase">
                                                        <i class="fas fa-check-circle mr-0.5"></i> EVALUADO
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </details>

    </div>
</div>

{{-- Script de Recálculo Dinámico AJAX y Control de Capas Leaflet --}}
@push('scripts')
<script>
    window.recomendacionAcoData = @json($recomendacionAco ? $recomendacionAco->toArray() : null);
    window.capaRecomendadaAco = null;
    window.capaMasCortaAco = null;
    window.capasAlternativasAco = [];
    window.capaObstaculosActivos = null;
    window.capaObstaculosResueltos = null;

    function renderizarCapasCartograficasAco(data) {
        if (!window.leafletMap || !data) return;

        // Limpiar capas previas si existen
        if (window.capaRecomendadaAco) window.leafletMap.removeLayer(window.capaRecomendadaAco);
        if (window.capaMasCortaAco) window.leafletMap.removeLayer(window.capaMasCortaAco);
        if (window.capasAlternativasAco && window.capasAlternativasAco.length) {
            window.capasAlternativasAco.forEach(l => window.leafletMap.removeLayer(l));
            window.capasAlternativasAco = [];
        }
        if (window.capaObstaculosActivos) window.leafletMap.removeLayer(window.capaObstaculosActivos);
        if (window.capaObstaculosResueltos) window.leafletMap.removeLayer(window.capaObstaculosResueltos);

        const bounds = window.L.latLngBounds([]);

        // 1. Alternativas neutras de fondo (líneas finas grises)
        if (Array.isArray(data.geometria_alternativas)) {
            data.geometria_alternativas.forEach(geo => {
                if (Array.isArray(geo) && geo.length >= 2) {
                    const pts = geo.map(p => [p.lat, p.lng]);
                    const l = window.L.polyline(pts, {
                        color: '#94a3b8',
                        weight: 3,
                        opacity: 0.45
                    }).addTo(window.leafletMap);
                    l.bindPopup('<div class="p-1 text-xs"><strong>TRAMO ALTERNATIVO</strong><br><span class="text-[10px] text-slate-500">Red topológica analizada</span></div>');
                    window.capasAlternativasAco.push(l);
                    bounds.extend(l.getBounds());
                }
            });
        }

        // 2. Ruta más corta (línea discontinua ámbar/roja)
        if (Array.isArray(data.geometria_mas_corta)) {
            const cortoPuntos = [];
            data.geometria_mas_corta.forEach(geo => {
                if (Array.isArray(geo)) {
                    geo.forEach(p => cortoPuntos.push([p.lat, p.lng]));
                }
            });
            if (cortoPuntos.length >= 2) {
                window.capaMasCortaAco = window.L.polyline(cortoPuntos, {
                    color: '#d97706',
                    weight: 4,
                    dashArray: '8, 8',
                    opacity: 0.85
                }).addTo(window.leafletMap);
                window.capaMasCortaAco.bindPopup(
                    `<div class="p-1 text-xs font-bold text-amber-900">
                        CAMINO DE MENOR DISTANCIA<br>
                        <span class="text-[11px] font-normal text-slate-700">Distancia: <strong>${data.distancia_camino_corto} M</strong> · Riesgo acumulado: <strong>${data.riesgo_camino_corto}</strong></span>
                    </div>`
                );
                bounds.extend(window.capaMasCortaAco.getBounds());
            }
        }

        // 3. RUTA RECOMENDADA ACO (Línea gruesa esmeralda destacada)
        if (Array.isArray(data.geometria_recomendada)) {
            const recoPuntos = [];
            data.geometria_recomendada.forEach(geo => {
                if (Array.isArray(geo)) {
                    geo.forEach(p => recoPuntos.push([p.lat, p.lng]));
                }
            });
            if (recoPuntos.length >= 2) {
                window.capaRecomendadaAco = window.L.polyline(recoPuntos, {
                    color: '#059669',
                    weight: 8,
                    opacity: 0.95
                }).addTo(window.leafletMap);
                window.capaRecomendadaAco.bindPopup(
                    `<div class="p-1 text-xs">
                        <strong class="text-emerald-900 uppercase">━━━ RUTA RECOMENDADA (ACO)</strong><br>
                        <span class="text-[11px] text-slate-700">Distancia: <strong>${data.distancia_total} M</strong> · Riesgo: <strong>${data.nivel_riesgo_estimado}</strong></span><br>
                        <span class="text-[10px] text-slate-500 italic mt-1 block">"${data.motivo_recomendacion}"</span>
                    </div>`
                );
                bounds.extend(window.capaRecomendadaAco.getBounds());
            }
        }

        // 4. Marcadores de Obstáculos Activos y Resueltos
        const obstaculos = data.obstaculos_detalle || [];
        const activosMarkers = [];
        const resueltosMarkers = [];

        obstaculos.forEach(obs => {
            if (obs.lat && obs.lng) {
                if (obs.es_activo) {
                    const m = window.L.circleMarker([obs.lat, obs.lng], {
                        radius: 7,
                        color: '#b91c1c',
                        fillColor: '#ef4444',
                        fillOpacity: 0.9,
                        weight: 2
                    }).bindPopup(
                        `<div class="p-1 text-xs">
                            <strong class="text-rose-800 uppercase">● OBSTÁCULO ACTIVO</strong><br>
                            <span class="font-bold text-slate-800">${obs.tipo}:</span> ${obs.descripcion}<br>
                            <span class="text-[10px] text-slate-500 uppercase mt-0.5 block">Tramo: ${obs.tramo_nom}</span>
                        </div>`
                    );
                    activosMarkers.push(m);
                    bounds.extend([obs.lat, obs.lng]);
                } else {
                    const m = window.L.circleMarker([obs.lat, obs.lng], {
                        radius: 6,
                        color: '#047857',
                        fillColor: '#10b981',
                        fillOpacity: 0.8,
                        weight: 1
                    }).bindPopup(
                        `<div class="p-1 text-xs">
                            <strong class="text-emerald-800 uppercase">✓ OBSTÁCULO RESUELTO</strong><br>
                            <span class="font-bold text-slate-800">${obs.tipo}:</span> ${obs.descripcion}<br>
                            <span class="text-[10px] text-slate-500 uppercase mt-0.5 block">Estado: RESUELTO (no afecta riesgo vigente)</span>
                        </div>`
                    );
                    resueltosMarkers.push(m);
                }
            }
        });

        if (activosMarkers.length) {
            window.capaObstaculosActivos = window.L.featureGroup(activosMarkers).addTo(window.leafletMap);
        }

        if (resueltosMarkers.length) {
            window.capaObstaculosResueltos = window.L.featureGroup(resueltosMarkers);
            const chk = document.getElementById('chk-capa-resueltos');
            if (chk && chk.checked) {
                window.capaObstaculosResueltos.addTo(window.leafletMap);
            }
        }

        if (bounds.isValid()) {
            window.leafletMap.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    function toggleCapaObstaculosResueltos(mostrar) {
        if (!window.leafletMap || !window.capaObstaculosResueltos) return;
        if (mostrar) {
            window.capaObstaculosResueltos.addTo(window.leafletMap);
        } else {
            window.leafletMap.removeLayer(window.capaObstaculosResueltos);
        }
    }

    function ejecutarRecalculoAco() {
        const btn = document.getElementById('btn-recalcular-aco');
        const icono = document.getElementById('icono-btn-recalcular');
        const texto = document.getElementById('texto-btn-recalcular');

        if (btn) btn.disabled = true;
        if (icono) icono.className = 'fas fa-spinner fa-spin';
        if (texto) texto.textContent = 'CALCULANDO OPTIMIZACIÓN ACO...';

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('rutas.aco.recalcular', $ruta) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.recomendacion) {
                window.recomendacionAcoData = data.recomendacion;
                renderizarCapasCartograficasAco(data.recomendacion);

                // Actualizar interfaz administrativa
                const elDist = document.getElementById('aco-distancia');
                if (elDist) elDist.textContent = `${data.recomendacion.distancia_total} M`;

                const elRiesgo = document.getElementById('aco-nivel-riesgo');
                if (elRiesgo) elRiesgo.textContent = data.recomendacion.nivel_riesgo_estimado;

                const elObs = document.getElementById('aco-obstaculos-activos');
                if (elObs) elObs.textContent = data.recomendacion.obstaculos_activos_count;

                const elAlt = document.getElementById('aco-alternativas');
                if (elAlt) elAlt.textContent = data.recomendacion.cantidad_alternativas;

                const elMotivo = document.getElementById('aco-motivo-recomendacion');
                if (elMotivo) elMotivo.textContent = `"${data.recomendacion.motivo_recomendacion}"`;

                const elFecha = document.getElementById('aco-fecha-calculo');
                if (elFecha) elFecha.textContent = data.recomendacion.fecha_calculo;

                // Ocultar alerta de desactualización si existía
                const alertaDesact = document.getElementById('alerta-desactualizada-aco');
                if (alertaDesact) alertaDesact.style.display = 'none';

                // Actualizar o mostrar historial de cambio reactivo
                if (data.historial_cambio && data.historial_cambio.hay_cambio) {
                    const seccionHist = document.getElementById('seccion-historial-cambio-aco');
                    if (seccionHist) {
                        seccionHist.style.display = 'block';
                        const h = data.historial_cambio;
                        const elHFecha = document.getElementById('aco-historial-fecha');
                        if (elHFecha) elHFecha.textContent = h.fecha_cambio || '';
                        const elHAntCamino = document.getElementById('aco-historial-ant-camino');
                        if (elHAntCamino) elHAntCamino.textContent = 'Nodos: ' + (h.anterior?.nodos || []).join(' → ');
                        const elHAntDist = document.getElementById('aco-historial-ant-dist');
                        if (elHAntDist) elHAntDist.textContent = (h.anterior?.distancia || 0) + ' m';
                        const elHAntRiesgo = document.getElementById('aco-historial-ant-riesgo');
                        if (elHAntRiesgo) elHAntRiesgo.textContent = h.anterior?.riesgo || 0;
                        const elHAntCosto = document.getElementById('aco-historial-ant-costo');
                        if (elHAntCosto) elHAntCosto.textContent = h.anterior?.costo || 0;

                        const elHActCamino = document.getElementById('aco-historial-act-camino');
                        if (elHActCamino) elHActCamino.textContent = 'Nodos: ' + (h.actual?.nodos || []).join(' → ');
                        const elHActDist = document.getElementById('aco-historial-act-dist');
                        if (elHActDist) elHActDist.textContent = (h.actual?.distancia || 0) + ' m';
                        const elHActRiesgo = document.getElementById('aco-historial-act-riesgo');
                        if (elHActRiesgo) elHActRiesgo.textContent = h.actual?.riesgo || 0;
                        const elHActCosto = document.getElementById('aco-historial-act-costo');
                        if (elHActCosto) elHActCosto.textContent = h.actual?.costo || 0;

                        const elHMotivo = document.getElementById('aco-historial-motivo');
                        if (elHMotivo) elHMotivo.innerHTML = '<i class="fas fa-info-circle text-indigo-500 mr-1"></i> Motivo: "' + (h.motivo || '') + '"';
                    }
                }

                // Actualizar métricas técnicas
                if (data.recomendacion.metricas) {
                    for (const [k, v] of Object.entries(data.recomendacion.metricas)) {
                        const cell = document.getElementById(`metrica-val-${k}`);
                        if (cell) cell.textContent = (v.valor !== null) ? v.valor : 'N/A';
                    }
                }

                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'RECOMENDACIÓN ACO ACTUALIZADA',
                        text: 'El mapa y las métricas han sido recalculadas exitosamente.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'NO SE PUDO OPTIMIZAR',
                        text: data.mensaje || 'La ruta no cumple las condiciones para optimización.',
                        confirmButtonColor: '#2d6a27'
                    });
                }
            }
        })
        .catch(err => {
            console.error('Error en recálculo ACO:', err);
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ERROR DE COMUNICACIÓN',
                    text: 'Ocurrió un error al contactar al motor de optimización.',
                    confirmButtonColor: '#2d6a27'
                });
            }
        })
        .finally(() => {
            if (btn) btn.disabled = false;
            if (icono) icono.className = 'fas fa-sync-alt';
            if (texto) texto.textContent = 'RECALCULAR RECOMENDACIÓN';
        });
    }

    // Inicializar renderizado una vez que Leaflet esté disponible
    document.addEventListener('DOMContentLoaded', () => {
        const interval = setInterval(() => {
            if (window.leafletMap && window.recomendacionAcoData) {
                renderizarCapasCartograficasAco(window.recomendacionAcoData);
                clearInterval(interval);
            }
        }, 150);
        setTimeout(() => clearInterval(interval), 4000);
    });
</script>
@endpush
