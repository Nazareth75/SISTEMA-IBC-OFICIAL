@php
    $persona = $persona ?? null;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- DATOS PERSONALES --}}
    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos personales</h3>

        <div class="space-y-4">
            <div>
                <label for="nom_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre *
                </label>
                <input
                    type="text"
                    name="nom_pnv"
                    id="nom_pnv"
                    value="{{ old('nom_pnv', $persona?->nom_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                @error('nom_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ape_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Apellido *
                </label>
                <input
                    type="text"
                    name="ape_pnv"
                    id="ape_pnv"
                    value="{{ old('ape_pnv', $persona?->ape_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                @error('ape_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cii_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                        CI *
                    </label>
                    <input
                        type="text"
                        name="cii_pnv"
                        id="cii_pnv"
                        value="{{ old('cii_pnv', $persona?->cii_pnv) }}"
                        title="INGRESE SOLO NÚMEROS SIN EXTENSIÓN"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">INGRESE SOLO NÚMEROS SIN EXTENSIÓN</p>
                    @error('cii_pnv')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ext_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                        Extensión
                    </label>
                    <select
                        name="ext_pnv"
                        id="ext_pnv"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">SELECCIONE</option>
                        @foreach ($extensiones as $extension)
                            <option value="{{ $extension }}" @selected(old('ext_pnv', $persona?->ext_pnv) === $extension)>
                                {{ $extension }}
                            </option>
                        @endforeach
                    </select>
                    @error('ext_pnv')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fen_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de nacimiento *
                    </label>
                    <input
                        type="date"
                        name="fen_pnv"
                        id="fen_pnv"
                        value="{{ old('fen_pnv', optional($persona?->fen_pnv)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                    @error('fen_pnv')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gen_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                        Género *
                    </label>
                    <select
                        name="gen_pnv"
                        id="gen_pnv"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="">SELECCIONE</option>
                        @foreach ($generos as $genero)
                            <option value="{{ $genero }}" @selected(old('gen_pnv', $persona?->gen_pnv) === $genero)>
                                {{ $genero }}
                            </option>
                        @endforeach
                    </select>
                    @error('gen_pnv')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- CONTACTO --}}
    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Contacto</h3>

        <div class="space-y-4">
            <div>
                <label for="tel_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Teléfono
                </label>
                <input
                    type="text"
                    name="tel_pnv"
                    id="tel_pnv"
                    value="{{ old('tel_pnv', $persona?->tel_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('tel_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cor_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Correo
                </label>
                <input
                    type="email"
                    name="cor_pnv"
                    id="cor_pnv"
                    value="{{ old('cor_pnv', $persona?->cor_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('cor_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="dir_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Dirección
                </label>
                <input
                    type="text"
                    name="dir_pnv"
                    id="dir_pnv"
                    value="{{ old('dir_pnv', $persona?->dir_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('dir_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ciu_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Ciudad *
                </label>
                <select
                    name="ciu_pnv"
                    id="ciu_pnv"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="">SELECCIONE</option>
                    @foreach ($ciudades as $ciudad)
                        <option value="{{ $ciudad }}" @selected(old('ciu_pnv', $persona?->ciu_pnv) === $ciudad)>
                            {{ $ciudad }}
                        </option>
                    @endforeach
                </select>
                @error('ciu_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- DISCAPACIDAD --}}
    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Discapacidad</h3>

        <div class="space-y-4">
            <div>
                <label for="gdi_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Grado de discapacidad *
                </label>
                <select
                    name="gdi_pnv"
                    id="gdi_pnv"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="">SELECCIONE</option>
                    @foreach ($grados as $grado)
                        <option value="{{ $grado }}" @selected(old('gdi_pnv', $persona?->gdi_pnv) === $grado)>
                            {{ $grado }}
                        </option>
                    @endforeach
                </select>
                @error('gdi_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cau_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Causa
                </label>
                <input
                    type="text"
                    name="cau_pnv"
                    id="cau_pnv"
                    value="{{ old('cau_pnv', $persona?->cau_pnv) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('cau_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="aut_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Nivel de autonomía *
                </label>
                <select
                    name="aut_pnv"
                    id="aut_pnv"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="">SELECCIONE</option>
                    @foreach ($autonomias as $autonomia)
                        <option value="{{ $autonomia }}" @selected(old('aut_pnv', $persona?->aut_pnv) === $autonomia)>
                            {{ $autonomia }}
                        </option>
                    @endforeach
                </select>
                @error('aut_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if(isset($estados))
                <div>
                    <label for="est_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado
                    </label>
                    <select
                        name="est_pnv"
                        id="est_pnv"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        @foreach ($estados as $estado)
                            <option value="{{ $estado }}" @selected(old('est_pnv', $persona?->est_pnv ?? 'PENDIENTE') === $estado)>
                                {{ $estado }}
                            </option>
                        @endforeach
                    </select>
                    @error('est_pnv')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </div>

    {{-- FOTO Y OBSERVACIONES --}}
    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto y observaciones</h3>

        <div class="space-y-4">
            <div>
                <label for="fot_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Foto
                </label>
                <input
                    type="file"
                    name="fot_pnv"
                    id="fot_pnv"
                    accept=".jpg,.jpeg,.png"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                <p class="mt-1 text-xs text-gray-500">Máximo 2MB. Solo JPG o PNG.</p>
                @error('fot_pnv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if($persona?->fot_pnv)
                    <div class="mt-3">
                        <img
                            src="{{ asset('storage/' . $persona->fot_pnv) }}"
                            alt="Foto de la persona"
                            class="h-28 w-28 object-cover rounded-lg border border-gray-200"
                        >
                    </div>
                @endif
            </div>

            <div>
                <label for="obs_pnv" class="block text-sm font-medium text-gray-700 mb-1">
                    Observaciones
                </label>
                <textarea
                    name="obs_pnv"
                    id="obs_pnv"
                    rows="5"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('obs_pnv', $persona?->obs_pnv) }}</textarea>
                @error('obs_pnv')
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
        href="{{ route('personas-no-videntes.index') }}"
        class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition"
    >
        Volver
    </a>
</div>