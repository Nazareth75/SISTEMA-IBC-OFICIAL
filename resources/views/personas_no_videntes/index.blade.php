<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Personas No Videntes
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Listado general de personas registradas en el sistema.
                </p>
            </div>

            <div>
                @php($usuario = auth()->user())
                @if($usuario?->puedeCrearPersonaNoVidente())
                    <div>
                        <a href="{{ route('personas-no-videntes.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Registrar nueva persona
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
                @include('personas_no_videntes.partials.filtros')
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                @include('personas_no_videntes.partials.tabla')
            </div>
        </div>
    </div>
</x-app-layout>