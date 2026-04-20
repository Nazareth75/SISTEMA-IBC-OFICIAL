<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ver/Editar Asignación de Beneficio
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Actualiza los detalles de la asignación del beneficio.
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

            <!-- Card de información del afiliado y beneficio -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Afiliado</h3>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $asignacion->afiliacion->persona->nom_pnv }} {{ $asignacion->afiliacion->persona->ape_pnv }}
                        </p>
                        <p class="text-sm text-gray-600">CI: {{ $asignacion->afiliacion->persona->cii_pnv }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Beneficio</h3>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $asignacion->beneficio->nom_bso }}
                        </p>
                        <p class="text-sm text-gray-600">Tipo: {{ $asignacion->beneficio->getNombreTipo() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('asignacion-beneficios.update', $asignacion) }}" method="POST" id="asignacionForm">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Beneficio (Solo lectura en esta pantalla) -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600">El afiliado no puede cambiarse una vez asignado el beneficio</p>
                        </div>

                        <!-- Selector de Beneficio -->
                        <div>
                            <label for="cod_beneficios_sociales_abe" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Beneficio Social
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="cod_beneficios_sociales_abe"
                                name="cod_beneficios_sociales_abe"
                                class="w-full px-4 py-2 border @error('cod_beneficios_sociales_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                @foreach($beneficiosActivos as $beneficio)
                                    <option value="{{ $beneficio->cod_beneficios_sociales }}" @selected($asignacion->cod_beneficios_sociales_abe == $beneficio->cod_beneficios_sociales)>
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
                            <label for="fas_abe" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Asignación
                                <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="fas_abe"
                                name="fas_abe" 
                                value="{{ old('fas_abe', $asignacion->fas_abe->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border @error('fas_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            @error('fas_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Fin (Opcional) -->
                        <div>
                            <label for="ffi_abe" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin
                            </label>
                            <input 
                                type="date" 
                                id="ffi_abe"
                                name="ffi_abe" 
                                value="{{ old('ffi_abe', $asignacion->ffi_abe?->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border @error('ffi_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('ffi_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="est_abe" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado
                                <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="est_abe"
                                name="est_abe"
                                class="w-full px-4 py-2 border @error('est_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                @foreach($estados as $key => $label)
                                    <option value="{{ $key }}" @selected($asignacion->est_abe === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('est_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="obs_abe" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones
                            </label>
                            <textarea 
                                id="obs_abe"
                                name="obs_abe" 
                                rows="4"
                                placeholder="Notas adicionales sobre esta asignación..."
                                class="w-full px-4 py-2 border @error('obs_abe') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >{{ old('obs_abe', $asignacion->obs_abe) }}</textarea>
                            @error('obs_abe')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="charCount">0</span> / 500 caracteres
                            </p>
                        </div>

                        <!-- Información adicional -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700 space-y-1">
                                <strong>Creado:</strong> {{ $asignacion->fcr_abe->format('d/m/Y H:i') }} <br>
                                <strong>Última actualización:</strong> {{ $asignacion->fmo_abe->format('d/m/Y H:i') }} <br>
                                @if($asignacion->usuarioRegistro)
                                    <strong>Registrado por:</strong> {{ $asignacion->usuarioRegistro->nom_usu }} {{ $asignacion->usuarioRegistro->ape_usu }}
                                @endif
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
                                class="flex-1 px-4 py-2 text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition font-medium">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Botón Eliminar en footer -->
                @if(auth()->user()?->esAdministrador())
                    <div class="mt-6 pt-6 border-t">
                        <form action="{{ route('asignacion-beneficios.destroy', $asignacion) }}" method="POST" class="inline" data-confirm-delete data-confirm-message="Esta asignación será eliminada permanentemente. ¿Estás seguro?">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition font-medium"
                            >
                                Eliminar Asignación
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
