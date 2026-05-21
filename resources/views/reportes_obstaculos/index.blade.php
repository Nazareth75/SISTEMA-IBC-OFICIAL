<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-eye mr-2" style="color:#006837"></i> OBSERVATORIO DE OBSTÁCULOS URBANOS
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">GEOLOCALIZACIÓN Y SEGUIMIENTO DE REPORTES CIUDADANOS</p>
            </div>
            <a href="{{ route('reportes-obstaculos.create') }}"
                class="inline-flex items-center gap-1 bg-[#e8f7ef] hover:bg-[#cbece0] text-[#006837] border-2 border-[#006837] text-sm font-bold px-4 py-2 rounded uppercase shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add_location</span>
                NUEVO REPORTE
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filtros (Búsqueda en Cliente) --}}
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">BUSCAR CÓDIGO O TIPO</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-gray-400 text-sm">search</span>
                            </span>
                            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Ej. OBS-0001, Bache..."
                                class="w-full border rounded pl-10 pr-3 py-2 text-sm uppercase focus:ring-2 focus:ring-[#006837] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">FILTRAR POR ESTADO</label>
                        <select id="filterEstado" onchange="filterTable()" class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-[#006837] focus:outline-none">
                            <option value="">TODOS LOS ESTADOS</option>
                            <option value="REPORTADO">REPORTADO</option>
                            <option value="ACTIVO">ACTIVO (VALIDADO)</option>
                            <option value="DESCARTADO">DESCARTADO</option>
                            <option value="RESUELTO">RESUELTO</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">FILTRAR POR RIESGO</label>
                        <select id="filterRiesgo" onchange="filterTable()" class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-[#006837] focus:outline-none">
                            <option value="">TODOS LOS NIVELES</option>
                            <option value="BAJO">BAJO</option>
                            <option value="MEDIO">MEDIO</option>
                            <option value="ALTO">ALTO</option>
                            <option value="CRITICO">CRÍTICO</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            @if($reportes->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    <span class="material-symbols-outlined text-6xl text-[#006837] mb-4">check_circle</span>
                    <p class="font-semibold uppercase text-lg">NO HAY REPORTES URBANOS REGISTRADOS</p>
                    <p class="text-sm mt-1">El observatorio se encuentra vacío por el momento.</p>
                </div>
            @else
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" id="reportesTable">
                        <thead style="background-color:#004d27">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">CÓDIGO / TIPO</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">UBICACIÓN</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ESTADO</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">RIESGO</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden lg:table-cell">VOLUNTARIO</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden lg:table-cell">FECHA</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($reportes as $reporte)
                                @php 
                                    $estColor = match($reporte->estado) {
                                        'REPORTADO' => 'bg-yellow-100 text-yellow-800',
                                        'ACTIVO' => 'bg-blue-100 text-blue-800',
                                        'DESCARTADO' => 'bg-gray-200 text-gray-600',
                                        'RESUELTO' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    $riskColor = match($reporte->nivel_riesgo) {
                                        'BAJO' => 'text-green-600 bg-green-50 border border-green-200',
                                        'MEDIO' => 'text-yellow-600 bg-yellow-50 border border-yellow-200',
                                        'ALTO' => 'text-orange-600 bg-orange-50 border border-orange-200',
                                        'CRITICO' => 'text-red-600 bg-red-50 border border-red-200',
                                        default => 'text-gray-600'
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 reporte-row" data-estado="{{ $reporte->estado }}" data-riesgo="{{ $reporte->nivel_riesgo }}">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-[#006837] search-target">{{ $reporte->codigo }}</div>
                                        <div class="text-xs text-gray-500 font-semibold uppercase search-target">{{ $reporte->tipo_obstaculo }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1 text-gray-700">
                                            <span class="material-symbols-outlined text-[16px] text-[#964900]">location_on</span>
                                            <span class="text-xs uppercase font-semibold">{{ Str::limit($reporte->direccion_referencia ?? 'UBICACIÓN GPS', 35) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded text-[11px] tracking-wider font-bold {{ $estColor }}">{{ $reporte->estado }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded text-[11px] font-bold {{ $riskColor }}">{{ $reporte->nivel_riesgo }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-600 uppercase hidden lg:table-cell font-medium">
                                        {{ explode(' ', $reporte->reportadoPor?->name)[0] ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500 hidden lg:table-cell">
                                        {{ $reporte->fecha_reporte?->format('d/m/Y') }}
                                        <div class="text-[10px]">{{ $reporte->fecha_reporte?->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('reportes-obstaculos.show', $reporte->id) }}"
                                            class="inline-flex items-center gap-1 bg-[#f0f9f4] hover:bg-[#e1f3e9] text-[#006837] border border-[#006837] text-xs font-bold px-3 py-1.5 rounded uppercase transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            GESTIONAR
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function filterTable() {
            const input = document.getElementById('searchInput').value.toUpperCase();
            const estado = document.getElementById('filterEstado').value;
            const riesgo = document.getElementById('filterRiesgo').value;
            const rows = document.querySelectorAll('.reporte-row');

            rows.forEach(row => {
                const textContent = Array.from(row.querySelectorAll('.search-target'))
                                         .map(el => el.textContent.toUpperCase())
                                         .join(' ');
                
                const matchSearch = textContent.includes(input);
                const matchEstado = estado === "" || row.dataset.estado === estado;
                const matchRiesgo = riesgo === "" || row.dataset.riesgo === riesgo;

                if (matchSearch && matchEstado && matchRiesgo) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
