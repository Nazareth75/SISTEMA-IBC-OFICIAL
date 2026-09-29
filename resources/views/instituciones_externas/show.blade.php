<x-app-layout>
    @php
        $institucionVista = $institucion ?? $institucionExterna ?? null;
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">
                    Detalle de Institución Externa
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">
                    {{ $institucionVista?->nom_iex ?? 'SIN REGISTRO' }}
                </p>
            </div>

            <a href="{{ route('instituciones-externas.index') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition uppercase text-xs font-semibold tracking-widest">
                Volver al catálogo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500 uppercase text-xs font-semibold">Nombre</p>
                        <p class="mt-1 font-medium text-gray-900 uppercase">{{ $institucionVista?->nom_iex ?? 'SIN REGISTRO' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 uppercase text-xs font-semibold">Tipo</p>
                        <p class="mt-1 font-medium text-gray-900 uppercase">{{ $institucionVista?->getNombreTipo() ?? 'SIN REGISTRO' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 uppercase text-xs font-semibold">Ciudad</p>
                        <p class="mt-1 font-medium text-gray-900 uppercase">{{ $institucionVista?->getCiudadFormato() ?? 'SIN REGISTRO' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 uppercase text-xs font-semibold">Teléfono</p>
                        <p class="mt-1 font-medium text-gray-900 uppercase">{{ $institucionVista?->getTeléfonoFormato() ?? 'SIN REGISTRO' }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-gray-500 uppercase text-xs font-semibold">Correo</p>
                        <p class="mt-1 font-medium text-gray-900 uppercase">{{ $institucionVista?->getEmailFormato() ?? 'SIN REGISTRO' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 uppercase text-xs font-semibold">Estado</p>
                        <p class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $institucionVista?->act_iex ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} uppercase">
                            {{ $institucionVista?->act_iex ? 'ACTIVA' : 'INACTIVA' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-8">
                    <a href="{{ route('instituciones-externas.edit', $institucionVista) }}"
                       class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition uppercase text-xs font-semibold tracking-widest">
                        Editar
                    </a>

                    <a href="{{ route('instituciones-externas.index') }}"
                       class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition uppercase text-xs font-semibold tracking-widest">
                        Regresar
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>