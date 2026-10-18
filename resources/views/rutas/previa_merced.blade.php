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
        @include('rutas.partials.mapa_compartido')
    </div>
</x-app-layout>
