<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight uppercase flex items-center gap-2">
                    <i class="fas fa-tasks text-emerald-600"></i>
                    BANDEJA DE VERIFICACIONES PENDIENTES
                </h2>
                <p class="text-xs text-slate-500 uppercase mt-0.5">
                    DECISIÓN TÉCNICA SOBRE CONSENSO COLABORATIVO DE OBSTÁCULOS VIALES
                </p>
            </div>
            <a href="{{ route('obstaculos.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 uppercase">
                <i class="fas fa-list"></i> Gestión de Obstáculos
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm text-xs text-emerald-800 font-bold uppercase">
                <i class="fas fa-check-circle mr-1.5"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm text-xs text-blue-800 font-bold uppercase">
                <i class="fas fa-info-circle mr-1.5"></i> {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm text-xs text-rose-800 font-bold uppercase">
                <i class="fas fa-times-circle mr-1.5"></i> {{ session('error') }}
            </div>
        @endif

        @if($pendientes->isEmpty())
            <div class="bg-white rounded-xl shadow border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-check-double"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 uppercase">NO HAY VERIFICACIONES PENDIENTES DE DECISIÓN</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    Actualmente no existen obstáculos activos con nuevas verificaciones comunitarias por auditar.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pendientes as $item)
                    @php
                        $obs = $item['obstaculo'];
                        $con = $item['evaluacion'];
                        $esCandidato = ($con['estado_consenso'] === 'CANDIDATO_RESOLUCION');
                        $enConflicto = ($con['estado_consenso'] === 'EN_CONFLICTO');
                    @endphp

                    <div class="bg-white rounded-xl shadow border {{ $esCandidato ? 'border-emerald-500/60 ring-1 ring-emerald-500/30' : ($enConflicto ? 'border-amber-500/60 ring-1 ring-amber-500/30' : 'border-slate-200') }} overflow-hidden">
                        {{-- Encabezado del Obstáculo --}}
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-sm font-bold text-sm {{ $esCandidato ? 'bg-emerald-600' : ($enConflicto ? 'bg-amber-600' : 'bg-slate-600') }}">
                                    #{{ $obs->getKey() }}
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-slate-900 uppercase">
                                            {{ $obs->tip_obs }}
                                        </h4>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase {{ $obs->colorEstado() }}">
                                            {{ $obs->est_obs }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        Tramo: <strong class="text-slate-800">{{ $obs->tramo?->nom_ctr ?? 'Tramo asociado' }}</strong>
                                    </p>
                                </div>
                            </div>

                            {{-- Badge de Estado de Consenso --}}
                            <div>
                                @if($esCandidato)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-sm uppercase tracking-wide">
                                        <i class="fas fa-check-circle text-emerald-600"></i>
                                        CANDIDATO A RESOLUCIÓN ({{ $con['conteo_ya_no_esta'] }}/{{ $con['min_requerido'] }})
                                    </span>
                                @elseif($enConflicto)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-sm uppercase tracking-wide">
                                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                                        EVIDENCIA EN CONFLICTO ({{ $con['conteo_ya_no_esta'] }} NO ESTÁ / {{ $con['conteo_sigue_presente'] }} CONTINÚA)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 uppercase">
                                        <i class="fas fa-clock text-slate-500"></i>
                                        {{ str_replace('_', ' ', $con['estado_consenso']) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Cuerpo y Desglose de Evidencia --}}
                        <div class="p-6 space-y-4">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">DESCRIPCIÓN DEL OBSTÁCULO</p>
                                <p class="text-sm text-slate-800 font-medium mt-1 bg-slate-50 p-2.5 rounded border border-slate-100">
                                    "{{ $obs->des_obs }}"
                                </p>
                            </div>

                            {{-- Resumen de Consenso Comunitario --}}
                            <div class="bg-slate-50 rounded-lg p-3.5 border border-slate-200">
                                <p class="text-xs font-bold text-slate-800 uppercase mb-2 flex items-center gap-1.5">
                                    <i class="fas fa-chart-pie text-indigo-600"></i>
                                    RESUMEN DE EVIDENCIA ({{ $con['usuarios_unicos'] }} VOLUNTARIOS DISTINTOS, {{ $con['total_verificaciones'] }} REGISTROS TOTALES)
                                </p>
                                <p class="text-xs text-slate-700 italic mb-3">
                                    "{{ $con['motivo'] }}"
                                </p>

                                {{-- Lista de últimas verificaciones con usuarios y fotos --}}
                                <div class="space-y-2">
                                    @foreach($con['verificaciones_deduplicadas'] as $v)
                                        <div class="flex items-center justify-between p-2.5 rounded bg-white border border-slate-200 text-xs">
                                            <div class="flex items-center gap-2.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $v->badgeClase() }}">
                                                    {{ $v->etiquetaRespuesta() }}
                                                </span>
                                                <span class="font-bold text-slate-800">
                                                    {{ $v->usuario?->name ?? $v->usuario?->nom_usu ?? 'Voluntario' }}
                                                </span>
                                                @if($v->observacion)
                                                    <span class="text-slate-500 text-[11px] truncate max-w-md">
                                                        — "{{ $v->observacion }}"
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-3 text-[10px] text-slate-400 font-mono">
                                                @if($v->foto)
                                                    <a href="{{ asset('storage/' . $v->foto) }}" target="_blank" class="text-emerald-700 font-bold hover:underline">
                                                        <i class="fas fa-image"></i> Ver Foto
                                                    </a>
                                                @endif
                                                <span>{{ $v->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Acciones del Técnico Responsable --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-200">
                                <div class="text-xs text-slate-500">
                                    Al confirmar como RESUELTO, el riesgo del tramo disminuirá y el algoritmo ACO recalculará la recomendación.
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- Mantener Activo --}}
                                    <form action="{{ route('verificaciones.mantener', $obs) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 uppercase border border-slate-300">
                                            Mantener Activo
                                        </button>
                                    </form>

                                    {{-- Confirmar como Resuelto --}}
                                    <form action="{{ route('verificaciones.confirmar', $obs) }}" method="POST" onsubmit="return confirm('¿Está seguro de confirmar este obstáculo como RESUELTO? Esta acción actualizará inmediatamente los perfiles de riesgo vial y la optimización ACO.');">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold text-white shadow uppercase transition hover:opacity-95 flex items-center gap-1.5" style="background-color: #2d6a27">
                                            <i class="fas fa-check-circle"></i> Confirmar como Resuelto
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
