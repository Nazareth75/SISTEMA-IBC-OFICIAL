@php
    $tutor = $tutor ?? null;
@endphp

<div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nom_tco" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="nom_tco" id="nom_tco"
                   value="{{ old('nom_tco', $tutor?->nom_tco) }}"
                   title="SOLO LETRAS Y ESPACIOS"
                   pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\\s]+$"
                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('nom_tco')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:col-span-2">
            <div>
                <label for="app_tco" class="block text-sm font-medium text-gray-700 mb-1">Apellido paterno *</label>
                <input type="text" name="app_tco" id="app_tco"
                       value="{{ old('app_tco', $tutor?->app_tco) }}"
                       title="SOLO LETRAS Y ESPACIOS"
                       pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\\s]+$"
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('app_tco')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apm_tco" class="block text-sm font-medium text-gray-700 mb-1">Apellido materno *</label>
                <input type="text" name="apm_tco" id="apm_tco"
                       value="{{ old('apm_tco', $tutor?->apm_tco) }}"
                       title="SOLO LETRAS Y ESPACIOS"
                       pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\\s]+$"
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('apm_tco')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="par_tco" class="block text-sm font-medium text-gray-700 mb-1">Parentesco *</label>
            <input type="text" name="par_tco" id="par_tco"
                   value="{{ old('par_tco', $tutor?->par_tco) }}"
                   title="SOLO LETRAS Y ESPACIOS"
                   pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\\s]+$"
                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('par_tco')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tel_tco" class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
            <input type="text" name="tel_tco" id="tel_tco"
                   value="{{ old('tel_tco', $tutor?->tel_tco) }}"
                   inputmode="numeric"
                   pattern="^[0-9]+$"
                   oninput="this.value = this.value.replace(/\D/g, '')"
                   title="SOLO NÚMEROS"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('tel_tco')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="cor_tco" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
            <input type="email" name="cor_tco" id="cor_tco"
                   value="{{ old('cor_tco', $tutor?->cor_tco) }}"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('cor_tco')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="etl_tco" value="1"
                       @checked(old('etl_tco', $tutor?->etl_tco))
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Marcar como tutor legal actual</span>
            </label>
            @error('etl_tco')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="seccion-documentos-legal" class="md:col-span-2 space-y-4 p-4 bg-gray-50 rounded-xl border border-gray-200 mt-2" style="display: none;">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 text-base">folder_shared</span>
                    Documentación de Respaldo (Tutor Legal)
                </h3>
                <a href="{{ route('personas-no-videntes.tutores.documento-base', $persona) }}{{ $tutor ? '?tutor_id=' . $tutor->getKey() : '' }}" 
                   target="_blank" 
                   class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">print</span>
                    Imprimir Documento Base
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="doc_ci_tco" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cédula de Identidad (PDF o Imagen)</label>
                    <input type="file" name="doc_ci_tco" id="doc_ci_tco" accept=".pdf,image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg p-1">
                    @error('doc_ci_tco')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($tutor && $tutor->doc_ci_tco)
                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <span class="text-green-600 font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                Archivo cargado
                            </span>
                            <a href="{{ asset('storage/' . $tutor->doc_ci_tco) }}" target="_blank" class="text-indigo-600 hover:underline">Ver actual</a>
                        </div>
                    @endif
                </div>

                <div>
                    <label for="doc_legal_tco" class="block text-xs font-bold text-gray-700 uppercase mb-1">Papel Legal / Resolución (PDF o Imagen)</label>
                    <input type="file" name="doc_legal_tco" id="doc_legal_tco" accept=".pdf,image/*"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg p-1">
                    @error('doc_legal_tco')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($tutor && $tutor->doc_legal_tco)
                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <span class="text-green-600 font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                Archivo cargado
                            </span>
                            <a href="{{ asset('storage/' . $tutor->doc_legal_tco) }}" target="_blank" class="text-indigo-600 hover:underline">Ver actual</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <button type="submit"
                class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
            Guardar
        </button>

        <a href="{{ route('personas-no-videntes.tutores.index', $persona) }}"
           class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
            Volver
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.querySelector('input[name="etl_tco"]');
        const docSection = document.getElementById('seccion-documentos-legal');
        
        function toggleSection() {
            if (checkbox && docSection) {
                if (checkbox.checked) {
                    docSection.style.display = 'block';
                } else {
                    docSection.style.display = 'none';
                }
            }
        }
        
        if (checkbox) {
            checkbox.addEventListener('change', toggleSection);
            toggleSection();
        }
    });
</script>