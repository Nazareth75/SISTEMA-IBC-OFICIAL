<form method="GET" action="{{ route('rutas.index') }}" class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">BUSCAR</label>
            <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                placeholder="CÓDIGO, NOMBRE, ORIGEN, DESTINO..."
                title="BUSCAR POR CÓDIGO, NOMBRE, ORIGEN O DESTINO"
                class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">ESTADO</label>
            <select name="estado" title="FILTRAR POR ESTADO"
                class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">
                <option value="">TODOS LOS ESTADOS</option>
                @foreach($estados as $est)
                    <option value="{{ $est }}" @selected(($filtros['estado'] ?? '') === $est)>{{ $est }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 text-white text-sm font-bold py-2 px-4 rounded uppercase"
                style="background-color:#2d6a27">
                <i class="fas fa-search mr-1"></i> BUSCAR
            </button>
            @if(($filtros['buscar'] ?? '') || ($filtros['estado'] ?? ''))
                <a href="{{ route('rutas.index') }}"
                    class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold py-2 px-4 rounded uppercase">
                    <i class="fas fa-times mr-1"></i> LIMPIAR
                </a>
            @endif
        </div>
    </div>
</form>
