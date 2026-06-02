<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Registrar Nuevo Acceso a Salud
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Completa el formulario para registrar un nuevo acceso a una institución de salud.
                </p>
            </div>
            <a href="{{ route('accesos-salud.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('accesos-salud.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Afiliado (Select2) -->
                        <div>
                            <label for="cod_afiliaciones_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Afiliado
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="cod_afiliaciones_asa"
                                name="cod_afiliaciones_asa"
                                class="w-full"
                                style="width: 100%"
                                required
                            >
                                <option value="">Selecciona un afiliado...</option>
                                @if(old('cod_afiliaciones_asa'))
                                    @php
                                        $afiliado = \App\Models\Afiliacion::find(old('cod_afiliaciones_asa'));
                                    @endphp
                                    @if($afiliado)
                                        <option value="{{ $afiliado->cod_afiliaciones }}" selected>
                                            {{ $afiliado->persona->nom_pnv ?? 'N/A' }}
                                        </option>
                                    @endif
                                @endif
                            </select>
                            @error('cod_afiliaciones_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Institución Externa -->
                        <div>
                            <label for="cod_instituciones_externas_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Institución Externa
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="cod_instituciones_externas_asa"
                                name="cod_instituciones_externas_asa"
                                class="w-full px-4 py-2 border @error('cod_instituciones_externas_asa') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona una institución...</option>
                                @foreach(\App\Models\InstitucionExterna::activas()->get() as $institucion)
                                    <option value="{{ $institucion->cod_instituciones_externas }}" 
                                            @selected(old('cod_instituciones_externas_asa') === (string)$institucion->cod_instituciones_externas)>
                                        {{ $institucion->nom_iex }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cod_instituciones_externas_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Acceso -->
                        <div>
                            <label for="fea_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Fecha de Acceso
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="fea_asa"
                                name="fea_asa" 
                                value="{{ old('fea_asa') }}"
                                class="w-full px-4 py-2 border @error('fea_asa') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            @error('fea_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Motivo del Acceso -->
                        <div>
                            <label for="mot_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Motivo del Acceso
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea 
                                id="mot_asa"
                                name="mot_asa" 
                                rows="3"
                                placeholder="Describe el motivo del acceso..."
                                class="w-full px-4 py-2 border @error('mot_asa') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >{{ old('mot_asa') }}</textarea>
                            @error('mot_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="est_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Estado
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="est_asa"
                                name="est_asa"
                                class="w-full px-4 py-2 border @error('est_asa') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona un estado...</option>
                                @foreach(\App\Models\AccesoSalud::ESTADOS as $estado => $label)
                                    <option value="{{ $estado }}" @selected(old('est_asa') === $estado)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('est_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="obs_asa" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Observaciones
                            </label>
                            <textarea 
                                id="obs_asa"
                                name="obs_asa" 
                                rows="2"
                                placeholder="Notas adicionales (opcional)..."
                                class="w-full px-4 py-2 border @error('obs_asa') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >{{ old('obs_asa') }}</textarea>
                            @error('obs_asa')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4 mt-8">
                        <button 
                            type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Registrar Acceso
                        </button>
                        <a 
                            href="{{ route('accesos-salud.index') }}"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-center"
                        >
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cod_afiliaciones_asa').select2({
                ajax: {
                    url: '{{ route("afiliados.accesos") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    }
                },
                minimumInputLength: 0,
                placeholder: 'Busca por nombre o cédula...'
            });
        });
    </script>
    @endpush
</x-app-layout>
