<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-terminal mr-2" style="color:#006837"></i> LOGS DEL SISTEMA
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">HISTORIAL DE EVENTOS Y ERRORES GENERALES DE LA APLICACIÓN</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.logs.download') }}"
                    class="inline-flex items-center gap-2 bg-[#006837] hover:bg-[#004d27] text-white text-sm font-bold px-4 py-2 rounded-xl shadow-md uppercase transition-all">
                    <i class="fas fa-download"></i> DESCARGAR LOG
                </a>
                <button type="button" onclick="confirmarLimpieza()"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-xl shadow-md uppercase transition-all">
                    <i class="fas fa-trash-alt"></i> LIMPIAR LOGS
                </button>
                <form id="form-limpiar-logs" action="{{ route('admin.logs.clear') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Estadísticas y Filtros --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- KPI Tamaño --}}
                <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-[#006837] flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">TAMAÑO DEL ARCHIVO</p>
                        <p class="text-2xl font-black mt-2 text-[#004d27]">
                            @if($fileSize >= 1048576)
                                {{ number_format($fileSize / 1048576, 2) }} MB
                            @elseif($fileSize >= 1024)
                                {{ number_format($fileSize / 1024, 2) }} KB
                            @else
                                {{ $fileSize }} B
                            @endif
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-5xl opacity-10 text-[#006837]">sd_card</span>
                </div>

                {{-- KPI Entradas --}}
                <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-yellow-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">ENTRADAS REGISTRADAS</p>
                        <p class="text-2xl font-black mt-2 text-yellow-600">{{ $totalCount }}</p>
                    </div>
                    <span class="material-symbols-outlined text-5xl opacity-10 text-yellow-500">list_alt</span>
                </div>

                {{-- KPI Estado general --}}
                <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase">ESTADO DE SALUD</p>
                        <p class="text-2xl font-black mt-2 text-blue-600">
                            @if($totalCount === 0)
                                EXCELENTE
                            @elseif($totalCount > 100)
                                CRÍTICO
                            @else
                                ESTABLE
                            @endif
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-5xl opacity-10 text-blue-500">monitor_heart</span>
                </div>
            </div>

            {{-- Filtros y Tabla --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <form action="{{ route('admin.logs.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full">
                            <label for="search" class="block text-xs font-bold text-gray-700 uppercase mb-2">BÚSQUEDA POR TEXTO</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="BUSCAR EN MENSAJE O TRAZA..."
                                    class="pl-10 w-full rounded-xl border-gray-300 focus:border-[#006837] focus:ring-[#006837] text-sm uppercase">
                            </div>
                        </div>

                        <div class="w-full md:w-64">
                            <label for="level" class="block text-xs font-bold text-gray-700 uppercase mb-2">FILTRAR POR NIVEL</label>
                            <select name="level" id="level"
                                class="w-full rounded-xl border-gray-300 focus:border-[#006837] focus:ring-[#006837] text-sm uppercase">
                                <option value="all" {{ request('level') === 'all' || !request('level') ? 'selected' : '' }}>TODOS LOS NIVELES</option>
                                <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>ERROR</option>
                                <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>WARNING</option>
                                <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>INFO</option>
                                <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>DEBUG</option>
                            </select>
                        </div>

                        <div class="flex gap-2 w-full md:w-auto">
                            <button type="submit"
                                class="w-full md:w-auto bg-[#006837] hover:bg-[#004d27] text-white font-bold px-6 py-2.5 rounded-xl text-sm uppercase shadow transition-all">
                                FILTRAR
                            </button>
                            <a href="{{ route('admin.logs.index') }}"
                                class="w-full md:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-6 py-2.5 rounded-xl text-sm uppercase text-center transition-all">
                                LIMPIAR
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Tabla de Logs --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#006837]">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider w-48">FECHA Y HORA</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider w-32">NIVEL</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider w-32">ENTORNO</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-white uppercase tracking-wider">MENSAJE</th>
                                <th class="px-6 py-3.5 text-center text-xs font-bold text-white uppercase tracking-wider w-24">DETALLE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($paginated as $index => $entry)
                                @php
                                    $badgeColor = match(strtoupper($entry['level'])) {
                                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-red-100 text-red-800 border-red-200',
                                        'WARNING' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'INFO', 'NOTICE' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'DEBUG' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                        {{ Carbon\Carbon::parse($entry['date'])->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeColor }} uppercase">
                                            {{ $entry['level'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500 uppercase">
                                        {{ $entry['env'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800 break-words uppercase">
                                        {{ $entry['message'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @if($entry['stack'])
                                            <button type="button" onclick="toggleStack({{ $index }})"
                                                class="text-[#006837] hover:text-[#004d27] focus:outline-none transition-colors"
                                                title="VER TRAZA COMPLETA">
                                                <span id="icon-{{ $index }}" class="material-symbols-outlined align-middle text-2xl">visibility</span>
                                            </button>
                                        @else
                                            <span class="text-gray-300 font-bold">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($entry['stack'])
                                    <tr id="stack-{{ $index }}" class="hidden bg-gray-50">
                                        <td colspan="5" class="px-8 py-4">
                                            <div class="bg-gray-900 text-gray-100 rounded-xl p-4 overflow-x-auto max-h-96 text-xs font-mono whitespace-pre shadow-inner">
                                                {{ $entry['stack'] }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-6xl text-gray-300">find_in_page</span>
                                            <p class="text-gray-400 font-bold uppercase">NO SE ENCONTRARON ENTRADAS DE LOG QUE COINCIDAN CON LOS CRITERIOS.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($paginated->hasPages())
                    <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                        {{ $paginated->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function toggleStack(index) {
            const row = document.getElementById(`stack-${index}`);
            const icon = document.getElementById(`icon-${index}`);
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.textContent = 'visibility_off';
            } else {
                row.classList.add('hidden');
                icon.textContent = 'visibility';
            }
        }

        function confirmarLimpieza() {
            Swal.fire({
                title: '¿VACIAR ARCHIVO DE LOGS?',
                html: `<div class="text-sm uppercase text-gray-600 mb-2">Esta acción vaciará completamente el historial de logs de eventos actuales.</div>
                       <small class="text-red-600 font-bold uppercase">ESTA ACCIÓN NO SE PUEDE DESHACER.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'SÍ, VACIAR',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-limpiar-logs').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
