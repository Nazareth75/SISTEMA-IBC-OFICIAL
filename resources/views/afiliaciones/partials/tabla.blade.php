<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Persona</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">CI</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha afiliación</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Estado</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
            @forelse ($afiliaciones as $afiliacion)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ $afiliacion->ide_afi }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ $afiliacion->persona?->nombre_completo }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ $afiliacion->persona?->cii_pnv }}{{ $afiliacion->persona?->ext_pnv ? ' ' . $afiliacion->persona?->ext_pnv : '' }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ $afiliacion->tipoAfiliado?->nom_taf }}
                    </td>

                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($afiliacion->fia_afi)->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                            $estadoClases = match($afiliacion->est_afi) {
                                'ACTIVA' => 'bg-green-100 text-green-800',
                                'INACTIVA' => 'bg-gray-100 text-gray-800',
                                'SUSPENDIDA' => 'bg-red-100 text-red-800',
                                'VENCIDA' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-slate-100 text-slate-800',
                            };
                        @endphp

                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estadoClases }}">
                            {{ $afiliacion->est_afi }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('afiliaciones.show', $afiliacion) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-sky-100 text-sky-700 rounded-lg text-xs font-semibold hover:bg-sky-200 transition">
                                Ver
                            </a>

                            @if(auth()->user()?->puedeEditarAfiliacion())
                                <a href="{{ route('afiliaciones.edit', $afiliacion) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold hover:bg-amber-200 transition">
                                    Editar
                                </a>
                            @endif

                            @if(auth()->user()?->puedeEliminarAfiliacion())
                                <form action="{{ route('afiliaciones.destroy', $afiliacion) }}" method="POST" class="form-eliminar-afiliacion">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                        No se encontraron afiliaciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($afiliaciones->hasPages())
    <div class="px-4 py-4 border-t border-gray-100">
        {{ $afiliaciones->links() }}
    </div>
@endif