<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('niveles-seguridad.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-shield-alt mr-2" style="color:#2d6a27"></i>
                    EDITAR NIVEL: {{ $nivelSeguridad->nom_nse }}
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">SOLO SE PUEDE MODIFICAR LA DESCRIPCIÓN Y EL COLOR</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('niveles-seguridad.update', $nivelSeguridad) }}" method="POST">
                @csrf @method('PUT')
                <div class="bg-white rounded-lg shadow p-6 space-y-5">

                    {{-- Vista previa del nivel --}}
                    <div class="flex items-center gap-3 p-3 rounded-lg border"
                        style="border-color:{{ $nivelSeguridad->colorHex() }}; background-color:{{ $nivelSeguridad->colorHex() }}10">
                        <span class="w-10 h-10 rounded-full font-bold text-white flex items-center justify-center text-lg"
                            style="background-color:{{ $nivelSeguridad->colorHex() }}">
                            {{ $nivelSeguridad->niv_nse }}
                        </span>
                        <div>
                            <p class="font-bold uppercase" style="color:{{ $nivelSeguridad->colorHex() }}">
                                {{ $nivelSeguridad->nom_nse }}
                            </p>
                            <p class="text-xs text-gray-500 uppercase">NIVEL {{ $nivelSeguridad->niv_nse }} — FIJO DEL SISTEMA</p>
                        </div>
                    </div>

                    {{-- Color --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
                            COLOR (HEX) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="preview-color"
                                value="{{ old('col_nse', $nivelSeguridad->colorHex()) }}"
                                oninput="document.getElementById('input-color').value = this.value"
                                class="h-10 w-16 border rounded cursor-pointer">
                            <input type="text" name="col_nse" id="input-color"
                                value="{{ old('col_nse', $nivelSeguridad->colorHex()) }}"
                                placeholder="#000000"
                                title="CÓDIGO HEX DEL COLOR — OBLIGATORIO"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                oninput="document.getElementById('preview-color').value = this.value"
                                class="flex-1 border rounded px-3 py-2 font-mono focus:ring-2 focus:ring-green-600 focus:outline-none @error('col_nse') border-red-500 @enderror">
                        </div>
                        @error('col_nse')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">DESCRIPCIÓN</label>
                        <textarea name="des_nse" rows="3" maxlength="200"
                            placeholder="DESCRIPCIÓN DEL NIVEL DE SEGURIDAD"
                            title="DESCRIPCIÓN OPCIONAL"
                            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">{{ old('des_nse', $nivelSeguridad->des_nse) }}</textarea>
                    </div>

                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <a href="{{ route('niveles-seguridad.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-5 py-2 rounded uppercase text-sm">
                        CANCELAR
                    </a>
                    <button type="submit"
                        class="text-white font-bold px-5 py-2 rounded uppercase text-sm shadow"
                        style="background-color:#2d6a27">
                        <i class="fas fa-save mr-1"></i> GUARDAR
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
