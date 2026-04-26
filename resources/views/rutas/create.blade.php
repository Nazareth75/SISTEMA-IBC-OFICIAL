<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rutas.index') }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-route mr-2" style="color:#2d6a27"></i> NUEVA RUTA SEGURA
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">SE CREARÁ EN ESTADO BORRADOR — LUEGO PODRÁ AGREGAR TRAMOS</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('rutas.store') }}" method="POST">
                @csrf
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 border-b pb-2">
                        DATOS GENERALES DE LA RUTA
                    </h3>
                    @include('rutas.partials.form')
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <a href="{{ route('rutas.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-6 py-2 rounded uppercase text-sm">
                        CANCELAR
                    </a>
                    <button type="submit"
                        class="text-white font-bold px-6 py-2 rounded uppercase text-sm shadow"
                        style="background-color:#2d6a27">
                        <i class="fas fa-save mr-1"></i> CREAR RUTA
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const e = document.querySelector('meta[name="flash-error"]');
        if (e) Swal.fire({ toast:true, position:'top-end', icon:'error', title: e.content, showConfirmButton:false, timer:4000, timerProgressBar:true });
    });
    </script>
    @endpush
</x-app-layout>
