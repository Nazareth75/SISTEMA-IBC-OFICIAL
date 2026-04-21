<form method="GET" action="{{ route('capacitaciones.index') }}" class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Buscador -->
        <div>
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2 uppercase">
                Buscar
            </label>
            <input
                type="text"
                id="buscar"
                name="buscar"
                value="{{ request('buscar', '') }}"
                placeholder="Nombre o código..."
                title="Buscar por nombre o código de capacitación"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Filtro por tipo -->
        <div>
            <label for="tipo_id" class="block text-sm font-medium text-gray-700 mb-2 uppercase">
                Tipo de Capacitación
            </label>
            <select
                id="tipo_id"
                name="tipo_id"
                title="Filtrar por tipo de capacitación"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->cod_tipos_capacitacion }}"
                            @selected(request('tipo_id') == $tipo->cod_tipos_capacitacion)>
                        {{ $tipo->nom_tca }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por estado -->
        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2 uppercase">
                Estado
            </label>
            <select
                id="estado"
                name="estado"
                title="Filtrar por estado de la capacitación"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todos los estados</option>
                @foreach(\App\Models\Capacitacion::ESTADOS as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(request('estado') === $clave)>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-2">
            <button
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium uppercase"
            >
                Buscar
            </button>
            <a
                href="{{ route('capacitaciones.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium uppercase"
            >
                Limpiar
            </a>
        </div>
    </div>
</form>
