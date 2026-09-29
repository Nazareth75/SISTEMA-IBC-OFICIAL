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
                        "primary-fixed-dim": "#83d99c", "on-tertiary-fixed-variant": "#0d522e", "on-tertiary-fixed": "#00210e",
                        "background": "#f9f9fc", "error": "#ba1a1a", "tertiary-fixed": "#aff2c1", "tertiary": "#044d29",
                        "on-primary-container": "#8ee4a6", "inverse-primary": "#83d99c", "surface-container-lowest": "#ffffff",
                        "on-primary-fixed": "#00210e", "on-tertiary": "#ffffff", "primary-fixed": "#9ef6b6", "outline": "#6f7a70",
                        "on-tertiary-container": "#9ee0b0", "surface-container-high": "#e8e8ea", "surface-container": "#eeeef0",
                        "on-background": "#1a1c1e", "on-secondary-fixed": "#311300", "tertiary-container": "#26653f",
                        "on-surface": "#1a1c1e", "inverse-surface": "#2f3133", "surface-variant": "#e2e2e5", "primary": "#004d27",
                        "secondary-fixed": "#ffdcc6", "secondary": "#964900", "tertiary-fixed-dim": "#94d5a6",
                        "on-secondary-fixed-variant": "#723600", "on-error-container": "#93000a", "surface-container-low": "#f3f3f6",
                        "on-error": "#ffffff", "primary-container": "#006837", "secondary-fixed-dim": "#ffb786",
                        "error-container": "#ffdad6", "inverse-on-surface": "#f0f0f3", "on-primary-fixed-variant": "#00522a",
                        "surface": "#f9f9fc", "on-primary": "#ffffff", "surface-container-highest": "#e2e2e5",
                        "secondary-container": "#ff8928", "on-secondary-container": "#642f00", "surface-tint": "#0b6d3b",
                        "on-secondary": "#ffffff", "outline-variant": "#bec9be", "surface-dim": "#dadadc",
                        "surface-bright": "#f9f9fc", "on-surface-variant": "#3f4940"
                    },
                    "fontFamily": {
                        "h1": ["Public Sans"], "h2": ["Public Sans"], "body-md": ["Public Sans"], "body-lg": ["Public Sans"],
                        "button": ["Public Sans"], "label-bold": ["Public Sans"], "display": ["Public Sans"]
                    },
                    "fontSize": {
                        "h1": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                        "h2": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-bold": ["14px", {"lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "display": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "button": ["16px", {"lineHeight": "24px", "fontWeight": "600"}]
                    }
                }
            }
        }
    </script>
    <style>
        .custom-dashboard { font-family: 'Public Sans', sans-serif; color: #1a1c1e; }
        .custom-dashboard .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); border: 1px solid #E1E3E1; }
    </style>
    @endpush

    <div class="custom-dashboard bg-background min-h-[calc(100vh-64px)] pb-24 pt-6">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            
            <!-- Welcome Section & Primary Action -->
            <section class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-primary rounded-2xl p-8 text-on-primary shadow-lg overflow-hidden relative">
                <div class="relative z-10">
                    <p class="font-label-bold text-label-bold opacity-90 mb-1">¡Buenos días, {{ explode(' ', auth()->user()->nombre_completo)[0] ?? 'Técnico' }}!</p>
                    <h2 class="font-h1 text-h1 mb-2">Resumen de Inspección</h2>
                    <p class="font-body-md text-body-md max-w-md opacity-80">
                        @if($statsTecnico['rutasValidar'] > 0)
                            Hoy tienes {{ $statsTecnico['rutasValidar'] }} rutas prioritarias por revisar en el sistema.
                        @else
                            No tienes rutas pendientes de revisión urgente por el momento.
                        @endif
                    </p>
                </div>
                <a href="{{ route('rutas.index') }}" class="relative z-10 min-h-[48px] px-6 py-3 bg-secondary text-on-secondary rounded-lg font-button text-button shadow-md hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 w-full md:w-auto">
                    <span class="material-symbols-outlined">add_location_alt</span>
                    Iniciar Inspección de Ruta
                </a>
                <!-- Decorative element -->
                <div class="absolute right-0 top-0 w-64 h-64 bg-primary-container rounded-full -mr-20 -mt-20 opacity-20"></div>
            </section>

            <!-- Bento Grid Statistics -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Validar Rutas -->
                <a href="{{ route('rutas.index') }}" class="glass-card rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow block">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">route</span>
                        </div>
                        <span class="font-label-bold text-label-bold text-on-surface-variant">Rutas por Validar</span>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="font-display text-display text-primary">{{ str_pad($statsTecnico['rutasValidar'], 2, '0', STR_PAD_LEFT) }}</span>
                        @if($statsTecnico['rutasHoy'] > 0)
                            <span class="font-label-bold text-label-bold text-primary bg-primary/10 px-2 py-1 rounded-full">+{{ $statsTecnico['rutasHoy'] }} hoy</span>
                        @endif
                    </div>
                </a>
                <!-- Reportes Voluntarios -->
                <a href="{{ route('reportes-obstaculos.index') }}" class="glass-card rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow block">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                            <span class="material-symbols-outlined">assignment_turned_in</span>
                        </div>
                        <span class="font-label-bold text-label-bold text-on-surface-variant">Reportes Pendientes</span>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="font-display text-display text-secondary">{{ str_pad($statsTecnico['reportesPendientes'], 2, '0', STR_PAD_LEFT) }}</span>
                        @if($statsTecnico['reportesPendientes'] > 0)
                            <span class="font-label-bold text-label-bold text-secondary bg-secondary/10 px-2 py-1 rounded-full">Por revisar</span>
                        @endif
                    </div>
                </a>
                <!-- Obstáculos Críticos -->
                <div class="glass-card rounded-2xl p-6 shadow-sm border-l-4 border-error hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-full bg-error/10 flex items-center justify-center text-error">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <span class="font-label-bold text-label-bold text-on-surface-variant">Obstáculos Críticos</span>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="font-display text-display text-error">{{ str_pad($statsTecnico['obstaculosCriticos'], 2, '0', STR_PAD_LEFT) }}</span>
                        <a href="{{ route('reportes-obstaculos.index') }}" class="text-primary font-button text-button hover:underline">Ver detalles</a>
                    </div>
                </div>
            </section>

            <!-- Map & Active List Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pb-8">
                <!-- Map Section -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-h2 text-h2 text-primary">Mapa de Alertas Cercanas</h3>
                        <span class="flex items-center gap-1 text-on-surface-variant font-label-bold text-label-bold">
                            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span> En vivo
                        </span>
                    </div>
                    <div class="w-full aspect-video rounded-2xl overflow-hidden shadow-sm border border-outline-variant bg-surface-container relative">
                        <img alt="Simplified map of La Paz" class="w-full h-full object-cover grayscale opacity-60" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCySp3BN1_WQzk6xy5KwwRxaHhQ4w9PY7-u7GbTrASNLFdvN3d4ZKnsBmzCWHb_IVI709AK8xMGXqsKNVpxtaEZJ5fAF8Uush8qNxDI1lpbdH5FnIhsmnNBqQX1WUcJ8YSEeRC0XTbOFPxB3ASdKHbKdd8LXk827gc7YkIECsUpNP-D2f53Q4SGyXSyWATDROqYIxP6NnmlmNfPFNCPPGfBs7mpm8hzJzLAsiRKKhgf96JN7rfX6y-7J2RuaGBxebrBLyX4VFoSqRU"/>
                        <!-- Map HUD -->
                        <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end pointer-events-none">
                            <div class="bg-white/90 backdrop-blur p-3 rounded-lg shadow-lg pointer-events-auto border border-outline-variant">
                                <p class="font-label-bold text-label-bold text-primary">Observatorio GPS</p>
                                <p class="font-body-md text-sm text-on-surface-variant">{{ $statsTecnico['reportesPendientes'] }} alertas urbanas activas</p>
                            </div>
                            <a href="{{ route('reportes-obstaculos.mapa-monitoreo') }}" class="px-4 py-2.5 bg-primary text-white rounded-lg shadow-md flex items-center gap-2 font-bold text-xs uppercase pointer-events-auto active:scale-95 transition-transform hover:brightness-110">
                                <span class="material-symbols-outlined text-[18px]">map</span>
                                <span>Ingresar al Mapa</span>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Pending Obstacles List -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-h2 text-h2 text-primary">Reportes Críticos Recientes</h3>
                        <a href="{{ route('reportes-obstaculos.index') }}" class="font-button text-button text-primary hover:bg-primary/5 px-3 py-1 rounded-full transition-colors">Ver todos</a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($statsTecnico['reportesCriticosList'] as $reporte)
                            <a href="{{ route('reportes-obstaculos.show', $reporte->id) }}" class="glass-card rounded-2xl p-4 flex gap-4 items-start hover:bg-surface-container-low transition-colors group block">
                                <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-gray-200">
                                    @if($reporte->foto)
                                        <img src="{{ Storage::url($reporte->foto) }}" alt="Foto obstáculo" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <span class="material-symbols-outlined">image_not_supported</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-label-bold text-label-bold text-on-surface">{{ $reporte->codigo }} - {{ $reporte->tipo_obstaculo }}</h4>
                                        <span class="bg-error/10 text-error px-2 py-0.5 rounded text-xs font-bold uppercase">Crítico</span>
                                    </div>
                                    <p class="font-body-md text-sm text-on-surface-variant line-clamp-1">{{ Str::limit($reporte->descripcion, 50) }}</p>
                                    <div class="mt-2 flex items-center gap-4 text-xs font-bold text-primary opacity-70">
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">location_on</span> {{ Str::limit($reporte->direccion_referencia, 15) }}</span>
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">schedule</span> {{ $reporte->fecha_reporte?->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined self-center text-outline transition-transform group-hover:translate-x-1">chevron_right</span>
                            </a>
                        @empty
                            <div class="glass-card rounded-2xl p-8 text-center flex flex-col items-center justify-center">
                                <span class="material-symbols-outlined text-4xl text-primary/40 mb-2">check_circle</span>
                                <p class="font-label-bold text-on-surface-variant">No hay obstáculos críticos reportados.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        <!-- BottomNavBar (Visible en Móviles, Oculto en md) -->
        <nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 bg-surface-container-lowest shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] rounded-t-2xl border-t border-outline-variant">
            <!-- Dashboard (Active) -->
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center bg-secondary-container text-on-secondary-container rounded-full px-4 py-1 transition-all duration-200 active:scale-90">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                <span class="font-label-bold text-[10px]">Inicio</span>
            </a>
            <!-- Rutas -->
            <a href="{{ route('rutas.index') }}" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-1 transition-all duration-200 active:scale-90 hover:bg-surface-container-high rounded-full">
                <span class="material-symbols-outlined">route</span>
                <span class="font-label-bold text-[10px]">Rutas</span>
            </a>
            <!-- Reportes -->
            <a href="{{ route('reportes-obstaculos.index') }}" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-1 transition-all duration-200 active:scale-90 hover:bg-surface-container-high rounded-full">
                <span class="material-symbols-outlined">assignment_turned_in</span>
                <span class="font-label-bold text-[10px]">Reportes</span>
            </a>
            <!-- Perfil -->
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-1 transition-all duration-200 active:scale-90 hover:bg-surface-container-high rounded-full">
                <span class="material-symbols-outlined">account_circle</span>
                <span class="font-label-bold text-[10px]">Perfil</span>
            </a>
        </nav>
    </div>

    @push('scripts')
    <script>
        // Efecto visual de pulsación rápida
        document.querySelectorAll('a.glass-card, button').forEach(element => {
            element.addEventListener('mousedown', function(e) {
                this.classList.add('scale-[0.98]');
                setTimeout(() => this.classList.remove('scale-[0.98]'), 150);
            });
        });

        // Simulación de mapa radar activo
        setInterval(() => {
            const markers = document.querySelectorAll('.animate-pulse');
            markers.forEach(m => {
                m.style.opacity = m.style.opacity === '0.5' ? '1' : '0.5';
            });
        }, 1000);
    </script>
    @endpush
</x-app-layout>
