@php
    $capacitacion = $capacitacion ?? null;
@endphp

<div class="space-y-6">

    {{-- NOMBRE --}}
    <div>
        <label for="nom_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Nombre de la Capacitación <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            id="nom_cap"
            name="nom_cap"
            value="{{ old('nom_cap', $capacitacion?->nom_cap) }}"
            title="INGRESE EL NOMBRE DE LA CAPACITACIÓN"
            placeholder="Ej: CURSO DE ORIENTACIÓN Y MOVILIDAD"
            class="w-full px-4 py-2 border @error('nom_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
            style="text-transform: uppercase"
            required
        />
        @error('nom_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- OBJETIVO --}}
    <div>
        <label for="obj_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Objetivo
        </label>
        <textarea
            id="obj_cap"
            name="obj_cap"
            rows="3"
            title="DESCRIBA EL OBJETIVO DE LA CAPACITACIÓN"
            placeholder="Descripción del objetivo de la capacitación..."
            class="w-full px-4 py-2 border @error('obj_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >{{ old('obj_cap', $capacitacion?->obj_cap) }}</textarea>
        @error('obj_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- TIPO DE CAPACITACIÓN --}}
    <div>
        <label for="cod_tipos_capacitacion_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Tipo de Capacitación <span class="text-red-500">*</span>
        </label>
        <select
            id="cod_tipos_capacitacion_cap"
            name="cod_tipos_capacitacion_cap"
            title="SELECCIONE EL TIPO DE CAPACITACIÓN"
            class="w-full px-4 py-2 border @error('cod_tipos_capacitacion_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >
            <option value="">— SELECCIONE UN TIPO —</option>
            @foreach($tipos as $tipo)
                <option value="{{ $tipo->cod_tipos_capacitacion }}"
                        @selected(old('cod_tipos_capacitacion_cap', $capacitacion?->cod_tipos_capacitacion_cap) == $tipo->cod_tipos_capacitacion)>
                    {{ $tipo->nom_tca }}
                </option>
            @endforeach
        </select>
        @error('cod_tipos_capacitacion_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- FECHAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- FECHA INICIO --}}
        <div>
            <label for="fei_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
                Fecha de Inicio
            </label>
            <input
                type="date"
                id="fei_cap"
                name="fei_cap"
                value="{{ old('fei_cap', $capacitacion?->fei_cap?->format('Y-m-d')) }}"
                title="FECHA EN QUE INICIA LA CAPACITACIÓN (DD/MM/AAAA)"
                class="w-full px-4 py-2 border @error('fei_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            @error('fei_cap')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- FECHA FIN --}}
        <div>
            <label for="fef_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
                Fecha de Fin
            </label>
            <input
                type="date"
                id="fef_cap"
                name="fef_cap"
                value="{{ old('fef_cap', $capacitacion?->fef_cap?->format('Y-m-d')) }}"
                title="DEBE SER IGUAL O POSTERIOR A LA FECHA DE INICIO"
                class="w-full px-4 py-2 border @error('fef_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            @error('fef_cap')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- LUGAR --}}
    <div>
        <label for="lug_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Lugar
        </label>
        <input
            type="text"
            id="lug_cap"
            name="lug_cap"
            value="{{ old('lug_cap', $capacitacion?->lug_cap) }}"
            title="INGRESE EL LUGAR DONDE SE REALIZARÁ LA CAPACITACIÓN"
            placeholder="Ej: SEDE CENTRAL IBC — SALA A"
            class="w-full px-4 py-2 border @error('lug_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            style="text-transform: uppercase"
        />
        @error('lug_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- RESPONSABLE --}}
    <div>
        <label for="cod_usuarios_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Responsable
        </label>
        <select
            id="cod_usuarios_cap"
            name="cod_usuarios_cap"
            title="SELECCIONE EL RESPONSABLE DE LA CAPACITACIÓN"
            class="w-full px-4 py-2 border @error('cod_usuarios_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="">— SIN RESPONSABLE ASIGNADO —</option>
            @foreach($usuarios as $usuario)
                <option value="{{ $usuario->cod_usuarios }}"
                        @selected(old('cod_usuarios_cap', $capacitacion?->cod_usuarios_cap) == $usuario->cod_usuarios)>
                    {{ strtoupper($usuario->nom_usu . ' ' . $usuario->ape_usu) }}
                </option>
            @endforeach
        </select>
        @error('cod_usuarios_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- ESTADO --}}
    <div>
        <label for="est_cap" class="block text-sm font-medium text-gray-700 mb-1 uppercase">
            Estado <span class="text-red-500">*</span>
        </label>
        <select
            id="est_cap"
            name="est_cap"
            title="SELECCIONE EL ESTADO ACTUAL DE LA CAPACITACIÓN"
            class="w-full px-4 py-2 border @error('est_cap') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
        >
            <option value="">— SELECCIONE UN ESTADO —</option>
            @foreach(\App\Models\Capacitacion::ESTADOS as $clave => $etiqueta)
                <option value="{{ $clave }}"
                        @selected(old('est_cap', $capacitacion?->est_cap) === $clave)>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        @error('est_cap')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>
