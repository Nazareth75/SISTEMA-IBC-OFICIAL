<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Registrar Nueva Institución Externa
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Completa el formulario para crear una nueva institución.
                </p>
            </div>
            <a href="{{ route('instituciones-externas.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                Volver al Catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('instituciones-externas.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Nombre de la Institución -->
                        <div>
                            <label for="nom_iex" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Nombre de la Institución
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nom_iex"
                                name="nom_iex" 
                                value="{{ old('nom_iex') }}"
                                placeholder="Ej: Hospital General Central"
                                class="w-full px-4 py-2 border @error('nom_iex') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            @error('nom_iex')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo de Institución -->
                        <div>
                            <label for="tip_iex" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Tipo de Institución
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <select 
                                id="tip_iex"
                                name="tip_iex"
                                class="w-full px-4 py-2 border @error('tip_iex') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Selecciona un tipo...</option>
                                @foreach(\App\Models\InstitucionExterna::TIPOS as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tip_iex') === $tipo)>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tip_iex')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div>
                            <label for="ciu_iex" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Ciudad
                            </label>
                            <input 
                                type="text" 
                                id="ciu_iex"
                                name="ciu_iex" 
                                value="{{ old('ciu_iex') }}"
                                placeholder="Ej: La Paz"
                                class="w-full px-4 py-2 border @error('ciu_iex') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('ciu_iex')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="tel_iex" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input 
                                type="text" 
                                id="tel_iex"
                                name="tel_iex" 
                                value="{{ old('tel_iex') }}"
                                placeholder="Ej: +591 2 1234567"
                                class="w-full px-4 py-2 border @error('tel_iex') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('tel_iex')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="cor_iex" class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico
                            </label>
                            <input 
                                type="email" 
                                id="cor_iex"
                                name="cor_iex" 
                                value="{{ old('cor_iex') }}"
                                placeholder="Ej: contacto@institucion.com"
                                class="w-full px-4 py-2 border @error('cor_iex') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            @error('cor_iex')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4 mt-8">
                        <button 
                            type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Registrar Institución
                        </button>
                        <a 
                            href="{{ route('instituciones-externas.index') }}"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-center"
                        >
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
