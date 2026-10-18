<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-exclamation-triangle mr-2" style="color:#f0b429"></i> OBSTÁCULOS EN TRAMOS
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">OBSTÁCULOS REGISTRADOS EN SEGMENTOS DE RUTAS Y SU IMPACTO EN RIESGO/ACO</p>
            </div>
            <a href="{{ route('rutas.index') }}"
                class="inline-flex items-center gap-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold px-4 py-2 rounded uppercase">
                <i class="fas fa-route"></i> VER RUTAS
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('obstaculos.index') }}" class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">BUSCAR TRAMO / RUTA</label>
                        <input type="text" name="buscar" value="{{ $filtros['buscar'] }}"
                            placeholder="NOMBRE DEL TRAMO O RUTA..."
                            class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">ESTADO</label>
                        <select name="estado" class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <option value="">TODOS</option>
                            @foreach($estados as $est)
                                <option value="{{ $est }}" @selected($filtros['estado'] === $est)>{{ $est }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">TIPO</label>
                        <select name="tipo" class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <option value="">TODOS LOS TIPOS</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t }}" @selected($filtros['tipo'] === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 text-white text-sm font-bold py-2 px-4 rounded uppercase"
                            style="background-color:#f0b429; color:#1a1a1a">
                            <i class="fas fa-search mr-1"></i> BUSCAR
                        </button>
                        @if($filtros['buscar'] || $filtros['estado'] || $filtros['tipo'])
                            <a href="{{ route('obstaculos.index') }}"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold py-2 px-3 rounded uppercase">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Tabla --}}
            @if($obstaculos->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    <div class="text-5xl mb-4">✅</div>
                    <p class="font-semibold uppercase text-lg">NO HAY OBSTÁCULOS PENDIENTES</p>
                    <p class="text-sm mt-1">TODOS LOS REPORTES HAN SIDO GESTIONADOS</p>
                </div>
            @else
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead style="background-color:#92400e">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">TIPO</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">DESCRIPCIÓN</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase hidden md:table-cell">TRAMO / RUTA</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ESTADO</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden lg:table-cell">ORIGEN</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden lg:table-cell">FECHA</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($obstaculos as $ob)
                                @php
                                    $obColor = \App\Models\Obstaculo::COLORES_ESTADO[$ob->est_obs] ?? 'bg-gray-100 text-gray-800';
                                    $tieneEvidencias = $ob->verificaciones()->exists() || $ob->reportes()->exists();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold uppercase text-gray-800">{{ $ob->tip_obs }}</td>
                                    <td class="px-4 py-3 text-gray-600 uppercase">{{ Str::limit($ob->des_obs, 50) }}</td>
                                    <td class="px-4 py-3 hidden md:table-cell">
                                        <p class="text-xs font-semibold uppercase text-gray-700">{{ $ob->tramo?->nom_ctr ?? '—' }}</p>
                                        @if($ob->tramo?->ruta)
                                            <a href="{{ route('rutas.show', $ob->tramo->ruta) }}"
                                                class="text-xs text-blue-600 hover:underline uppercase">
                                                {{ $ob->tramo->ruta->ide_rut }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $obColor }}">{{ $ob->est_obs }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500 uppercase hidden lg:table-cell">{{ $ob->ori_obs }}</td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500 hidden lg:table-cell">
                                        {{ $ob->fcr_obs?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-1.5 flex-wrap">
                                            @if($ob->tramo?->ruta)
                                                <a href="{{ route('rutas.show', $ob->tramo->ruta) }}"
                                                    title="Ver ruta asociada, mapa y recomendación ACO"
                                                    class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-2 py-1 rounded uppercase shadow-sm transition">
                                                    <i class="fas fa-eye text-[11px]"></i> VER
                                                </a>
                                            @endif
                                            @if($ob->est_obs === 'REPORTADO')
                                                <form action="{{ route('obstaculos.confirmar', $ob) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" title="CONFIRMAR — MARCAR ACTIVO"
                                                        class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold px-2 py-1 rounded uppercase shadow-sm transition">
                                                        <i class="fas fa-check text-[11px]"></i> CONFIRMAR
                                                    </button>
                                                </form>
                                                @if(!$tieneEvidencias)
                                                    <button type="button"
                                                        onclick="confirmarRechazar({{ $ob->getKey() }}, '{{ addslashes($ob->tip_obs) }}')"
                                                        title="RECHAZAR REPORTE (ELIMINAR)"
                                                        class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded uppercase shadow-sm transition">
                                                        <i class="fas fa-times text-[11px]"></i> RECHAZAR
                                                    </button>
                                                    <form id="form-rechazar-{{ $ob->getKey() }}"
                                                        action="{{ route('obstaculos.rechazar', $ob) }}" method="POST" class="hidden">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                @else
                                                    <span title="No puede eliminarse: posee reportes o verificaciones asociadas (use Resolver o Confirmar)"
                                                        class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-semibold px-2 py-1 rounded uppercase cursor-not-allowed select-none">
                                                        <i class="fas fa-times text-[11px]"></i> RECHAZAR
                                                    </span>
                                                @endif
                                            @elseif($ob->est_obs === 'ACTIVO')
                                                @php
                                                    $consenso = app(\App\Services\Aco\ServicioConsensoVerificacionObstaculo::class)->evaluarConsenso($ob);
                                                @endphp
                                                @if(!empty($consenso['es_candidato_resolucion']))
                                                    <a href="{{ route('verificaciones.bandeja') }}"
                                                        title="Consenso colaborativo alcanzado: {{ $consenso['conteo_ya_no_esta'] }} votos indican que ya no está. Revisar en bandeja técnica."
                                                        class="inline-flex items-center gap-1 bg-purple-100 hover:bg-purple-200 text-purple-800 border border-purple-300 text-xs font-bold px-2 py-1 rounded uppercase shadow-sm transition animate-pulse">
                                                        <i class="fas fa-user-check text-[11px]"></i> CONSENSO
                                                    </a>
                                                @endif
                                                <form action="{{ route('obstaculos.resolver', $ob) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" title="MARCAR COMO RESUELTO"
                                                        class="inline-flex items-center gap-1 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-bold px-2 py-1 rounded uppercase shadow-sm transition">
                                                        <i class="fas fa-check-double text-[11px]"></i> RESOLVER
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded uppercase">
                                                    <i class="fas fa-check-circle text-emerald-600 text-[11px]"></i> RESUELTO
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $obstaculos->links() }}</div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function confirmarRechazar(id, tipo) {
        Swal.fire({
            title: '¿RECHAZAR REPORTE?',
            html: `<span class="font-semibold uppercase">${tipo}</span><br>
                   <small class="text-gray-500">EL REPORTE SERÁ ELIMINADO DEL SISTEMA.</small>`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#6b7280', cancelButtonColor: '#dc2626',
            confirmButtonText: 'SÍ, RECHAZAR', cancelButtonText: 'CANCELAR', reverseButtons: true,
        }).then(r => { if (r.isConfirmed) document.getElementById(`form-rechazar-${id}`).submit(); });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const s = document.querySelector('meta[name="flash-success"]');
        if (s) Swal.fire({ toast:true, position:'top-end', icon:'success', title: s.content, showConfirmButton:false, timer:3500, timerProgressBar:true });
        const e = document.querySelector('meta[name="flash-error"]');
        if (e) Swal.fire({ toast:true, position:'top-end', icon:'error', title: e.content, showConfirmButton:false, timer:4000, timerProgressBar:true });
    });
    </script>
    @endpush
</x-app-layout>
