<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ciudad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Contacto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trámites</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($instituciones as $institucion)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $institucion->nom_iex }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $institucion->tip_iex }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $institucion->ciu_iex ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <div>📞 {{ $institucion->tel_iex ?? '—' }}</div>
                        <div>📧 {{ $institucion->cor_iex ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($institucion->act_iex)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Activa
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ✕ Inactiva
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                        {{ $institucion->contarTramitesActivos() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">
                        <a href="{{ route('instituciones-externas.show', $institucion) }}"
                           class="text-blue-600 hover:text-blue-900 font-medium"
                           title="Ver detalles">
                            👁️
                        </a>
                        <a href="{{ route('instituciones-externas.edit', $institucion) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="Editar">
                            ✏️
                        </a>
                        @if($institucion->act_iex)
                            <form action="{{ route('instituciones-externas.toggle', $institucion) }}" 
                                  method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-yellow-600 hover:text-yellow-900 font-medium"
                                        title="Desactivar"
                                        onclick="return confirm('¿Desactivar esta institución?')">
                                    🔒
                                </button>
                            </form>
                        @else
                            <form action="{{ route('instituciones-externas.toggle', $institucion) }}" 
                                  method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-green-600 hover:text-green-900 font-medium"
                                        title="Activar"
                                        onclick="return confirm('¿Activar esta institución?')">
                                    🔓
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('instituciones-externas.destroy', $institucion) }}" 
                              method="POST" class="inline-block"
                              onsubmit="return confirm('¿Eliminar esta institución? Esta acción no se puede deshacer.');">
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
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No hay instituciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="px-6 py-4 border-t border-gray-200">
    {{ $instituciones->links() }}
</div>
