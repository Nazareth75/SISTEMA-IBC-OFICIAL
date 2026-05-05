<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold text-[#004d27] tracking-tight">
                    Gestión de Personas No Videntes
                </h2>
                <p class="text-gray-600 font-medium mt-1">
                    Listado general de personas registradas en el sistema.
                </p>
            </div>

            @php($usuario = auth()->user())

            @if($usuario?->puedeCrearPersonaNoVidente())
                <a href="{{ route('personas-no-videntes.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-[#006837] text-white rounded-xl font-bold text-sm uppercase tracking-widest shadow hover:bg-[#004d27] transition">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Registrar nueva persona
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-[#f9f9fc] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-6 text-[#004d27]">
                    <span class="material-symbols-outlined">filter_list</span>
                    <h3 class="font-bold text-lg">Filtros de búsqueda</h3>
                </div>

                @include('personas_no_videntes.partials.filtros')
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                @include('personas_no_videntes.partials.tabla')
            </div>

        </div>
    </div>
</x-app-layout>