<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Afiliaciones
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Listado general de afiliaciones registradas en el sistema.
                </p>
            </div>

            @if(auth()->user()?->puedeCrearAfiliacion() && !auth()->user()->tieneRol('ADMINISTRADOR_NO_VIDENTE'))
                <div>
                    <a href="{{ route('afiliaciones.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Registrar nueva afiliación
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
                @include('afiliaciones.partials.filtros')
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                @include('afiliaciones.partials.tabla')
            </div>
        </div>
    </div>
</x-app-layout>