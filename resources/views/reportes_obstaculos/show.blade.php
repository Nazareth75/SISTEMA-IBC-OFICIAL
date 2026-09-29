<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Reporte: ') }} {{ $reporteObstaculo->codigo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
            @endif

            {{-- Información general --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-bold mb-4 text-gray-800">Información del Reporte</h3>
                            <p class="mb-1"><strong>Código:</strong> <span class="font-mono text-[#006837] font-bold">{{ $reporteObstaculo->codigo }}</span></p>
                            <p class="mb-1"><strong>Tipo:</strong> {{ $reporteObstaculo->tipo_obstaculo }}</p>
                            <p class="mb-1"><strong>Método Detección:</strong> <span class="font-bold text-[#006837]">{{ $reporteObstaculo->metodo_deteccion }}</span></p>
                            <p class="mb-1"><strong>Estado:</strong> <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">{{ $reporteObstaculo->estado }}</span></p>
                            <p class="mb-1"><strong>Nivel de Riesgo:</strong> {{ $reporteObstaculo->nivel_riesgo }}</p>
                            <p class="mb-1"><strong>Prioridad:</strong> {{ $reporteObstaculo->prioridad }}</p>
                            <p class="mb-1"><strong>Descripción:</strong> {{ $reporteObstaculo->descripcion }}</p>
                            <p class="mb-1"><strong>Dirección:</strong> {{ $reporteObstaculo->direccion_referencia ?: 'No especificada' }}</p>
                            <p class="mb-1"><strong>Fecha Reporte:</strong> {{ $reporteObstaculo->fecha_reporte?->format('d/m/Y H:i') }}</p>
                            @if($reporteObstaculo->reportadoPor)
                                <p class="mb-1"><strong>Reportado Por:</strong> {{ $reporteObstaculo->reportadoPor->nombre_completo }} ({{ $reporteObstaculo->reportadoPor->role?->nom_rol }})</p>
                            @endif
                            @if($reporteObstaculo->observacion_validacion)
                                <p class="mb-1"><strong>Observación Validación:</strong> {{ $reporteObstaculo->observacion_validacion }}</p>
                            @endif
                            @if($reporteObstaculo->fecha_resolucion)
                                <p class="mb-1"><strong>Fecha Resolución:</strong> {{ $reporteObstaculo->fecha_resolucion?->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        <div>
                            @if($reporteObstaculo->foto)
                                <h3 class="text-lg font-bold mb-4 text-gray-800">Foto Evidencia</h3>
                                <img src="{{ asset('storage/' . $reporteObstaculo->foto) }}" alt="Foto del obstáculo" class="max-w-full h-auto rounded-lg shadow-md max-h-64 object-cover">
                            @else
                                <div class="bg-gray-100 flex items-center justify-center h-48 rounded-lg text-gray-500 font-bold uppercase text-xs">
                                    Sin foto adjunta
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trazabilidad de Navegación si ya existe vinculación --}}
            @if($reporteObstaculo->obstaculo)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-emerald-500">
                <div class="p-6 bg-emerald-50/50 border-b border-emerald-200">
                    <h3 class="text-lg font-bold text-emerald-900 mb-4 flex items-center gap-2 uppercase">
                        <span class="material-symbols-outlined text-emerald-700">alt_route</span>
                        TRAZABILIDAD — OBSTÁCULO Y RUTA ASOCIADA
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs font-bold text-gray-700 uppercase">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-200">
                            <span class="text-[10px] text-gray-400 block mb-1">OBSTÁCULO ASOCIADO</span>
                            <span class="text-base text-emerald-700 font-extrabold">#{{ $reporteObstaculo->obstaculo->cod_obstaculos }}</span>
                            <p class="text-[11px] text-gray-600 mt-1 font-semibold">{{ $reporteObstaculo->obstaculo->tip_obs }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-200">
                            <span class="text-[10px] text-gray-400 block mb-1">RUTA</span>
                            <span class="text-sm text-gray-900 font-bold">{{ $reporteObstaculo->obstaculo->tramo?->ruta?->nom_rut ?? 'N/A' }}</span>
                            <p class="text-[10px] text-emerald-600 font-mono mt-1">{{ $reporteObstaculo->obstaculo->tramo?->ruta?->ide_rut ?? 'N/A' }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-200">
                            <span class="text-[10px] text-gray-400 block mb-1">TRAMO DE CALLE</span>
                            <span class="text-sm text-gray-900 font-bold">{{ $reporteObstaculo->obstaculo->tramo?->nom_ctr ?? 'N/A' }}</span>
                            <p class="text-[10px] text-gray-500 mt-1">{{ $reporteObstaculo->obstaculo->tramo?->tip_ctr ?? 'TRAMO' }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-emerald-200">
                            <span class="text-[10px] text-gray-400 block mb-1">ESTADO NAVEGACIÓN</span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-800 inline-block mt-1">
                                {{ $reporteObstaculo->obstaculo->est_obs }}
                            </span>
                            <p class="text-[10px] text-gray-500 mt-1">ORIGEN: {{ $reporteObstaculo->obstaculo->ori_obs }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Formulario para Vincular a Ruta / Crear Obstáculo (Solo si ACTIVO y sin vincular) --}}
            @if(auth()->user()->puedeValidarObstaculos() && $reporteObstaculo->estado === 'ACTIVO' && !$reporteObstaculo->cod_obstaculos_rpo)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-amber-300">
                <div class="p-6 bg-amber-50/40 border-b border-amber-200">
                    <h3 class="text-lg font-bold text-amber-900 mb-2 flex items-center gap-2 uppercase">
                        <span class="material-symbols-outlined text-amber-700">add_location_alt</span>
                        VINCULAR A RUTA / CREAR OBSTÁCULO DE NAVEGACIÓN
                    </h3>
                    <p class="text-xs text-amber-800 font-medium mb-4">
                        Este reporte fue previamente validado. Seleccione la ruta y el tramo correspondiente para convertirlo en un obstáculo activo en la red de navegación.
                    </p>

                    @php
                        $tiposMapped = [
                            'POSTE CAÍDO / DAÑADO' => 'POSTE',
                            'OBRA EN LA VÍA' => 'OBRA',
                            'VEHÍCULO MAL ESTACIONADO' => 'VEHÍCULO',
                            'BACHES / AGUJEROS' => 'BACHE',
                            'COMERCIO INFORMAL' => 'VENDEDOR',
                        ];
                        $defaultTipObs = $tiposMapped[strtoupper($reporteObstaculo->tipo_obstaculo)] ?? 'OTRO';
                    @endphp

                    <form action="{{ route('reportes-obstaculos.convertir-obstaculo', $reporteObstaculo->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="cod_rutas" class="block text-xs font-bold text-gray-700 uppercase mb-1">1. SELECCIONAR RUTA *</label>
                                <select name="cod_rutas" id="cod_rutas" required onchange="onRutaChange()" class="w-full text-xs font-bold uppercase rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">-- SELECCIONE RUTA --</option>
                                    @foreach($rutas as $r)
                                        <option value="{{ $r->cod_rutas }}">{{ $r->ide_rut }} — {{ $r->nom_rut }} ({{ $r->est_rut }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="cod_calles_tramos" class="block text-xs font-bold text-gray-700 uppercase mb-1">2. SELECCIONAR TRAMO PERTENECIENTE *</label>
                                <select name="cod_calles_tramos" id="cod_calles_tramos" required disabled class="w-full text-xs font-bold uppercase rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 bg-gray-100">
                                    <option value="">-- PRIMERO SELECCIONE RUTA --</option>
                                </select>
                            </div>

                            <div>
                                <label for="tip_obs" class="block text-xs font-bold text-gray-700 uppercase mb-1">3. TIPO NORMALIZADO *</label>
                                <select name="tip_obs" id="tip_obs" required class="w-full text-xs font-bold uppercase rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    @foreach(\App\Models\Obstaculo::TIPOS as $key => $val)
                                        <option value="{{ $key }}" {{ $key === $defaultTipObs ? 'selected' : '' }}>{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="des_obs" class="block text-xs font-bold text-gray-700 uppercase mb-1">DESCRIPCIÓN DEL OBSTÁCULO (MÁX. 250 CARACTERES)</label>
                            <input type="text" name="des_obs" id="des_obs" value="{{ old('des_obs', $reporteObstaculo->descripcion) }}" maxlength="250" class="w-full text-xs font-bold uppercase rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-[#006837] hover:bg-[#004d27] text-white text-xs font-bold uppercase rounded-lg shadow-md transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">link</span>
                                <span>VINCULAR Y CREAR OBSTÁCULO</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Mapa --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold mb-4">Ubicación en el Mapa</h3>
                    @if($reporteObstaculo->latitud && $reporteObstaculo->longitud)
                        <div id="map" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>
                    @else
                        <p class="text-gray-500">Ubicación GPS no disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Acciones de Validación / Resolución --}}
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
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-bold text-xs uppercase">Validar y Activar Reporte</button>
                        </form>

                        <form action="{{ route('reportes-obstaculos.descartar', $reporteObstaculo->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea descartar este reporte?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-bold text-xs uppercase">Descartar Reporte</button>
                        </form>
                    </div>
                </div>
                @endif

                @if($reporteObstaculo->estado === 'ACTIVO' && !$reporteObstaculo->cod_obstaculos_rpo)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">Acciones de Resolución Directa</h3>
                        <form action="{{ route('reportes-obstaculos.resolver', $reporteObstaculo->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea marcar este reporte como resuelto?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold text-xs uppercase">Marcar como Resuelto</button>
                        </form>
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>

    @push('scripts')
    @if(isset($rutas) && count($rutas) > 0)
    <script>
        const rutasData = @json($rutas);

        function onRutaChange() {
            const rutaSelect = document.getElementById('cod_rutas');
            const tramoSelect = document.getElementById('cod_calles_tramos');
            const selectedRutaId = parseInt(rutaSelect.value);

            tramoSelect.innerHTML = '<option value="">-- SELECCIONE TRAMO --</option>';

            if (!selectedRutaId) {
                tramoSelect.disabled = true;
                tramoSelect.classList.add('bg-gray-100');
                return;
            }

            const rutaEncontrada = rutasData.find(r => r.cod_rutas === selectedRutaId);
            if (rutaEncontrada && rutaEncontrada.tramos && rutaEncontrada.tramos.length > 0) {
                rutaEncontrada.tramos.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.cod_calles_tramos;
                    opt.textContent = `${t.nom_ctr} (${t.tip_ctr}) — Orden: ${t.ord_ctr}`;
                    tramoSelect.appendChild(opt);
                });
                tramoSelect.disabled = false;
                tramoSelect.classList.remove('bg-gray-100');
            } else {
                const opt = document.createElement('option');
                opt.value = "";
                opt.textContent = "ESTA RUTA NO TIENE TRAMOS REGISTRADOS";
                tramoSelect.appendChild(opt);
                tramoSelect.disabled = true;
                tramoSelect.classList.add('bg-gray-100');
            }
        }
    </script>
    @endif

    @if($reporteObstaculo->latitud && $reporteObstaculo->longitud)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
    @endif
    @endpush
</x-app-layout>
