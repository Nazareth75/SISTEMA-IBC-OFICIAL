@php
    $afiliacion = $afiliacion ?? null;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos de afiliación</h3>

        <div class="space-y-4">
            @if(!$afiliacion)
                <div>
                    <label for="buscar_persona" class="block text-sm font-medium text-gray-700 mb-1">
                        Buscar persona no vidente *
                    </label>
                    <input
                        type="text"
                        id="buscar_persona"
                        placeholder="Escriba nombre o CI..."
                        class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                        autocomplete="off"
                    >
                    
                    <!-- Contenedor de resultados en formato fichas -->
                    <div id="resultados_personas" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Las fichas se cargarán aquí con JavaScript -->
                    </div>

                    <!-- Input oculto para almacenar el valor seleccionado -->
                    <input
                        type="hidden"
                        name="cod_personas_no_videntes_afi"
                        id="cod_personas_no_videntes_afi"
                        value="{{ old('cod_personas_no_videntes_afi', '') }}"
                        required
                    >

                    <!-- Mostrar persona seleccionada -->
                    <div id="persona_seleccionada" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg hidden">
                        <p class="text-sm text-green-700">
                            <span class="font-semibold">✓ Persona seleccionada:</span>
                            <span id="nombre_persona_sel"></span>
                        </p>
                    </div>

                    @error('cod_personas_no_videntes_afi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        Solo se muestran personas ACTIVAS SIN AFILIACIÓN VIGENTE. Máximo 5 resultados.
                    </p>
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Persona no vidente
                    </label>
                    <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800">
                        {{ $afiliacion->persona?->nombre_completo }} — CI: {{ $afiliacion->persona?->cii_pnv }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        La persona no puede cambiarse en edición.
                    </p>
                </div>
            @endif

            <div>
                <label for="cod_tipos_afiliado_afi" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de afiliado *
                </label>
                <select
                    name="cod_tipos_afiliado_afi"
                    id="cod_tipos_afiliado_afi"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="">SELECCIONE</option>
                    @foreach ($tiposAfiliado as $tipoAfiliado)
                        <option value="{{ $tipoAfiliado->cod_tipos_afiliado }}"
                            @selected(old('cod_tipos_afiliado_afi', $afiliacion?->cod_tipos_afiliado_afi) == $tipoAfiliado->cod_tipos_afiliado)>
                            {{ $tipoAfiliado->nom_taf }}
                        </option>
                    @endforeach
                </select>
                @error('cod_tipos_afiliado_afi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fia_afi" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de afiliación *
                    </label>
                    <input
                        type="date"
                        name="fia_afi"
                        id="fia_afi"
                        value="{{ old('fia_afi', optional($afiliacion?->fia_afi)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                    @error('fia_afi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fve_afi" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de vencimiento
                    </label>
                    <input
                        type="date"
                        name="fve_afi"
                        id="fve_afi"
                        value="{{ old('fve_afi', optional($afiliacion?->fve_afi)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('fve_afi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($afiliacion && isset($estados))
                <div>
                    <label for="est_afi" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado *
                    </label>
                    <select
                        name="est_afi"
                        id="est_afi"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        @foreach ($estados as $estado)
                            <option value="{{ $estado }}" @selected(old('est_afi', $afiliacion->est_afi) === $estado)>
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                    @error('est_afi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Observaciones</h3>

        <div class="space-y-4">
            @if(!$afiliacion)
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Antes de registrar, verifique que la persona no tenga ya una afiliación ACTIVA.
                </div>
            @endif

            <div>
                <label for="obs_afi" class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                </label>
                <textarea
                    name="obs_afi"
                    id="obs_afi"
                    rows="8"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('obs_afi', $afiliacion?->obs_afi) }}</textarea>
                @error('obs_afi')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="mt-6 flex flex-col sm:flex-row gap-3">
    <button
        type="submit"
        class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition"
    >
        Guardar
    </button>

    <a
        href="{{ route('afiliaciones.index') }}"
        class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition"
    >
        Volver
    </a>
</div>  