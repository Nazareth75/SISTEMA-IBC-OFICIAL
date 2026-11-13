<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl uppercase">RED PEATONAL ASISTIDA: IBC - HOSPITAL LA MERCED</h2>
    </x-slot>
    <div class="max-w-6xl mx-auto p-6 space-y-4">
        <div class="bg-emerald-50 border border-emerald-300 rounded p-4 text-sm text-emerald-900" role="note">
            <strong>RED PEATONAL ASISTIDA:</strong> {{ $previa['advertencia'] ?? 'Evaluación de rutas y caminos alternativos para personas afiliadas al IBC.' }}
        </div>
        <div class="text-sm text-gray-700">
            Distancia del recorrido recomendado: <strong>{{ number_format($previa['distancia_mas_corta'] ?? $previa['distancia_total'], 1) }} m</strong>.
            {{ $previa['cantidad_alternativas'] }} caminos completos, {{ $previa['cantidad_tramos'] }} tramos.
        </div>
        @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
            <div class="flex gap-2">
                <button type="button" id="leer-previa" class="border border-green-800 bg-green-800 text-white font-bold px-4 py-2 rounded uppercase text-sm">ESCUCHAR RUTA</button>
                <button type="button" id="parar-previa" class="border border-gray-600 bg-gray-100 text-gray-800 font-bold px-4 py-2 rounded uppercase text-sm">DETENER AUDIO</button>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded',()=>{
                document.getElementById('leer-previa').onclick=()=>{speechSynthesis.cancel();const voz=new SpeechSynthesisUtterance(@json($previa['resumen_audio']));voz.lang='es-BO';speechSynthesis.speak(voz);};
                document.getElementById('parar-previa').onclick=()=>speechSynthesis.cancel();
            });
            </script>
        @endif
        @include('rutas.partials.mapa_compartido', [
            'datosMapa' => $previa,
            'tituloMapa' => 'IBC - HOSPITAL LA MERCED · RED PEATONAL ASISTIDA'
        ])
    </div>
</x-app-layout>
