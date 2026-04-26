<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            TIPOS DE CAPACITACIÓN
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            CATÁLOGO DE TIPOS DE CAPACITACIÓN
                        </h3>
                        <p class="text-sm text-gray-600">
                            Administra los tipos disponibles para registrar capacitaciones.
                        </p>
                    </div>

                    <a href="{{ route('tipos-capacitacion.create') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold">
                        NUEVO TIPO
                    </a>
                </div>

                <div class="p-6">
                    <form method="GET" action="{{ route('tipos-capacitacion.index') }}" class="mb-4">
                        <div class="flex gap-2">
                            <input type="text"
                                   name="buscar"
                                   value="{{ request('buscar') }}"
                                   placeholder="BUSCAR POR NOMBRE..."
                                   class="w-full border-gray-300 rounded-lg shadow-sm">

                            <button type="submit"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                                BUSCAR
                            </button>

                            <a href="{{ route('tipos-capacitacion.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                                LIMPIAR
                            </a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descripción</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse($tipos as $tipo)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $tipo->nom_tca }}
                                        </td>

                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $tipo->des_tca ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            @if($tipo->act_tca)
                                                <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                    ACTIVO
                                                </span>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                                    INACTIVO
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-sm text-center space-x-2">
                                            <a href="{{ route('tipos-capacitacion.edit', $tipo) }}"
                                               class="text-blue-600 hover:text-blue-900 font-medium">
                                                EDITAR
                                            </a>

                                            <form action="{{ route('tipos-capacitacion.toggle', $tipo) }}"
                                                  method="POST"
                                                  class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="text-yellow-600 hover:text-yellow-900 font-medium">
                                                    {{ $tipo->act_tca ? 'DESACTIVAR' : 'ACTIVAR' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('tipos-capacitacion.destroy', $tipo) }}"
                                                  method="POST"
                                                  class="inline-block"
                                                  onsubmit="return confirm('¿ELIMINAR ESTE TIPO DE CAPACITACIÓN?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 font-medium">
                                                    ELIMINAR
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            NO HAY TIPOS DE CAPACITACIÓN REGISTRADOS.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $tipos->links() }}
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-gray-600 hover:text-gray-900">
                    ← VOLVER AL DASHBOARD
                </a>
            </div>
        </div>
    </div>
</x-app-layout>