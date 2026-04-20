@if($asignaciones->isEmpty())
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p class="mt-4 text-gray-500">No hay asignaciones de beneficios registradas.</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Afiliado</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Beneficio</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Tipo</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Asignación</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Vencimiento</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Estado</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($asignaciones as $asignacion)
                    <tr class="hover:bg-gray-50 transition">
                        <!-- Afiliado -->
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <div class="flex flex-col">
                                <span>{{ $asignacion->afiliacion->persona->nom_pnv }} {{ $asignacion->afiliacion->persona->ape_pnv }}</span>
                                <span class="text-xs text-gray-500">CI: {{ $asignacion->afiliacion->persona->cii_pnv }}</span>
                            </div>
                        </td>

                        <!-- Beneficio -->
                        <td class="px-6 py-4">
                            {{ $asignacion->beneficio->nom_bso }}
                        </td>

                        <!-- Tipo -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $asignacion->beneficio->getNombreTipo() }}
                            </span>
                        </td>

                        <!-- Fecha Asignación -->
                        <td class="px-6 py-4 text-center">
                            {{ $asignacion->fas_abe->format('d/m/Y') }}
                        </td>

                        <!-- Fecha Fin -->
                        <td class="px-6 py-4 text-center">
                            @if($asignacion->ffi_abe)
                                <div class="flex flex-col items-center">
                                    <span>{{ $asignacion->ffi_abe->format('d/m/Y') }}</span>
                                    @if($asignacion->estaVencida() && $asignacion->est_abe === 'ACTIVO')
                                        <span class="text-xs text-red-600 font-semibold">VENCIDA</span>
                                    @elseif($asignacion->est_abe === 'ACTIVO')
                                        <span class="text-xs text-gray-500">({{ $asignacion->getDiasRestantes() }} días)</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">Sin límite</span>
                            @endif
                        </td>

                        <!-- Estado (Badge) -->
                        <td class="px-6 py-4 text-center">
                            @php
                                $colorMap = [
                                    'ACTIVO' => 'green',
                                    'SUSPENDIDO' => 'yellow',
                                    'FINALIZADO' => 'red',
                                ];
                                $color = $colorMap[$asignacion->est_abe] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                <span class="w-2 h-2 bg-{{ $color }}-600 rounded-full mr-2"></span>
                                {{ $asignacion->getNombreEstado() }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if(auth()->user()?->esAdministrador())
                                    <!-- Botón Ver/Editar -->
                                    <a href="{{ route('asignacion-beneficios.edit', $asignacion) }}"
                                       class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition"
                                       title="Ver/Editar asignación">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('asignacion-beneficios.destroy', $asignacion) }}" method="POST" class="inline" data-confirm-delete data-confirm-message="Esta asignación será eliminada permanentemente.">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                                            title="Eliminar asignación"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
        {{ $asignaciones->links() }}
    </div>
@endif
