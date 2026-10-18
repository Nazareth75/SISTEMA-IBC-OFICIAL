<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl">MISMA RUTA DE LA APP: IBC - HOSPITAL LA MERCED</h2></x-slot>
    <div class="max-w-6xl mx-auto p-6">
        <p role="note">{{ $previa['advertencia'] }} ACO compara solamente distancia; no hay riesgo validado.</p>
        <p>Camino mas corto de la red comparada: {{ number_format($previa['distancia_mas_corta'], 1) }} m.
            {{ $previa['cantidad_alternativas'] }} caminos completos, {{ $previa['cantidad_tramos'] }} tramos.</p>
        @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
            <button type="button" id="leer-previa" class="border p-4">ESCUCHAR RUTA</button>
            <button type="button" id="parar-previa" class="border p-4">DETENER AUDIO</button>
            <script>
            document.addEventListener('DOMContentLoaded',()=>{
                document.getElementById('leer-previa').onclick=()=>{speechSynthesis.cancel();const voz=new SpeechSynthesisUtterance(@json($previa['resumen_audio']));voz.lang='es-BO';speechSynthesis.speak(voz);};
                document.getElementById('parar-previa').onclick=()=>speechSynthesis.cancel();
            });
            </script>
        @endif
        <p class="font-bold my-3">VERDE: RECOMENDADA ACO. AZUL: ALTERNATIVAS. T: TRAMOS. MISMA GEOMETRIA Y REFUERZO QUE RECIBE EL CELULAR.</p>
        <div id="mapa-previa" style="height:620px"></div>
        <div id="caminos-previa"></div>
        <p>Circulos blancos: limites de tramos. Pulsa un tramo para consultar su calle. Ancho: refuerzo real de feromona ACO.</p>
    </div>
    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/mapa-aco.js') }}?v=celular4"></script>
    <script>
        const mapaPrevia=L.map('mapa-previa').setView([-16.49,-68.13],14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OpenStreetMap contributors'}).addTo(mapaPrevia);
        window.renderizarCaminosAco(mapaPrevia,@json($previa),{alternativas:true,celular:true,panel:'caminos-previa'});
    </script>
    @endpush
</x-app-layout>
