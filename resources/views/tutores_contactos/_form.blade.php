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