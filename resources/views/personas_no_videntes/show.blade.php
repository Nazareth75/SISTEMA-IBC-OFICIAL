<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle de Persona No Vidente
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Información completa de la persona registrada.
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('personas-no-videntes.edit', $persona) }}"
                   class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 transition">
                    Editar
                </a>

                <a href="{{ route('personas-no-videntes.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

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

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-shrink-0">
                        @if($persona->fot_pnv)
                            <img
                                src="{{ asset('storage/' . $persona->fot_pnv) }}"
                                alt="Foto de {{ $persona->nombre_completo }}"
                                class="h-36 w-36 object-cover rounded-xl border border-gray-200"
                            >
                        @else
                            <div class="h-36 w-36 rounded-xl border border-dashed border-gray-300 flex items-center justify-center text-sm text-gray-400">
                                Sin foto
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $persona->nombre_completo }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Código: {{ $persona->ide_pnv }}
                                </p>
                            </div>

                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $estadoClases }}">
                                    {{ $persona->est_pnv }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">CI</p>
                                <p class="font-medium text-gray-900">
                                    {{ $persona->cii_pnv }}{{ $persona->ext_pnv ? ' ' . $persona->ext_pnv : '' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Fecha de nacimiento</p>
                                <p class="font-medium text-gray-900">
                                    {{ optional($persona->fen_pnv)->format('d/m/Y') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Género</p>
                                <p class="font-medium text-gray-900">{{ $persona->gen_pnv }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Teléfono</p>
                                <p class="font-medium text-gray-900">{{ $persona->tel_pnv ?: '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Correo</p>
                                <p class="font-medium text-gray-900">{{ $persona->cor_pnv ?: '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Ciudad</p>
                                <p class="font-medium text-gray-900">{{ $persona->ciu_pnv }}</p>
                            </div>

                            <div class="md:col-span-2 xl:col-span-3">
                                <p class="text-gray-500">Dirección</p>
                                <p class="font-medium text-gray-900">{{ $persona->dir_pnv ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos de discapacidad</h3>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-500">Grado de discapacidad</p>
                            <p class="font-medium text-gray-900">{{ $persona->gdi_pnv }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Causa</p>
                            <p class="font-medium text-gray-900">{{ $persona->cau_pnv ?: '—' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Nivel de autonomía</p>
                            <p class="font-medium text-gray-900">{{ $persona->aut_pnv }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Observaciones</p>
                            <p class="font-medium text-gray-900 whitespace-pre-line">{{ $persona->obs_pnv ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Tutor / Contactos</h3>

                        <a href="{{ route('personas-no-videntes.tutores.index', $persona) }}"
                           class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Agregar tutor
                        </a>
                    </div>

                    @if($persona->tutoresContactos->count())
                        <div class="space-y-3">
                            @foreach($persona->tutoresContactos as $tutor)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ $tutor->nombre_completo }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $tutor->par_tco }}
                                            </p>
                                        </div>

                                        @if($tutor->etl_tco)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                Tutor legal
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-gray-500">Teléfono</p>
                                            <p class="font-medium text-gray-900">{{ $tutor->tel_tco }}</p>
                                        </div>

                                        <div>
                                            <p class="text-gray-500">Correo</p>
                                            <p class="font-medium text-gray-900">{{ $tutor->cor_tco ?: '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No existen tutores o contactos registrados.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de afiliaciones</h3>

                @if($persona->afiliaciones->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha afiliación</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Vencimiento</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($persona->afiliaciones as $afiliacion)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $afiliacion->ide_afi }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ optional($afiliacion->fia_afi)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ optional($afiliacion->fve_afi)->format('d/m/Y') ?: '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $afiliacion->est_afi }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $afiliacion->obs_afi ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No existen afiliaciones registradas.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>