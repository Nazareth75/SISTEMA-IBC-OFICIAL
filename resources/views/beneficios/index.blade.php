<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Catálogo de Beneficios Sociales
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gestión de beneficios que el IBC asigna a sus afiliados activos.
                </p>
            </div>

            @if(auth()->user()?->esAdministrador())
                <div>
                    <a href="{{ route('beneficios.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Beneficio
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
                @include('beneficios.partials.filtros')
            </div>

            <!-- Tabla de Beneficios -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                @include('beneficios.partials.tabla')
            </div>
        </div>
    </div>
</x-app-layout>
