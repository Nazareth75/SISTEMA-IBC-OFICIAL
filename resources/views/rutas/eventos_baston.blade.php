<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl uppercase">EVENTOS DEL BASTÓN</h2></x-slot>
    <div class="max-w-6xl mx-auto p-6 space-y-4 uppercase">
        <a href="{{ route('rutas.index') }}" class="underline">VOLVER A RUTAS</a>
        <p>LOS EVENTOS SON EVIDENCIA. UNA DETECCIÓN NO CREA TRAMOS NI IDENTIFICA AUTOMÁTICAMENTE EL TIPO DE OBSTÁCULO.</p>
        @forelse($eventos as $evento)
        <article class="bg-white rounded shadow p-4">
            <h3 class="font-bold">{{ $evento->tipo }} · {{ $evento->estado }}</h3>
            <p>{{ $evento->fecha_evento->format('d/m/Y H:i:s') }} · ORIGEN {{ $evento->origen }}</p>
            <p>UBICACIÓN: {{ $evento->latitud ?? 'NO DISPONIBLE' }}, {{ $evento->longitud ?? 'NO DISPONIBLE' }}</p>
            <p>{{ $evento->tramo_id ? 'ASOCIADO A TRAMO FÍSICO EXISTENTE' : 'SIN ASOCIACIÓN VÁLIDA A UN TRAMO' }}</p>
            @if($evento->tipo === 'SOS')<p class="font-bold text-red-800">SOS REGISTRADO. AVISO A FAMILIARES NO IMPLEMENTADO.</p>@endif
            @if($evento->tipo === 'DETECCION' && $evento->tramo_id && $evento->estado !== 'EVIDENCIA_REPORTADA' && Auth::user()->puedeGestionarObstaculos())
                <form action="{{ route('rutas.eventos.reportar', $evento) }}" method="POST" class="mt-3 space-x-2">
                    @csrf
                    <label>TIPO VERIFICADO <select required name="tipo_obstaculo" title="TIPO SEGÚN REVISIÓN DE LA EVIDENCIA">
                        <option value="">SELECCIONA EL TIPO</option>
                        @foreach(\App\Models\Obstaculo::TIPOS as $codigo=>$nombre)<option value="{{ $codigo }}">{{ $nombre }}</option>@endforeach
                    </select></label>
                    <label>PRIORIDAD <select required name="prioridad" title="PRIORIDAD SEGÚN EVALUACIÓN TÉCNICA">
                        <option value="">SELECCIONA LA PRIORIDAD</option>
                        <option value="1">BAJA</option><option value="2">MEDIA</option><option value="3">ALTA</option><option value="4">CRÍTICA</option>
                    </select></label>
                    <button type="submit" style="background-color:#2d6a27;color:white" class="bg-green-800 text-white p-3 rounded">REGISTRAR EVIDENCIA REVISADA</button>
                </form>
            @endif
        </article>
        @empty<p>NO HAY EVENTOS DEL BASTÓN.</p>@endforelse
        {{ $eventos->links() }}
    </div>
</x-app-layout>
