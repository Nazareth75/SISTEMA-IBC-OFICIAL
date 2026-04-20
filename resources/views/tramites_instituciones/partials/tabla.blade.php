<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Afiliado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Institución</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fechas</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($tramites as $tramite)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $tramite->afiliacion->persona->nom_pnv ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $tramite->institucion->nom_iex ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $tramite->tti_tin }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @php
                            $colorMap = [
                                'PENDIENTE' => 'yellow',
                                'EN PROCESO' => 'blue',
                                'RESUELTO' => 'green',
                                'RECHAZADO' => 'red',
                                'ABANDONADO' => 'gray'
                            ];
                            $color = $colorMap[$tramite->est_tin] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $tramite->est_tin }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <div>Inicia: {{ $tramite->fei_tin->format('d/m') }}</div>
                        <div>Termina: {{ $tramite->fer_tin->format('d/m') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">
                        <a href="{{ route('tramites-instituciones.edit', $tramite) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="Editar">
                            ✏️
                        </a>
                        <form action="{{ route('tramites-instituciones.destroy', $tramite) }}" 
                              method="POST" class="inline-block"
                              onsubmit="return confirm('¿Eliminar este trámite?');">
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
                        No hay trámites registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="px-6 py-4 border-t border-gray-200">
    {{ $tramites->links() }}
</div>
