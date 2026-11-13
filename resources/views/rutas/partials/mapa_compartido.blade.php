@php
    $datosMapa = $datosMapa ?? ($previa ?? app(\App\Services\VistaPreviaMerced::class)->obtener());
    $tituloMapa = $tituloMapa ?? ($datosMapa['ruta_nombre'] ?? 'IBC - HOSPITAL LA MERCED');
@endphp
<section class="bg-white border rounded p-4 my-4" aria-label="Mapa compartido con la app móvil">
    <div class="flex items-center justify-between border-b pb-2 mb-2">
        <h3 class="font-bold text-xl uppercase">{{ $tituloMapa }} · MAPA COMPARTIDO CON LA APP MÓVIL</h3>
        <span class="text-xs bg-green-100 text-green-800 font-bold px-2 py-1 rounded">SISTEMA MULTI-AFILIADO IBC</span>
    </div>
    <p class="text-sm text-gray-700">Vista compartida: {{ $datosMapa['cantidad_alternativas'] ?? count($datosMapa['caminos_candidatos'] ?? []) }} caminos, {{ $datosMapa['cantidad_tramos'] ?? 'red' }} tramos. Misma geometría y refuerzo que reciben las personas afiliadas en la aplicación móvil.</p>
    <p class="font-bold my-3 text-sm">VERDE: RECOMENDADA ACO. AZUL: ALTERNATIVAS. T: TRAMOS. MISMA GEOMETRÍA Y REFUERZO QUE RECIBE EL CELULAR.</p>
    <div id="mapa-catalogo-rutas" style="height:620px"></div>
    <div id="caminos-previa"></div>
    <p class="text-xs text-gray-500 mt-2">Círculos blancos: límites de tramos. Pulsa un tramo para consultar su calle. Grosor: refuerzo real de feromona ACO.</p>
    <p class="text-xs text-gray-500">La feromona representa el refuerzo acumulado por las hormigas artificiales. Mayor grosor significa mayor refuerzo relativo en la metaheurística.</p>
</section>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/mapa-aco.js') }}?v=unico6"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('mapa-catalogo-rutas');
        if (!el || window._mapaCatalogoRutasIniciado) return;
        window._mapaCatalogoRutasIniciado = true;
        const mapaPrevia = L.map('mapa-catalogo-rutas').setView([-16.49, -68.13], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap contributors' }).addTo(mapaPrevia);
        window.renderizarCaminosAco(mapaPrevia, @json($datosMapa), { alternativas: true, celular: true, panel: 'caminos-previa' });
    });
</script>
@endpush
