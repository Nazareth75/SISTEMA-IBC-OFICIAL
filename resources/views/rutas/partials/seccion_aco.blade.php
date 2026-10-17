{{-- ── SECCIÓN: CAPACIDAD Y DIAGNÓSTICO DE OPTIMIZACIÓN ACO ──────────────── --}}
<div class="bg-white rounded-lg shadow overflow-hidden border border-slate-200">
    {{-- Encabezado de la sección ACO --}}
    <div class="px-6 py-4 border-b flex flex-col md:flex-row md:items-center justify-between gap-3" style="background-color: #f8fafc">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-white shadow-sm" style="background-color:#2d6a27">
                <i class="fas fa-brain text-sm"></i>
            </span>
            <div>
                <h3 class="font-bold text-gray-800 text-base uppercase tracking-wide flex items-center gap-2">
                    OPTIMIZACIÓN DE RUTA CON ACO
                    <span class="text-xs font-normal text-slate-500 normal-case">(Algoritmo de Colonia de Hormigas)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 uppercase">
                    EVALUACIÓN AUTOMÁTICA DE PREPARACIÓN TOPOLÓGICA Y MULTIOBJETIVO
                </p>
            </div>
        </div>

        {{-- Badge consolidado de Estado ACO --}}
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border shadow-sm {{ $analisisAco->colorBadge }}">
                <i class="{{ $analisisAco->iconoFa }}"></i>
                <span>ESTADO: {{ $analisisAco->etiquetaEstado }}</span>
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- ── 1. BLOQUE EDUCATIVO: CONCEPTO DE RUTA Y TRAMO ──────────────── --}}
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-xs text-slate-700">
            <div class="flex items-start gap-3">
                <div class="text-emerald-700 text-lg mt-0.5">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="space-y-2">
                    <p class="font-bold uppercase text-slate-800 tracking-wide">
                        ¿CÓMO OPERA LA OPTIMIZACIÓN ACO SOBRE EL SISTEMA DE RUTAS?
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-slate-600">
                        <div class="bg-white p-3 rounded border border-slate-200">
                            <p class="font-bold text-slate-800 uppercase mb-1">
                                <i class="fas fa-route text-emerald-600 mr-1"></i> RUTA COMPLETA
                            </p>
                            <p>
                                Es el <strong>recorrido total planificado</strong> entre un punto de origen y un punto de destino. Representa la necesidad de desplazamiento del afiliado invidente.
                            </p>
                        </div>
                        <div class="bg-white p-3 rounded border border-slate-200">
                            <p class="font-bold text-slate-800 uppercase mb-1">
                                <i class="fas fa-road text-amber-600 mr-1"></i> TRAMO FÍSICO
                            </p>
                            <p>
                                Es un <strong>segmento vial físico reutilizable</strong> comprendido entre dos intersecciones o nodos conectados. Posee su propia longitud y atributos de seguridad.
                            </p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 pt-1">
                        <strong>Principio bioinspirado:</strong> ACO modela los tramos como aristas de un grafo vial ponderado. Para que las hormigas artificiales puedan comparar caminos y converger hacia la ruta más segura y eficiente, <em>debe existir al menos un punto de bifurcación con dos o más alternativas posibles</em> entre origen y destino.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── 2. LLAMADO A LA ACCIÓN CUANDO LA RUTA NO TIENE TRAMOS ───────── --}}
        @if(!$analisisAco->tieneTramos())
            <div class="rounded-lg border border-dashed border-amber-300 bg-amber-50 p-5 text-center">
                <div class="text-3xl text-amber-500 mb-2">🛣️</div>
                <h4 class="text-sm font-bold text-amber-900 uppercase">SIGUIENTE PASO: CONFIGURAR TRAMOS DE LA RUTA</h4>
                <p class="text-xs text-amber-700 max-w-xl mx-auto mt-1">
                    Esta ruta fue registrada en el sistema pero aún no cuenta con segmentos peatonales físicos asociados. 
                    Para activar el análisis topológico y evaluar su optimización bioinspirada, es necesario registrar o asociar sus tramos.
                </p>
                @if(in_array($ruta->est_rut, ['BORRADOR', 'DESACTUALIZADA']) && Auth::user()->puedeEditarRuta())
                    <a href="{{ route('rutas.edit', $ruta) }}"
                        class="inline-flex items-center gap-2 mt-4 px-4 py-2 text-xs font-bold text-white rounded shadow uppercase"
                        style="background-color: #2d6a27">
                        <i class="fas fa-plus"></i> AGREGAR O ASOCIAR TRAMOS EN EDICIÓN
                    </a>
                @endif
            </div>
        @endif

        {{-- ── 3. TARJETAS MÉTRICAS DE DIAGNÓSTICO TOPOLÓGICO ─────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Tramos registrados --}}
            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TRAMOS FÍSICOS</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 font-mono">{{ $analisisAco->cantidadTramos }}</span>
                    <span class="text-xs text-slate-500 uppercase font-semibold">segmentos</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Asociados vía N:M</p>
            </div>

            {{-- Nodos detectados --}}
            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NODOS TOPOLÓGICOS</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 font-mono">{{ $analisisAco->cantidadNodos }}</span>
                    <span class="text-xs text-slate-500 uppercase font-semibold">intersecciones</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Tolerancia: 8.0 m</p>
            </div>

            {{-- Caminos / Alternativas --}}
            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">CAMINOS POSIBLES</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black font-mono {{ $analisisAco->cantidadAlternativas >= 2 ? 'text-emerald-700' : 'text-amber-600' }}">
                        {{ $analisisAco->cantidadAlternativas }}
                    </span>
                    <span class="text-xs text-slate-500 uppercase font-semibold">
                        {{ $analisisAco->cantidadAlternativas === 1 ? 'alternativa única' : 'alternativas' }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">
                    {{ $analisisAco->esLineal ? 'Cadena en serie lineal' : ($analisisAco->cantidadAlternativas >= 2 ? 'Bifurcaciones activas' : 'Sin conexión') }}
                </p>
            </div>

            {{-- Cobertura de riesgo --}}
            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">COBERTURA RIESGO</p>
                @php
                    $cobBadge = match($analisisAco->coberturaRiesgo) {
                        'COMPLETO'  => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                        'PARCIAL'   => 'text-amber-700 bg-amber-50 border-amber-200',
                        default     => 'text-slate-600 bg-slate-100 border-slate-200',
                    };
                @endphp
                <div class="mt-1">
                    <span class="inline-block text-xs font-mono font-bold px-2 py-0.5 rounded border {{ $cobBadge }}">
                        {{ $analisisAco->coberturaRiesgo }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5">
                    {{ $analisisAco->tieneRiesgoSuficiente ? 'Riesgo calculable' : 'Riesgo no calculable' }}
                </p>
            </div>
        </div>

        {{-- ── 4. CHECKLIST Y ESTADO DE PREPARACIÓN ACO ─────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Columna 1 y 2: Checklist estructurado --}}
            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h4 class="text-xs font-bold text-slate-800 uppercase flex items-center gap-1.5">
                        <i class="fas fa-tasks text-emerald-700"></i>
                        CHECKLIST DE PREPARACIÓN PARA OPTIMIZACIÓN ACO
                    </h4>
                    <span class="text-[10px] text-slate-400 uppercase font-mono">REQUISITOS FORMALES</span>
                </div>

                <div class="space-y-2.5">
                    @foreach($analisisAco->checklist as $item)
                        <div class="flex items-start justify-between p-2.5 rounded border text-xs {{ $item['cumple'] ? 'bg-emerald-50/50 border-emerald-100 text-slate-700' : 'bg-rose-50/40 border-rose-100 text-slate-600' }}">
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold {{ $item['cumple'] ? 'bg-emerald-600 text-white' : 'bg-rose-500 text-white' }}">
                                    {!! $item['cumple'] ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' !!}
                                </span>
                                <div>
                                    <p class="font-bold uppercase text-[11px] {{ $item['cumple'] ? 'text-emerald-900' : 'text-rose-900' }}">
                                        {{ $item['titulo'] }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $item['detalle'] }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded {{ $item['cumple'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $item['cumple'] ? 'CUMPLIDO' : 'PENDIENTE' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Motivos de bloqueo específicos si no es optimizable --}}
                @if(!empty($analisisAco->motivosBloqueo))
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
                        <p class="font-bold uppercase flex items-center gap-1 text-[11px] mb-1 text-amber-900">
                            <i class="fas fa-exclamation-triangle text-amber-600"></i>
                            MOTIVOS POR LOS QUE ACO NO PUEDE EJECUTARSE TODAVÍA:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-700 pl-1">
                            @foreach($analisisAco->motivosBloqueo as $motivo)
                                <li>{{ $motivo }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Columna 3: Panel de Ejecución ACO --}}
            <div class="bg-slate-50 rounded-lg border border-slate-200 p-5 flex flex-col justify-between shadow-sm">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 uppercase flex items-center gap-1.5 mb-2 border-b pb-2">
                        <i class="fas fa-microchip text-slate-600"></i>
                        ACCIÓN DE SIMULACIÓN ACO
                    </h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-4">
                        El motor de optimización bioinspirada combina distancia métrica y riesgo de seguridad mediante la función canónica de costo <em>C(e) = wD·D_norm + wR·R_norm</em>.
                    </p>

                    <div class="bg-white rounded border border-slate-200 p-3 space-y-2 mb-4 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Nodo de Entrada:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $analisisAco->nodoOrigenId ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Nodo de Salida:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $analisisAco->nodoDestinoId ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Población sugerida:</span>
                            <span class="font-mono font-bold text-slate-800">30 hormigas</span>
                        </div>
                    </div>
                </div>

                {{-- Botón de Ejecución / Bloqueo --}}
                <div>
                    @if($analisisAco->esOptimizable)
                        <div class="bg-emerald-50 border border-emerald-200 rounded p-2.5 mb-3 text-xs text-emerald-800">
                            <p class="font-bold uppercase text-[11px] flex items-center gap-1.5 text-emerald-900">
                                <i class="fas fa-check-circle text-emerald-600"></i>
                                Estructura preparada para ACO
                            </p>
                            <p class="text-[10px] text-emerald-700 mt-1 leading-relaxed">
                                Ejecución ACO pendiente de integración del modelo de riesgo (Fase 5B).
                            </p>
                        </div>
                        <button type="button" disabled
                            class="w-full py-2.5 px-4 rounded text-xs font-bold uppercase bg-slate-200 text-slate-500 cursor-not-allowed flex items-center justify-center gap-2 border border-slate-300"
                            title="Ejecución ACO pendiente de integración del modelo de riesgo">
                            <i class="fas fa-clock"></i> EJECUCIÓN ACO PENDIENTE (FASE 5B)
                        </button>
                        <p class="text-[10px] text-slate-400 text-center uppercase font-semibold mt-2">
                            Topología lista · Modelo de riesgo en proceso de integración
                        </p>
                    @else
                        <button type="button" disabled
                            class="w-full py-2.5 px-4 rounded text-xs font-bold uppercase bg-slate-300 text-slate-500 cursor-not-allowed flex items-center justify-center gap-2 border border-slate-300"
                            title="Ejecución bloqueada: La ruta no cuenta con caminos alternativos o información de riesgo suficiente.">
                            <i class="fas fa-lock"></i> EJECUTAR ACO (DESHABILITADO)
                        </button>
                        <p class="text-[10px] text-slate-400 text-center uppercase font-semibold mt-2">
                            Faltan condiciones en la red para habilitar optimización
                        </p>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── 5. TABLA DE TRAMOS DE LA RUTA PARA ACO ───────────────────────── --}}
        @if($analisisAco->tieneTramos())
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-5 py-3 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-2" style="background-color: #f8fafc">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase flex items-center gap-1.5">
                            <i class="fas fa-network-wired text-emerald-700"></i>
                            TRAMOS DE LA RUTA Y CONEXIONES TOPOLÓGICAS ({{ count($analisisAco->tramosDetalle) }})
                        </h4>
                        <p class="text-[10px] text-slate-500 uppercase mt-0.5">
                            REPRESENTACIÓN FÍSICA Y ESTADO CUANTITATIVO PARA EL GRAFO ACO
                        </p>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase">
                        HAGA CLIC EN UN TRAMO PARA DESTACARLO EN EL MAPA
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="px-3 py-2 text-center">ORD</th>
                                <th class="px-3 py-2 text-center">ID</th>
                                <th class="px-3 py-2">NOMBRE DEL TRAMO</th>
                                <th class="px-3 py-2 text-center">TIPO</th>
                                <th class="px-3 py-2 text-right">DISTANCIA</th>
                                <th class="px-3 py-2 text-center">CONEXIÓN (NODOS)</th>
                                <th class="px-3 py-2 text-center">EVALUACIÓN</th>
                                <th class="px-3 py-2 text-center">CONDICIONES</th>
                                <th class="px-3 py-2 text-center">ZONAS</th>
                                <th class="px-3 py-2 text-center">OBSTÁCULOS</th>
                                <th class="px-3 py-2 text-center">COBERTURA</th>
                                <th class="px-3 py-2 text-center">ESTADO ACO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($analisisAco->tramosDetalle as $t)
                                <tr class="hover:bg-emerald-50/40 cursor-pointer transition"
                                    onclick="destacarTramoEnMapa({{ $t['id'] }})"
                                    id="fila-tramo-aco-{{ $t['id'] }}">
                                    <td class="px-3 py-2.5 text-center font-bold text-slate-700 font-mono">
                                        {{ $t['orden'] ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono font-semibold text-slate-500">
                                        {{ $t['id'] }}
                                    </td>
                                    <td class="px-3 py-2.5 font-bold uppercase text-slate-800">
                                        {{ $t['nombre'] }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center text-[10px] font-semibold text-slate-600">
                                        {{ $t['tipo'] }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-800">
                                        {{ number_format($t['longitud'], 1) }} M
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="inline-flex items-center gap-1 font-mono text-[10px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200">
                                            {{ $t['nodo_inicial'] }} <i class="fas fa-arrow-right text-[8px] text-slate-400"></i> {{ $t['nodo_final'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        @if($t['estado_evaluacion'] === 'NO_EVALUADO')
                                            <span class="text-[10px] font-semibold text-slate-400 uppercase">SIN EVALUAR</span>
                                        @else
                                            <span class="text-[10px] font-bold text-emerald-700 uppercase">{{ $t['nivel_seguridad'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono">
                                        @if($t['condiciones_count'] > 0)
                                            <span class="text-slate-800 font-bold">{{ $t['condiciones_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">SIN DATOS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono">
                                        @if($t['zonas_activas_count'] > 0)
                                            <span class="text-amber-700 font-bold">{{ $t['zonas_activas_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">SIN DATOS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono">
                                        @if($t['obstaculos_count'] > 0)
                                            <span class="text-rose-700 font-bold">{{ $t['obstaculos_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">SIN DATOS</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        @php
                                            $badgeCobTramo = match($t['cobertura_datos']) {
                                                'COMPLETO' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                'PARCIAL'  => 'bg-amber-100 text-amber-800 border-amber-300',
                                                default    => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded border {{ $badgeCobTramo }}">
                                            {{ $t['cobertura_datos'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        @if($t['cobertura_datos'] === 'SIN_DATOS')
                                            <span class="text-[10px] font-semibold text-amber-700" title="Requiere datos de riesgo vial">
                                                <i class="fas fa-exclamation-circle mr-0.5"></i> Sin riesgo
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-emerald-700" title="Información cuantitativa utilizable">
                                                <i class="fas fa-check-circle mr-0.5"></i> Listo
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
</div>
