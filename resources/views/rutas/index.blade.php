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
            @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
            <section class="bg-white rounded-lg shadow p-4 mb-4 uppercase" aria-label="Asistente de rutas">
                <button type="button" data-leer-rutas style="background-color:#2d6a27;color:white" class="bg-green-800 text-white font-bold rounded px-5 py-3">ESCUCHAR RUTAS DE ESTA PÁGINA</button>
                <button type="button" data-detener-asistente class="border border-gray-700 rounded px-5 py-3">DETENER AUDIO</button>
                <p id="estado-asistente-rutas" role="status" class="mt-3">ELIGE INICIAR ASISTENTE PARA ESCUCHAR LA RECOMENDACIÓN Y LOS OBSTÁCULOS.</p>
            </section>
            @endif
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('rutas.eventos') }}" class="text-xs font-bold uppercase text-emerald-800 hover:underline flex items-center gap-1.5">
                    <i class="fas fa-satellite-dish"></i> VER EVENTOS Y SOS DEL BASTÓN
                </a>
            </div>
            @include('rutas.partials.tabla')
        </div>
    </div>
    @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<script src="{{ asset('js/asistente-rutas.js') }}" defer></script>
@endif
</x-app-layout>
