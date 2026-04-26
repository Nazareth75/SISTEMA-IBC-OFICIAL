<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-route mr-2" style="color:#2d6a27"></i> RUTAS SEGURAS
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">GESTIÓN DE RUTAS URBANAS SEGURAS PARA AFILIADOS</p>
            </div>
            @if(Auth::user()->puedeCrearRuta())
                <a href="{{ route('rutas.create') }}"
                    class="inline-flex items-center gap-2 text-white text-sm font-bold px-4 py-2 rounded uppercase shadow"
                    style="background-color:#2d6a27">
                    <i class="fas fa-plus"></i> NUEVA RUTA
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('rutas.partials.filtros')
            @include('rutas.partials.tabla')
        </div>
    </div>
</x-app-layout>
