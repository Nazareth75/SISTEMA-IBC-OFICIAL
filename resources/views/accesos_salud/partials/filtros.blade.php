<form method="GET" action="{{ route('accesos-salud.index') }}" class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Buscador -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">
                Buscar
            </label>
            <input 
                type="text" 
                id="buscar"
                name="buscar" 
                value="{{ request('buscar', '') }}"
                placeholder="Afiliado o institución..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
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
                <option value="">Todos los estados</option>
                @foreach(\App\Models\AccesoSalud::ESTADOS as $estado => $label)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Espacio vacío para mantener alineación -->
        <div></div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button 
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
            >
                Filtrar
            </button>
            <a 
                href="{{ route('accesos-salud.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium"
            >
                Limpiar
            </a>
        </div>
    </div>
</form>
