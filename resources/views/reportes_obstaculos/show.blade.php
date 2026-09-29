<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Reporte: ') }} {{ $reporteObstaculo->codigo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-bold mb-4">Información del Obstáculo</h3>
                            <p><strong>Tipo:</strong> {{ $reporteObstaculo->tipo_obstaculo }}</p>
                            <p><strong>Estado:</strong> {{ $reporteObstaculo->estado }}</p>
                            <p><strong>Nivel de Riesgo:</strong> {{ $reporteObstaculo->nivel_riesgo }}</p>
                            <p><strong>Prioridad:</strong> {{ $reporteObstaculo->prioridad }}</p>
                            <p><strong>Descripción:</strong> {{ $reporteObstaculo->descripcion }}</p>
                            <p><strong>Dirección:</strong> {{ $reporteObstaculo->direccion_referencia }}</p>
                            <p><strong>Fecha Reporte:</strong> {{ $reporteObstaculo->fecha_reporte?->format('d/m/Y H:i') }}</p>
                            @if($reporteObstaculo->observacion_validacion)
                                <p><strong>Observación Validación:</strong> {{ $reporteObstaculo->observacion_validacion }}</p>
                            @endif
                            @if($reporteObstaculo->fecha_resolucion)
                                <p><strong>Fecha Resolución:</strong> {{ $reporteObstaculo->fecha_resolucion?->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        <div>
                            @if($reporteObstaculo->foto)
                                <h3 class="text-lg font-bold mb-4">Foto</h3>
                                <img src="{{ asset('storage/' . $reporteObstaculo->foto) }}" alt="Foto del obstáculo" class="max-w-full h-auto rounded-lg shadow-md max-h-64 object-cover">
                            @else
                                <div class="bg-gray-100 flex items-center justify-center h-48 rounded-lg text-gray-500">
                                    Sin foto adjunta
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Ubicación en el Mapa</h3>
                    @if($reporteObstaculo->latitud && $reporteObstaculo->longitud)
                        <div id="map" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>
                    @else
                        <p class="text-gray-500">Ubicación GPS no disponible.</p>
                    @endif
                </div>
            </div>

            @if(auth()->user()->puedeValidarObstaculos())
                @if($reporteObstaculo->estado === 'REPORTADO')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Acciones de Validación</h3>
                        <form action="{{ route('reportes-obstaculos.validar', $reporteObstaculo->id) }}" method="POST" class="mb-4">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label for="observacion_validacion" class="block text-sm font-medium text-gray-700">Observación (Opcional)</label>
                                <textarea name="observacion_validacion" id="observacion_validacion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Validar y Activar</button>
                        </form>

                        <form action="{{ route('reportes-obstaculos.descartar', $reporteObstaculo->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea descartar este reporte?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Descartar</button>
                        </form>
                    </div>
                </div>
                @endif

                @if($reporteObstaculo->estado === 'ACTIVO')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Acciones de Resolución</h3>
                        <form action="{{ route('reportes-obstaculos.resolver', $reporteObstaculo->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea marcar este obstáculo como resuelto?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Marcar como Resuelto</button>
                        </form>
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>

    @if($reporteObstaculo->latitud && $reporteObstaculo->longitud)
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lat = {{ $reporteObstaculo->latitud }};
            var lng = {{ $reporteObstaculo->longitud }};
            var map = L.map('map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup('{{ $reporteObstaculo->tipo_obstaculo }}')
                .openPopup();
        });
    </script>
    @endpush
    @endif
</x-app-layout>
