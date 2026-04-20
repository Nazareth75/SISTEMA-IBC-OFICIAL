<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editar Beneficio Social
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Actualiza la información del beneficio: {{ $beneficio->nom_bso }}
                </p>
            </div>
            <a href="{{ route('beneficios.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('beneficios.update', $beneficio) }}" method="POST" id="beneficioForm">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Nombre del Beneficio -->
                        <div>
                            <label for="nom_bso" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Nombre del Beneficio
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Nombre único del beneficio, máximo 150 caracteres
                                    </div>
                                </div>
                            </label>
                            <input 
                                type="text" 
                                id="nom_bso"
                                name="nom_bso" 
                                value="{{ old('nom_bso', $beneficio->nom_bso) }}"
                                placeholder="Ej: Asistencia Médica"
                                class="w-full px-4 py-2 border @error('nom_bso') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            @error('nom_bso')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo de Beneficio -->
                        <div>
                            <label for="tip_bso" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Tipo de Beneficio
                                <span class="text-red-500 ml-1">*</span>
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Selecciona la categoría del beneficio
                                    </div>
                                </div>
                            </label>
                            <select 
                                id="tip_bso"
                                name="tip_bso"
                                class="w-full px-4 py-2 border @error('tip_bso') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                @foreach($tipos as $key => $label)
                                    <option value="{{ $key }}" @selected(old('tip_bso', $beneficio->tip_bso) === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tip_bso')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="des_bso" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Descripción
                                <div class="relative group ml-2">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-2 px-3 whitespace-nowrap">
                                        Descripción detallada (opcional, máximo 250 caracteres)
                                    </div>
                                </div>
                            </label>
                            <textarea 
                                id="des_bso"
                                name="des_bso" 
                                rows="4"
                                placeholder="Describa los detalles del beneficio..."
                                class="w-full px-4 py-2 border @error('des_bso') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >{{ old('des_bso', $beneficio->des_bso) }}</textarea>
                            @error('des_bso')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="charCount">0</span> / 250 caracteres
                            </p>
                        </div>

                        <!-- Información adicional -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700">
                                <strong>Creado:</strong> {{ $beneficio->fcr_bso->format('d/m/Y H:i') }} <br>
                                <strong>Última actualización:</strong> {{ $beneficio->fmo_bso->format('d/m/Y H:i') }} <br>
                                <strong>Asignaciones activas:</strong> {{ $beneficio->contarAsignacionesActivas() }}
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4 pt-4 border-t">
                            <a href="{{ route('beneficios.index') }}"
                               class="flex-1 px-4 py-2 text-center text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition font-medium">
                                Cancelar
                            </a>
                            <button 
                                type="submit"
                                class="flex-1 px-4 py-2 text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition font-medium">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Botón Eliminar en footer -->
                @if(auth()->user()?->esAdministrador())
                    <div class="mt-6 pt-6 border-t">
                        <form action="{{ route('beneficios.destroy', $beneficio) }}" method="POST" class="inline" data-confirm-delete data-confirm-message="Este beneficio será eliminado permanentemente. ¿Estás seguro?">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition font-medium"
                            >
                                Eliminar Beneficio
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Contador de caracteres para descripción
        const textarea = document.getElementById('des_bso');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            charCount.textContent = textarea.value.length;
        }

        textarea.addEventListener('input', updateCharCount);
        updateCharCount();
    </script>
</x-app-layout>
