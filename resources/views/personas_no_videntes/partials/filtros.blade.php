<form method="GET" action="{{ route('personas-no-videntes.index') }}">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="flex flex-col gap-2">
            <label for="buscar" class="text-sm font-bold text-gray-700">
                Buscar
            </label>

            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    search
                </span>

                <input
                    type="text"
                    name="buscar"
                    id="buscar"
                    value="{{ $filtros['buscar'] ?? '' }}"
                    placeholder="Nombre, apellido, CI o código"
                    class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#006837] focus:ring-0 transition"
                >
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <label for="estado" class="text-sm font-bold text-gray-700">
                Estado
            </label>

            <select
                name="estado"
                id="estado"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#006837] focus:ring-0 transition bg-white"
            >
                <option value="">TODOS</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>
                        {{ $estado }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-2">
            <label for="grado" class="text-sm font-bold text-gray-700">
                Grado discapacidad
            </label>

            <select
                name="grado"
                id="grado"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#006837] focus:ring-0 transition bg-white"
            >
                <option value="">TODOS</option>
                @foreach ($grados as $grado)
                    <option value="{{ $grado }}" @selected(($filtros['grado'] ?? '') === $grado)>
                        {{ $grado }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-2">
            <label for="ciudad" class="text-sm font-bold text-gray-700">
                Ciudad
            </label>

            <select
                name="ciudad"
                id="ciudad"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#006837] focus:ring-0 transition bg-white"
            >
                <option value="">TODAS</option>
                @foreach ($ciudades as $ciudad)
                    <option value="{{ $ciudad }}" @selected(($filtros['ciudad'] ?? '') === $ciudad)>
                        {{ $ciudad }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

 <div class="flex flex-col sm:flex-row sm:justify-end gap-2 mt-6">
    <a
        href="{{ route('personas-no-videntes.index') }}"
        class="inline-flex justify-center items-center gap-1 px-3 py-1.5 border border-[#006837] text-[#006837] rounded-md font-semibold text-[11px] uppercase tracking-wide hover:bg-green-50 transition"
    >
        <span class="material-symbols-outlined text-sm">restart_alt</span>
        Limpiar filtros
    </a>

    <button
        type="submit"
        class="inline-flex justify-center items-center gap-1 px-4 py-1.5 bg-[#006837] text-white rounded-md font-semibold text-[11px] uppercase tracking-wide shadow-sm hover:bg-[#004d27] transition"
    >
        <span class="material-symbols-outlined text-sm">filter_alt</span>
        Filtrar
    </button>
</div>
</form>