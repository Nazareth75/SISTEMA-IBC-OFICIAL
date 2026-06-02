<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Tutores / Contactos
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Persona: {{ $persona->nombre_completo }} ({{ $persona->ide_pnv }})
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('personas-no-videntes.tutores.create', $persona) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Agregar tutor
                </a>

                <a href="{{ route('personas-no-videntes.show', $persona) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre completo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Parentesco</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Correo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tutor legal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($persona->tutoresContactos as $tutor)
                                <tr class="{{ $tutor->fbj_tco ? 'bg-gray-50 opacity-70' : 'hover:bg-gray-50' }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $tutor->nombre_completo }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $tutor->par_tco }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $tutor->tel_tco }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $tutor->cor_tco ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($tutor->etl_tco && !$tutor->fbj_tco)
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 w-12">
                                                    Sí
                                                </span>
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if($tutor->doc_ci_tco)
                                                        <a href="{{ asset('storage/' . $tutor->doc_ci_tco) }}" target="_blank" 
                                                           title="Ver Cédula de Identidad de Respaldo"
                                                           class="text-gray-500 hover:text-indigo-600 transition flex items-center">
                                                            <span class="material-symbols-outlined text-[18px]">badge</span>
                                                        </a>
                                                    @else
                                                        <span class="text-gray-300 cursor-not-allowed" title="Sin Cédula de Identidad cargada">
                                                            <span class="material-symbols-outlined text-[18px]">badge</span>
                                                        </span>
                                                    @endif

                                                    @if($tutor->doc_legal_tco)
                                                        <a href="{{ asset('storage/' . $tutor->doc_legal_tco) }}" target="_blank" 
                                                           title="Ver Documento de Tutoría Legal"
                                                           class="text-gray-500 hover:text-indigo-600 transition flex items-center">
                                                            <span class="material-symbols-outlined text-[18px]">gavel</span>
                                                        </a>
                                                    @else
                                                        <span class="text-gray-300 cursor-not-allowed" title="Sin Papel Legal cargado">
                                                            <span class="material-symbols-outlined text-[18px]">gavel</span>
                                                        </span>
                                                    @endif

                                                    <a href="{{ route('personas-no-videntes.tutores.documento-base', $persona) }}?tutor_id={{ $tutor->getKey() }}" 
                                                       target="_blank"
                                                       title="Imprimir/Descargar Documento Base"
                                                       class="text-gray-500 hover:text-indigo-600 transition flex items-center">
                                                        <span class="material-symbols-outlined text-[18px]">print</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-500">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($tutor->fbj_tco)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                Baja lógica
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            @if(!$tutor->fbj_tco)
                                                <a href="{{ route('personas-no-videntes.tutores.edit', [$persona, $tutor]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold hover:bg-amber-200 transition">
                                                    Editar
                                                </a>

                                                <form action="{{ route('personas-no-videntes.tutores.destroy', [$persona, $tutor]) }}" method="POST" class="form-eliminar-tutor">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400">Sin acciones</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No existen tutores o contactos registrados para esta persona.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>