<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl">MODO NAVEGACIÓN ACCESIBLE</h2></x-slot>
    <main class="max-w-3xl mx-auto p-6 space-y-4">
        <h1 style="font-size:28px;font-weight:bold">¿A DÓNDE QUIERES IR?</h1>
        <a href="{{ route('rutas.index') }}" class="underline">MODO ADMINISTRATIVO</a>
        @if($demo)<p>DATOS SIMULADOS DE DEMOSTRACIÓN. ACCESIBILIDAD NO VALIDADA EN CAMPO.</p>@endif
        <label for="buscar-destino">BUSCAR DESTINO</label>
        <input id="buscar-destino" type="search" class="w-full border rounded p-3" placeholder="HOSPITAL O LUGAR REGISTRADO">
        <label for="destino-accesible">ELEGIR DESTINO Y PUNTO DE SALIDA</label>
        <select id="destino-accesible" class="w-full border rounded p-3">
            <option value="">SELECCIONA UN DESTINO</option>
            @foreach($rutas as $r)<option value="{{ route('navegacion.ruta',$r) }}{{ $demo?'?demostracion=1':'' }}">{{ $r->dst_rut }} · DESDE {{ $r->ori_rut }}</option>@endforeach
        </select>
        <button type="button" id="buscar-ruta-accesible" style="background:#2d6a27;color:white;padding:18px;border-radius:8px;width:100%;font-weight:bold">BUSCAR RUTA SEGURA</button>
        <section id="resultado-accesible" hidden>
            <h2 class="font-bold text-xl">RUTA RECOMENDADA ENCONTRADA</h2>
            <p id="resumen-accesible"></p>
            <p>GUÍA APROXIMADA POR UBICACIÓN. COMIENZA EN EL ORIGEN REGISTRADO; NO CALCULA UN ENLACE DESDE OTRA UBICACIÓN.</p>
            <div class="flex flex-wrap gap-3 my-3">
                <button id="iniciar-navegacion" type="button" style="background:#2d6a27;color:white;padding:18px;border-radius:8px">INICIAR NAVEGACIÓN</button>
                <button id="repetir-navegacion" type="button" class="border rounded p-4">REPETIR INDICACIÓN</button>
                <button id="detener-navegacion" type="button" class="border rounded p-4">DETENER</button>
            </div>
            <div id="mapa-navegacion" style="height:360px" aria-label="Ruta recomendada y destino"></div>
        </section>
        <p id="estado-navegacion" role="status" aria-live="polite" style="font-size:20px">ELIGE UN DESTINO.</p>
    </main>
    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa-aco.js') }}"></script>
    <script src="{{ asset('js/navegacion-accesible.js') }}"></script>
    @endpush
</x-app-layout>
