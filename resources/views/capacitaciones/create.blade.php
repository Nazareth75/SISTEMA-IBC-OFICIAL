<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">
                    Nueva Capacitación
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">
                    Complete el formulario para registrar una nueva capacitación.
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
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">

                <form id="form-crear-capacitacion"
                      action="{{ route('capacitaciones.store') }}"
                      method="POST">
                    @csrf

                    @include('capacitaciones.partials.form')

                </form>

                <div class="flex gap-4 mt-8">
                    <button
                        type="button"
                        onclick="confirmarGuardar()"
                        class="flex-1 px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-medium uppercase"
                    >
                        Guardar
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
        function confirmarGuardar() {
            Swal.fire({
                title: '¿REGISTRAR CAPACITACIÓN?',
                text: 'SE CREARÁ UNA NUEVA CAPACITACIÓN EN EL SISTEMA.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2d6a27',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'SÍ, REGISTRAR',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-crear-capacitacion').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
