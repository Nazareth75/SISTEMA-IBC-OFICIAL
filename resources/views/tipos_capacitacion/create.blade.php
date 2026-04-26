<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                REGISTRAR TIPO DE CAPACITACIÓN
            </h2>

            <a href="{{ route('tipos-capacitacion.index') }}"
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-semibold">
                VOLVER
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        DATOS DEL TIPO DE CAPACITACIÓN
                    </h3>
                    <p class="text-sm text-gray-600">
                        Complete la información para registrar un nuevo tipo de capacitación.
                    </p>
                </div>

                <form id="form-tipo-capacitacion"
                      action="{{ route('tipos-capacitacion.store') }}"
                      method="POST"
                      class="p-6 space-y-6">
                    @csrf

                    {{-- NOMBRE --}}
                    <div>
                        <label for="nom_tca" class="block text-sm font-medium text-gray-700 uppercase">
                            Nombre <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               id="nom_tca"
                               name="nom_tca"
                               value="{{ old('nom_tca') }}"
                               maxlength="100"
                               required
                               title="INGRESE EL NOMBRE DEL TIPO DE CAPACITACIÓN"
                               placeholder="EJ: BRAILLE"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase @error('nom_tca') border-red-500 @enderror">

                        @error('nom_tca')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DESCRIPCIÓN --}}
                    <div>
                        <label for="des_tca" class="block text-sm font-medium text-gray-700 uppercase">
                            Descripción
                        </label>

                        <textarea id="des_tca"
                                  name="des_tca"
                                  rows="4"
                                  maxlength="250"
                                  title="DESCRIPCIÓN OPCIONAL DEL TIPO DE CAPACITACIÓN"
                                  placeholder="DESCRIPCIÓN OPCIONAL..."
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('des_tca') border-red-500 @enderror">{{ old('des_tca') }}</textarea>

                        <div class="mt-1 flex justify-between text-xs text-gray-500">
                            <span>MÁXIMO 250 CARACTERES.</span>
                            <span id="contador-descripcion">0 / 250</span>
                        </div>

                        @error('des_tca')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('tipos-capacitacion.index') }}"
                           class="px-5 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium uppercase text-sm">
                            Cancelar
                        </a>

                        <button type="button"
                                onclick="confirmarGuardadoTipo()"
                                class="px-5 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 font-medium uppercase text-sm">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        const descripcion = document.getElementById('des_tca');
        const contador = document.getElementById('contador-descripcion');

        function actualizarContador() {
            if (!descripcion || !contador) return;
            contador.textContent = `${descripcion.value.length} / 250`;
        }

        if (descripcion) {
            descripcion.addEventListener('input', actualizarContador);
            actualizarContador();
        }

        function confirmarGuardadoTipo() {
            const nombre = document.getElementById('nom_tca').value.trim();

            if (!nombre) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'EL NOMBRE DEL TIPO ES OBLIGATORIO.',
                    showConfirmButton: false,
                    timer: 3000,
                });
                return;
            }

            Swal.fire({
                title: '¿REGISTRAR TIPO DE CAPACITACIÓN?',
                html: `SE REGISTRARÁ EL TIPO: <strong>${nombre.toUpperCase()}</strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1d4ed8',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'SÍ, REGISTRAR',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-tipo-capacitacion').submit();
                }
            });
        }
    </script>
</x-app-layout>