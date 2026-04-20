<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Accesos a Salud
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gestión de accesos y visitas a instituciones externas de salud.
                </p>
            </div>

            @if(auth()->user()?->esAdministrador())
                <div>
                    <a href="{{ route('accesos-salud.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Acceso
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mensajes de sesión -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
                @include('accesos_salud.partials.filtros')
            </div>

            <!-- Tabla de Accesos -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                @include('accesos_salud.partials.tabla')
            </div>
        </div>
    </div>
</x-app-layout>
