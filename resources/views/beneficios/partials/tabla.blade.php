@if($beneficios->isEmpty())
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p class="mt-4 text-gray-500">No hay beneficios registrados.</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Nombre</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Tipo</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Descripción</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Asignaciones</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Estado</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($beneficios as $beneficio)
                    <tr class="hover:bg-gray-50 transition">
                        <!-- Nombre -->
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $beneficio->nom_bso }}
                        </td>

                        <!-- Tipo -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $beneficio->getNombreTipo() }}
                            </span>
                        </td>

                        <!-- Descripción -->
                        <td class="px-6 py-4 text-gray-600">
                            @if($beneficio->des_bso)
                                <span title="{{ $beneficio->des_bso }}" class="truncate block max-w-xs">
                                    {{ Str::limit($beneficio->des_bso, 50) }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Asignaciones Activas -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-800 font-semibold text-sm">
                                {{ $beneficio->contarAsignacionesActivas() }}
                            </span>
                        </td>

                        <!-- Estado (Badge) -->
                        <td class="px-6 py-4 text-center">
                            @if($beneficio->estaActivo())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-2 h-2 bg-gray-600 rounded-full mr-2"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if(auth()->user()?->esAdministrador())
                                    <!-- Botón Editar -->
                                    <a href="{{ route('beneficios.edit', $beneficio) }}"
                                       class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition"
                                       title="Editar beneficio">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Botón Activar/Desactivar -->
                                    <form action="{{ route('beneficios.toggle', $beneficio) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition"
                                            title="{{ $beneficio->estaActivo() ? 'Desactivar' : 'Activar' }} beneficio"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $beneficios->links() }}
    </div>
@endif
