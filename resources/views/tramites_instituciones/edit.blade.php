<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editar Trámite
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Afiliado: {{ $tramite->afiliacion->persona->nom_pnv ?? 'N/A' }}
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
            <!-- Información de Auditoría -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Estado Actual:</span>
                        <p class="font-medium text-blue-700">{{ $tramite->est_tin }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Días Transcurridos:</span>
                        <p class="font-medium">{{ $tramite->getDiasTranscurridos() }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Creado:</span>
                        <p class="font-medium">{{ $tramite->fcr_tin->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Modificado:</span>
                        <p class="font-medium">{{ $tramite->fmo_tin->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form id="form-editar" action="{{ route('tramites-instituciones.update', $tramite) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Afiliado (Solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Afiliado (No editable)
                            </label>
                            <input
                                type="text"
                                value="{{ $tramite->afiliacion->persona->nom_pnv ?? 'N/A' }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                disabled
                            />
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
                                            @selected(old('cod_instituciones_externas_tin', (string)$tramite->cod_instituciones_externas_tin) === (string)$institucion->cod_instituciones_externas)>
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
                                    <option value="{{ $clave }}" @selected(old('tti_tin', $tramite->tti_tin) === $clave)>
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
                            >{{ old('des_tin', $tramite->des_tin) }}</textarea>
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
                                value="{{ old('fei_tin', $tramite->fei_tin->format('Y-m-d')) }}"
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
                            </label>
                            <input
                                type="date"
                                id="fer_tin"
                                name="fer_tin"
                                value="{{ old('fer_tin', $tramite->fer_tin?->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border @error('fer_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('fer_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado del Trámite -->
                        <div>
                            <label for="est_tin" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Estado del Trámite
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select
                                id="est_tin"
                                name="est_tin"
                                class="w-full px-4 py-2 border @error('est_tin') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona un estado...</option>
                                @foreach(\App\Models\TramiteInstitucion::ESTADOS as $clave => $etiqueta)
                                    <option value="{{ $clave }}" @selected(old('est_tin', $tramite->est_tin) === $clave)>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('est_tin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </form>

                <!-- Botones fuera del form principal pero vinculados por form="form-editar" -->
                <div class="flex gap-4 mt-8">
                    <button
                        type="submit"
                        form="form-editar"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                    >
                        Guardar Cambios
                    </button>

                    <form
                        action="{{ route('tramites-instituciones.destroy', $tramite) }}"
                        method="POST"
                        class="flex-1"
                        data-confirm-delete
                        data-confirm-message="¿Estás seguro de que quieres eliminar este trámite? Esta acción no se puede deshacer.">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            Eliminar
                        </button>
                    </form>

                    <a
                        href="{{ route('tramites-instituciones.index') }}"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-center"
                    >
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
