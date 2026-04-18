<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre completo</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CI</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ciudad</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Grado discapacidad</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
            @forelse ($personas as $persona)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        {{ $persona->ide_pnv }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        {{ $persona->nombre_completo }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        {{ $persona->cii_pnv }}{{ $persona->ext_pnv ? ' ' . $persona->ext_pnv : '' }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        {{ $persona->ciu_pnv }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        {{ $persona->gdi_pnv }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                            $estadoClases = match($persona->est_pnv) {
                                'PENDIENTE' => 'bg-yellow-100 text-yellow-800',
                                'ACTIVO' => 'bg-green-100 text-green-800',
                                'INACTIVO' => 'bg-gray-100 text-gray-800',
                                'SUSPENDIDO' => 'bg-red-100 text-red-800',
                                'EGRESADO' => 'bg-blue-100 text-blue-800',
                                default => 'bg-slate-100 text-slate-800',
                            };
                        @endphp

                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estadoClases }}">
                            {{ $persona->est_pnv }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('personas-no-videntes.show', $persona) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-sky-100 text-sky-700 rounded-lg text-xs font-semibold hover:bg-sky-200 transition">
                                Ver
                            </a>

                            <a href="{{ route('personas-no-videntes.edit', $persona) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold hover:bg-amber-200 transition">
                                Editar
                            </a>

                            <form action="{{ route('personas-no-videntes.destroy', $persona) }}" method="POST"
                                  onsubmit="return confirm('¿Está seguro de eliminar esta persona no vidente?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                        No se encontraron registros de personas no videntes.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($personas->hasPages())
    <div class="px-4 py-4 border-t border-gray-100">
        {{ $personas->links() }}
    </div>
@endif