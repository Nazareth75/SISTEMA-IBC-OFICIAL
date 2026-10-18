@php $previa = $previa ?? app(\App\Services\VistaPreviaMerced::class)->obtener(); @endphp
<section class="bg-white border rounded p-4 my-4" aria-label="Mapa compartido con la app">
<h3 class="font-bold text-xl">IBC - HOSPITAL LA MERCED · MISMO MAPA QUE RECIBE ISRAEL</h3>
<p>Vista previa: 4 caminos, 41 tramos. Riesgo sin evaluar. La recomendacion actual compara distancia, no certifica seguridad.</p>
        <p class="font-bold my-3">VERDE: RECOMENDADA ACO. AZUL: ALTERNATIVAS. T: TRAMOS. MISMA GEOMETRIA Y REFUERZO QUE RECIBE EL CELULAR.</p>
        <div id="mapa-catalogo-rutas" style="height:620px"></div>
        <div id="caminos-previa"></div>
        <p>Circulos blancos: limites de tramos. Pulsa un tramo para consultar su calle. Ancho: refuerzo real de feromona ACO.</p>
<p>La feromona representa el refuerzo acumulado por las hormigas artificiales. Mayor grosor significa mayor refuerzo, no mayor peligro ni mas bastones.</p>
</section>
    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa-aco.js') }}?v=unico5"></script>
    <script>
        const mapaPrevia=L.map('mapa-catalogo-rutas').setView([-16.49,-68.13],14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OpenStreetMap contributors'}).addTo(mapaPrevia);
        window.renderizarCaminosAco(mapaPrevia,@json($previa),{alternativas:true,celular:true,panel:'caminos-previa'});
    </script>
    @endpush
