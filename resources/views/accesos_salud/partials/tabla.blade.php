<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Afiliado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Institución</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha de Acceso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Motivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($accesos as $acceso)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $acceso->afiliacion->persona->nom_pnv ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $acceso->institucion->nom_iex ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $acceso->fea_asa->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ Str::limit($acceso->mot_asa, 40) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $colorMap = [
                                'ACTIVO' => 'green',
                                'FINALIZADO' => 'blue',
                                'SUSPENDIDO' => 'red'
                            ];
                            $color = $colorMap[$acceso->est_asa] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $acceso->est_asa }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">
                        <a href="{{ route('accesos-salud.edit', $acceso) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="Editar">
                            ✏️
                        </a>
                        <form action="{{ route('accesos-salud.destroy', $acceso) }}" 
                              method="POST" class="inline-block"
                              onsubmit="return confirm('¿Eliminar este acceso?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No hay accesos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="px-6 py-4 border-t border-gray-200">
    {{ $accesos->links() }}
</div>
