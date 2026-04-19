<form method="GET" action="{{ route('afiliaciones.index') }}" class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-2">
            <label for="buscar" class="block text-sm font-medium text-gray-700 mb-1">
                Buscar
            </label>
            <input
                type="text"
                name="buscar"
                id="buscar"
                value="{{ $filtros['buscar'] ?? '' }}"
                placeholder="Nombre persona, CI o código afiliación"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
        </div>

        <div>
            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                Estado
            </label>
            <select
                name="estado"
                id="estado"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">TODOS</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>
                        {{ $estado }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                Tipo afiliado
            </label>
            <select
                name="tipo"
                id="tipo"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">TODOS</option>
                @foreach ($tiposAfiliado as $tipoAfiliado)
                    <option value="{{ $tipoAfiliado->cod_tipos_afiliado }}" @selected(($filtros['tipo'] ?? '') == $tipoAfiliado->cod_tipos_afiliado)>
                        {{ $tipoAfiliado->nom_taf }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-4 flex flex-col sm:flex-row gap-3">
        <button
            type="submit"
            class="inline-flex justify-center items-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-900 transition"
        >
            Filtrar
        </button>

        <a
            href="{{ route('afiliaciones.index') }}"
            class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition"
        >
            Limpiar filtros
        </a>
    </div>
</form>