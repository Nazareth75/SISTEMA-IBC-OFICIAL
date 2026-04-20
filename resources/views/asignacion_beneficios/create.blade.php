<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Asignar Beneficio Social
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Asigna un beneficio a un afiliado activo.
                </p>
            </div>
            <a href="{{ route('asignacion-beneficios.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Volver al Listado
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Alerta de error si existe -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errores en el formulario</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('asignacion-beneficios.store') }}" method="POST" id="asignacionForm">
                    @csrf

                    <div class="space-y-6">
                        <!-- Búsqueda de Afiliado -->
                        <div>
                            <label for="cod_afiliaciones_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Buscar Afiliado Activo
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap z-50">
                                        Haz clic para ver afiliados o escribe para buscar por nombre/CI
                                    </div>
                                </div>
                            </label>
                            <select 
                                id="cod_afiliaciones_abe"
                                name="cod_afiliaciones_abe" 
                                class="w-full px-4 py-2 border @error('cod_afiliaciones_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            >
                                <option value="">Selecciona un afiliado...</option>
                            </select>
                            @error('cod_afiliaciones_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-2">
                                💡 <strong>Consejo:</strong> Haz clic en el campo o escribe el nombre/CI para buscar afiliados activos.
                            </p>
                        </div>

                        <!-- Selector de Beneficio -->
                        <div>
                            <label for="cod_beneficios_sociales_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Beneficio Social
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Solo beneficios activos disponibles
                                    </div>
                                </div>
                            </label>
                            <select 
                                id="cod_beneficios_sociales_abe"
                                name="cod_beneficios_sociales_abe"
                                class="w-full px-4 py-2 border @error('cod_beneficios_sociales_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            >
                                <option value="">Selecciona un beneficio...</option>
                                @foreach($beneficiosActivos as $beneficio)
                                    <option value="{{ $beneficio->cod_beneficios_sociales }}" @selected(old('cod_beneficios_sociales_abe') == $beneficio->cod_beneficios_sociales)>
                                        {{ $beneficio->nom_bso }} ({{ $beneficio->getNombreTipo() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cod_beneficios_sociales_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Asignación -->
                        <div>
                            <label for="fas_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Fecha de Asignación
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Fecha en que comienza el beneficio
                                    </div>
                                </div>
                            </label>
                            <input 
                                type="date" 
                                id="fas_abe"
                                name="fas_abe" 
                                value="{{ old('fas_abe', now()->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border @error('fas_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            />
                            @error('fas_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Fin (Opcional) -->
                        <div>
                            <label for="ffi_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Opcional. Déjalo vacío si no tiene fecha límite
                                    </div>
                                </div>
                            </label>
                            <input 
                                type="date" 
                                id="ffi_abe"
                                name="ffi_abe" 
                                value="{{ old('ffi_abe') }}"
                                class="w-full px-4 py-2 border @error('ffi_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                            @error('ffi_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="est_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Estado
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Estado inicial de la asignación
                                    </div>
                                </div>
                            </label>
                            <select 
                                id="est_abe"
                                name="est_abe"
                                class="w-full px-4 py-2 border @error('est_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                required
                            >
                                <option value="ACTIVO" @selected(old('est_abe', 'ACTIVO') === 'ACTIVO')>Activo</option>
                                <option value="SUSPENDIDO" @selected(old('est_abe') === 'SUSPENDIDO')>Suspendido</option>
                                <option value="FINALIZADO" @selected(old('est_abe') === 'FINALIZADO')>Finalizado</option>
                            </select>
                            @error('est_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="obs_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Observaciones
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Información adicional sobre la asignación
                                    </div>
                                </div>
                            </label>
                            <textarea 
                                id="obs_abe"
                                name="obs_abe" 
                                rows="3"
                                placeholder="Notas adicionales sobre esta asignación..."
                                class="w-full px-4 py-2 border @error('obs_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >{{ old('obs_abe') }}</textarea>
                            @error('obs_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="charCount">0</span> / 500 caracteres
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 pt-4 border-t">
                            <a href="{{ route('asignacion-beneficios.index') }}"
                               class="flex-1 px-4 py-2 text-center text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition font-medium">
                                Cancelar
                            </a>
                            <button 
                                type="submit"
                                class="flex-1 px-4 py-2 text-center text-white bg-green-600 rounded-lg hover:bg-green-700 transition font-medium">
                                Asignar Beneficio
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery (requerido por Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 para buscador de afiliados -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Inicializando Select2...');
            
            // Inicializar Select2 para búsqueda de afiliados
            $('#cod_afiliaciones_abe').select2({
                placeholder: '🔍 Busca o selecciona un afiliado...',
                allowClear: true,
                minimumInputLength: 0,
                width: '100%',
                ajax: {
                    url: '{{ route("afiliados.buscar") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        console.log('Búsqueda Select2:', params.term || '(vacío)');
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        console.log('Resultados recibidos:', data);
                        return {
                            results: data
                        };
                    },
                    error: function(err) {
                        console.error('Error en AJAX:', err);
                    },
                    cache: true
                }
            });

            // Event listeners para debug
            $('#cod_afiliaciones_abe').on('select2:opening', function(e) {
                console.log('Select2 abierto');
            });

            $('#cod_afiliaciones_abe').on('select2:select', function(e) {
                console.log('Afiliado seleccionado:', e.params.data);
            });

            // Contador de caracteres
            const textarea = document.getElementById('obs_abe');
            const charCount = document.getElementById('charCount');
            
            function updateCharCount() {
                charCount.textContent = textarea.value.length;
            }

            textarea.addEventListener('input', updateCharCount);
            updateCharCount();
        });
    </script>
</x-app-layout>
