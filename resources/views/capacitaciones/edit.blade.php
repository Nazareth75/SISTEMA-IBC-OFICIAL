<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">
                    Editar Capacitación — {{ $capacitacion->ide_cap }}
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">
                    Modifique los datos de la capacitación.
                </p>
            </div>
            <a href="{{ route('capacitaciones.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition font-medium uppercase text-sm">
                Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Información de auditoría -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 uppercase text-xs">Código</span>
                        <p class="font-semibold text-blue-700">{{ $capacitacion->ide_cap }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-xs">Estado Actual</span>
                        <p class="font-semibold text-blue-700">{{ $capacitacion->est_cap }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-xs">Registrado</span>
                        <p class="font-medium">{{ $capacitacion->fcr_cap->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-xs">Última Modificación</span>
                        <p class="font-medium">{{ $capacitacion->fmo_cap->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">

                <form id="form-editar-capacitacion"
                      action="{{ route('capacitaciones.update', $capacitacion) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    @include('capacitaciones.partials.form')

                </form>

                <div class="flex gap-4 mt-8">
                    <button
                        type="button"
                        onclick="confirmarGuardarCambios()"
                        class="flex-1 px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-medium uppercase"
                    >
                        Guardar Cambios
                    </button>
                    <a
                        href="{{ route('capacitaciones.index') }}"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium uppercase text-center"
                    >
                        Cancelar
                    </a>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmarGuardarCambios() {
            Swal.fire({
                title: '¿GUARDAR CAMBIOS?',
                text: 'SE ACTUALIZARÁN LOS DATOS DE LA CAPACITACIÓN {{ addslashes($capacitacion->ide_cap) }}.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2d6a27',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'SÍ, GUARDAR',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-editar-capacitacion').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
