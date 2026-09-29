<x-app-layout>
    @push('styles')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-fixed-dim": "#83d99c", "on-primary-fixed": "#00210e", "on-secondary-container": "#642f00",
                        "on-error-container": "#93000a", "tertiary-container": "#26653f", "surface-container-low": "#f3f3f6",
                        "secondary-container": "#ff8928", "surface-container-high": "#e8e8ea", "surface-bright": "#f9f9fc",
                        "tertiary-fixed-dim": "#94d5a6", "primary-container": "#006837", "on-surface": "#1a1c1e",
                        "outline": "#6f7a70", "tertiary": "#044d29", "outline-variant": "#bec9be",
                        "surface-tint": "#0b6d3b", "on-tertiary-fixed-variant": "#0d522e", "on-tertiary-fixed": "#00210e",
                        "error-container": "#ffdad6", "surface-container": "#eeeef0", "surface": "#f9f9fc",
                        "secondary-fixed": "#ffdcc6", "on-primary-fixed-variant": "#00522a", "on-tertiary": "#ffffff",
                        "surface-container-highest": "#e2e2e5", "primary-fixed": "#9ef6b6", "primary": "#004d27",
                        "tertiary-fixed": "#aff2c1", "surface-container-lowest": "#ffffff", "secondary": "#964900",
                        "on-secondary-fixed-variant": "#723600", "on-surface-variant": "#3f4940", "inverse-on-surface": "#f0f0f3",
                        "on-primary": "#ffffff", "error": "#ba1a1a", "secondary-fixed-dim": "#ffb786",
                        "inverse-primary": "#83d99c", "background": "#f9f9fc", "on-secondary-fixed": "#311300",
                        "on-primary-container": "#8ee4a6", "inverse-surface": "#2f3133", "surface-variant": "#e2e2e5",
                        "on-error": "#ffffff", "on-background": "#1a1c1e", "surface-dim": "#dadadc",
                        "on-secondary": "#ffffff", "on-tertiary-container": "#9ee0b0"
                    },
                    "fontFamily": {
                        "h1": ["Public Sans"], "button": ["Public Sans"], "body-md": ["Public Sans"],
                        "body-lg": ["Public Sans"], "label-bold": ["Public Sans"], "display": ["Public Sans"], "h2": ["Public Sans"]
                    },
                    "fontSize": {
                        "h1": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                        "button": ["16px", {"lineHeight": "24px", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-bold": ["14px", {"lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "h2": ["24px", {"lineHeight": "32px", "fontWeight": "600"}]
                    }
                }
            }
        }
    </script>
    <style>
        .custom-ui { font-family: 'Public Sans', sans-serif; }
        .custom-ui .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); border: 1px solid #e1e3e1; }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Reportes de Obstáculos') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 custom-ui bg-background text-on-background min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
            <div class="mb-4 bg-primary-fixed border border-primary text-on-primary-fixed px-4 py-3 rounded-lg relative flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div>
                    <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-2">
                        <span>Rutas Seguras</span>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-primary font-bold">Mis Reportes</span>
                    </nav>
                    <h2 class="font-h1 text-h1 text-on-surface">Mis Reportes de Obstáculos</h2>
                    <p class="text-on-surface-variant font-body-md mt-1">Supervise y gestione las alertas de accesibilidad reportadas por usted.</p>
                </div>
                <a href="{{ route('reportes-obstaculos.create') }}" class="bg-secondary text-white px-6 py-3 rounded-xl font-button shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all flex items-center justify-center gap-2 h-14">
                    <span class="material-symbols-outlined">add</span>
                    NUEVO REPORTE MANUAL
                </a>
            </div>

            <!-- Stats Bento Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="glass-card p-6 rounded-xl shadow-sm hover:shadow-md transition-all border-l-4 border-l-primary">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-symbols-outlined text-primary p-2 bg-primary/10 rounded-lg">description</span>
                        @if($stats['hoy'] > 0)
                            <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1 rounded">+{{ $stats['hoy'] }} hoy</span>
                        @endif
                    </div>
                    <p class="text-on-surface-variant font-label-bold">TOTAL REPORTES</p>
                    <p class="text-h1 font-h1 text-on-surface mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="glass-card p-6 rounded-xl shadow-sm hover:shadow-md transition-all border-l-4 border-l-secondary">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-symbols-outlined text-secondary p-2 bg-secondary/10 rounded-lg">pending_actions</span>
                    </div>
                    <p class="text-on-surface-variant font-label-bold">EN REVISIÓN</p>
                    <p class="text-h1 font-h1 text-on-surface mt-1">{{ number_format($stats['pendientes']) }}</p>
                </div>
                <div class="glass-card p-6 rounded-xl shadow-sm hover:shadow-md transition-all border-l-4 border-l-error">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-symbols-outlined text-error p-2 bg-error/10 rounded-lg">priority_high</span>
                    </div>
                    <p class="text-on-surface-variant font-label-bold">RIESGO CRÍTICO</p>
                    <p class="text-h1 font-h1 text-on-surface mt-1">{{ number_format($stats['criticos']) }}</p>
                </div>
                <div class="glass-card p-6 rounded-xl shadow-sm hover:shadow-md transition-all border-l-4 border-l-primary-fixed-dim">
                    <div class="flex items-center justify-between mb-4">
                        <span class="material-symbols-outlined text-tertiary p-2 bg-tertiary/10 rounded-lg">check_circle</span>
                        @if($stats['total'] > 0)
                            <span class="text-xs font-bold text-tertiary bg-tertiary/10 px-2 py-1 rounded">Eficiencia {{ round(($stats['solucionados'] / $stats['total']) * 100) }}%</span>
                        @endif
                    </div>
                    <p class="text-on-surface-variant font-label-bold">SOLUCIONADOS</p>
                    <p class="text-h1 font-h1 text-on-surface mt-1">{{ number_format($stats['solucionados']) }}</p>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-surface p-6 rounded-xl border border-outline-variant shadow-sm space-y-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary">filter_list</span>
                    <h3 class="font-h2 text-label-bold text-on-surface">FILTROS AVANZADOS</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-on-surface-variant uppercase ml-1">Tipo de Obstáculo</label>
                        <select id="filter-tipo" onchange="applyFilters()" class="w-full bg-background border-2 border-outline-variant rounded-lg h-12 px-4 focus:border-primary focus:ring-0 transition-all font-body-md">
                            <option value="">Todos los tipos</option>
                            @foreach(\App\Models\ReporteObstaculo::select('tipo_obstaculo')->distinct()->pluck('tipo_obstaculo') as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-on-surface-variant uppercase ml-1">Nivel de Riesgo</label>
                        <select id="filter-riesgo" onchange="applyFilters()" class="w-full bg-background border-2 border-outline-variant rounded-lg h-12 px-4 focus:border-primary focus:ring-0 transition-all font-body-md">
                            <option value="">Todos los niveles</option>
                            <option value="CRITICO">Crítico</option>
                            <option value="ALTO">Alto</option>
                            <option value="MEDIO">Medio</option>
                            <option value="BAJO">Bajo</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-on-surface-variant uppercase ml-1">Búsqueda rápida</label>
                        <div class="flex items-center bg-background border-2 border-outline-variant rounded-lg h-12 px-4">
                            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
                            <input id="filter-search" onkeyup="applyFilters()" class="bg-transparent border-none focus:ring-0 text-body-md w-full p-0" placeholder="Código o descripción" type="text"/>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button onclick="resetFilters()" class="flex-1 p-3 border-2 border-outline-variant rounded-lg h-12 text-on-surface-variant hover:bg-surface-container-high transition-all flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined mr-2">restart_alt</span> LIMPIAR
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modern Data Table -->
            <div class="bg-surface rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="p-6 font-label-bold text-on-surface-variant">CÓDIGO</th>
                                <th class="p-6 font-label-bold text-on-surface-variant">TIPO</th>
                                <th class="p-6 font-label-bold text-on-surface-variant">ESTADO</th>
                                <th class="p-6 font-label-bold text-on-surface-variant">NIVEL RIESGO</th>
                                <th class="p-6 font-label-bold text-on-surface-variant">FECHA</th>
                                <th class="p-6 font-label-bold text-on-surface-variant text-right">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant" id="reportes-tbody">
                            @forelse($reportes as $reporte)
                                @php
                                    // Determinar colores de estado
                                    if($reporte->estado === 'REPORTADO') {
                                        $estColor = 'bg-secondary-fixed text-on-secondary-fixed';
                                    } elseif($reporte->estado === 'ACTIVO') {
                                        $estColor = 'bg-primary-fixed text-on-primary-fixed-variant';
                                    } elseif($reporte->estado === 'RESUELTO') {
                                        $estColor = 'bg-tertiary-fixed text-on-tertiary-fixed-variant';
                                    } else {
                                        $estColor = 'bg-surface-variant text-on-surface-variant';
                                    }

                                    // Determinar colores de riesgo
                                    if($reporte->nivel_riesgo === 'CRITICO') {
                                        $rColor = 'text-error-container text-on-error-container font-extrabold px-3 py-1 bg-error-container rounded border border-error/20';
                                        $rDot = '';
                                    } elseif($reporte->nivel_riesgo === 'ALTO') {
                                        $rColor = 'text-error font-bold';
                                        $rDot = '<span class="w-2 h-2 rounded-full bg-error"></span>';
                                    } elseif($reporte->nivel_riesgo === 'MEDIO') {
                                        $rColor = 'text-secondary font-bold';
                                        $rDot = '<span class="w-2 h-2 rounded-full bg-secondary"></span>';
                                    } else {
                                        $rColor = 'text-on-surface-variant font-bold opacity-60';
                                        $rDot = '<span class="w-2 h-2 rounded-full bg-outline"></span>';
                                    }
                                @endphp
                                <tr class="hover:bg-primary/5 transition-colors group data-row" data-tipo="{{ $reporte->tipo_obstaculo }}" data-riesgo="{{ $reporte->nivel_riesgo }}" data-search="{{ strtolower($reporte->codigo . ' ' . $reporte->descripcion) }}">
                                    <td class="p-6 font-bold text-primary">{{ $reporte->codigo }}</td>
                                    <td class="p-6 font-body-md text-on-surface">{{ $reporte->tipo_obstaculo }}</td>
                                    <td class="p-6">
                                        <span class="{{ $estColor }} text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                            @if($reporte->estado === 'ACTIVO') En Revisión @else {{ $reporte->estado }} @endif
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <span class="flex items-center gap-2 {{ $rColor }}">
                                            {!! $rDot !!} {{ $reporte->nivel_riesgo }}
                                        </span>
                                    </td>
                                    <td class="p-6 text-on-surface-variant text-sm">{{ $reporte->fecha_reporte?->format('d/m/Y H:i') }}</td>
                                    <td class="p-6 text-right">
                                        <a href="{{ route('reportes-obstaculos.show', $reporte->id) }}" class="text-primary font-button hover:underline px-4 py-2 rounded-lg hover:bg-primary/10 transition-all">Ver Detalle</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                        <p class="font-bold">No has reportado ningún obstáculo aún.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($reportes->count() > 0)
                <!-- Pagination Footer -->
                <div class="p-6 border-t border-outline-variant flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-on-surface-variant text-sm" id="table-stats">Mostrando {{ $reportes->count() }} reportes</p>
                </div>
                @endif
            </div>

            <!-- Accessibility Note / Footer -->
            <div class="flex flex-col md:flex-row items-center gap-6 p-8 bg-surface-container-lowest rounded-xl border-2 border-dashed border-outline-variant">
                <div class="flex-1">
                    <h4 class="font-h2 text-h2 text-primary font-bold">Compromiso con la Accesibilidad</h4>
                    <p class="text-on-surface-variant font-body-md mt-2 max-w-2xl">
                        Este panel ha sido optimizado para lectores de pantalla y navegación por teclado. Si detecta algún problema de visualización o navegación, por favor repórtelo al equipo de desarrollo institucional.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-4">
                        <span class="inline-flex items-center gap-2 text-xs font-bold bg-primary/10 text-primary px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm">visibility</span> ALTO CONTRASTE ACTIVO
                        </span>
                        <span class="inline-flex items-center gap-2 text-xs font-bold bg-primary/10 text-primary px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm">text_fields</span> FUENTES LEGIBLES
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        // Funcionalidad de Filtros
        function applyFilters() {
            const tipo = document.getElementById('filter-tipo').value;
            const riesgo = document.getElementById('filter-riesgo').value;
            const search = document.getElementById('filter-search').value.toLowerCase();
            
            let visibleCount = 0;
            document.querySelectorAll('.data-row').forEach(row => {
                const matchTipo = tipo === '' || row.dataset.tipo === tipo;
                const matchRiesgo = riesgo === '' || row.dataset.riesgo === riesgo;
                const matchSearch = search === '' || row.dataset.search.includes(search);
                
                if(matchTipo && matchRiesgo && matchSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const stats = document.getElementById('table-stats');
            if (stats) stats.textContent = `Mostrando ${visibleCount} reportes filtrados`;
        }

        function resetFilters() {
            document.getElementById('filter-tipo').value = '';
            document.getElementById('filter-riesgo').value = '';
            document.getElementById('filter-search').value = '';
            applyFilters();
        }

        // Micro-interacciones (del diseño original)
        document.querySelectorAll('.font-button, button').forEach(button => {
            button.addEventListener('mousedown', () => {
                button.classList.add('scale-95');
                setTimeout(() => button.classList.remove('scale-95'), 150);
            });
        });
    </script>
    @endpush
</x-app-layout>
