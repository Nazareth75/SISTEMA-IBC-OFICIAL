<form method="GET" action="{{ route('beneficios.index') }}" class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Buscador por nombre -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">
                Buscar
            </label>
            <input 
                type="text" 
                id="buscar"
                name="buscar" 
                value="{{ $filtros['buscar'] }}"
                placeholder="Nombre o descripción..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Filtro por tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Beneficio
            </label>
            <select 
                id="tipo"
                name="tipo"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos los tipos</option>
                @foreach($tipos as $key => $label)
                    <option value="{{ $key }}" @selected($filtros['tipo'] === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                Estado
            </label>
            <select 
                id="estado"
                name="estado"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                @foreach($estadosFiltro as $key => $label)
                    <option value="{{ $key }}" @selected($filtros['estado'] === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button 
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
            >
                Filtrar
            </button>
            <a 
                href="{{ route('beneficios.index') }}"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-center"
            >
                Limpiar
            </a>
        </div>
    </div>
</form>
