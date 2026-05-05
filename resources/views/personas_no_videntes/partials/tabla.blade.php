<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-[#f3f3f6] border-b-2 border-gray-200">
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">Código</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">Nombre completo</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">CI</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">Ciudad</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">Grado discapacidad</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-xs font-black text-gray-600 uppercase tracking-wider text-center">Acciones</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 bg-white">
            @forelse ($personas as $persona)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-5 text-sm font-bold text-[#006837] whitespace-nowrap">
                        {{ $persona->ide_pnv }}
                    </td>

                    <td class="px-6 py-5 text-sm font-bold text-gray-900 whitespace-nowrap">
                        {{ $persona->nombre_completo }}
                    </td>

                    <td class="px-6 py-5 text-sm text-gray-600 whitespace-nowrap">
                        {{ $persona->cii_pnv }}{{ $persona->ext_pnv ? ' ' . $persona->ext_pnv : '' }}
                    </td>

                    <td class="px-6 py-5 text-sm text-gray-600 whitespace-nowrap">
                        {{ $persona->ciu_pnv }}
                    </td>

                    <td class="px-6 py-5 text-sm whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-700">
                            {{ $persona->gdi_pnv }}
                        </span>
                    </td>

                    <td class="px-6 py-5 whitespace-nowrap">
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

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase {{ $estadoClases }}">
                            {{ $persona->est_pnv }}
                        </span>
                    </td>

                    <td class="px-6 py-5 text-center whitespace-nowrap">
                        <div class="flex justify-center gap-2">

                            <a href="{{ route('personas-no-videntes.show', $persona) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl text-[#006837] hover:bg-green-50 transition"
                               title="Ver detalle">
                                <span class="material-symbols-outlined text-xl">visibility</span>
                            </a>

                            @if(auth()->user()?->puedeEditarPersonaNoVidente())
                                <a href="{{ route('personas-no-videntes.edit', $persona) }}"
                                   class="w-9 h-9 flex items-center justify-center rounded-xl text-[#964900] hover:bg-orange-50 transition"
                                   title="Editar">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </a>
                            @endif

                            @if(auth()->user()?->puedeEliminarPersonaNoVidente())
                                <form
                                    action="{{ route('personas-no-videntes.destroy', $persona) }}"
                                    method="POST"
                                    data-confirm-delete
                                    data-confirm-message="¿Deseas eliminar el registro de {{ $persona->nombre_completo }}?"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl text-red-600 hover:bg-red-50 transition"
                                            title="Eliminar">
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                        No se encontraron registros de personas no videntes.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($personas->hasPages())
    <div class="px-6 py-4 bg-[#f3f3f6] border-t border-gray-200">
        {{ $personas->links() }}
    </div>
@endif