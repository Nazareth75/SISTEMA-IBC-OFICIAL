<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Registrar Nuevo Trámite
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Completa el formulario para crear un nuevo trámite institucional.
                </p>
            </div>
            <a href="{{ route('tramites-instituciones.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('tramites-instituciones.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Afiliado (Select2) -->
                        <div>
                            <label for="cod_afiliaciones_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Afiliado
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="cod_afiliaciones_tin"
                                name="cod_afiliaciones_tin"
                                class="w-full"
                                style="width: 100%"
                                required
                            >
                                <option value="">Selecciona un afiliado...</option>
                                @if(old('cod_afiliaciones_tin'))
                                    @php
                                        $afiliado = \App\Models\Afiliacion::find(old('cod_afiliaciones_tin'));
                                    @endphp
                                    @if($afiliado)
                                        <option value="{{ $afiliado->cod_afiliaciones }}" selected>
                                            {{ $afiliado->persona->nom_pnv ?? 'N/A' }}
                                        </option>
                                    @endif
                                @endif
                            </select>
                            @error('cod_afiliaciones_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Institución Externa -->
                        <div>
                            <label for="cod_instituciones_externas_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Institución Externa
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="cod_instituciones_externas_tin"
                                name="cod_instituciones_externas_tin"
                                class="w-full px-4 py-2 border @error('cod_instituciones_externas_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona una institución...</option>
                                @foreach(\App\Models\InstitucionExterna::activas()->get() as $institucion)
                                    <option value="{{ $institucion->cod_instituciones_externas }}" 
                                            @selected(old('cod_instituciones_externas_tin') === (string)$institucion->cod_instituciones_externas)>
                                        {{ $institucion->nom_iex }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cod_instituciones_externas_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo de Trámite -->
                        <div>
                            <label for="tti_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Tipo de Trámite
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="tti_tin"
                                name="tti_tin"
                                class="w-full px-4 py-2 border @error('tti_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona un tipo...</option>
                                @foreach(\App\Models\TramiteInstitucion::TIPOS as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('tti_tin') === $clave)>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tti_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="des_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Descripción del Trámite
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea 
                                id="des_tin"
                                name="des_tin" 
                                rows="3"
                                placeholder="Describe el trámite a realizar..."
                                class="w-full px-4 py-2 border @error('des_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >{{ old('des_tin') }}</textarea>
                            @error('des_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Inicio -->
                        <div>
                            <label for="fei_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="fei_tin"
                                name="fei_tin" 
                                value="{{ old('fei_tin') }}"
                                class="w-full px-4 py-2 border @error('fei_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            @error('fei_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Resolución Esperada -->
                        <div>
                            <label for="fer_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Fecha de Resolución Esperada
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input
                                type="date"
                                id="fer_tin"
                                name="fer_tin"
                                value="{{ old('fer_tin') }}"
                                class="w-full px-4 py-2 border @error('fer_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('fer_tin')
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
                            Registrar Trámite
                        </button>
                        <a 
                            href="{{ route('tramites-instituciones.index') }}"
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
            $('#cod_afiliaciones_tin').select2({
                ajax: {
                    url: '{{ route("afiliados.tramites") }}',
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
