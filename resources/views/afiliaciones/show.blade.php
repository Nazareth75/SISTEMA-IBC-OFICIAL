<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle de Afiliación
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Información completa de la afiliación registrada.
                </p>
            </div>

            <div class="flex gap-2">
                @if(auth()->user()?->puedeEditarAfiliacion())
                    <a href="{{ route('afiliaciones.edit', $afiliacion) }}"
                       class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 transition">
                        Editar
                    </a>
                @endif

                <a href="{{ route('afiliaciones.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $estadoClases = match($afiliacion->est_afi) {
            'ACTIVA' => 'bg-green-100 text-green-800',
            'INACTIVA' => 'bg-gray-100 text-gray-800',
            'SUSPENDIDA' => 'bg-red-100 text-red-800',
            'VENCIDA' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-slate-100 text-slate-800',
        };
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $afiliacion->ide_afi }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Afiliación registrada en el sistema
                        </p>
                    </div>

                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $estadoClases }}">
                            {{ $afiliacion->est_afi }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Tipo de afiliado</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->tipoAfiliado?->nom_taf ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Fecha de afiliación</p>
                        <p class="font-medium text-gray-900">{{ optional($afiliacion->fia_afi)->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Fecha de vencimiento</p>
                        <p class="font-medium text-gray-900">{{ optional($afiliacion->fve_afi)->format('d/m/Y') ?: '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Registrado por</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->usuarioRegistro?->nombre_completo ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Fecha de registro</p>
                        <p class="font-medium text-gray-900">{{ optional($afiliacion->fcr_afi)->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="md:col-span-2 xl:col-span-3">
                        <p class="text-gray-500">Observaciones</p>
                        <p class="font-medium text-gray-900 whitespace-pre-line">{{ $afiliacion->obs_afi ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Persona vinculada</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Código persona</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->persona?->ide_pnv ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Nombre completo</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->persona?->nombre_completo ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">CI</p>
                        <p class="font-medium text-gray-900">
                            {{ $afiliacion->persona?->cii_pnv ?? '—' }}{{ $afiliacion->persona?->ext_pnv ? ' ' . $afiliacion->persona?->ext_pnv : '' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Estado persona</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->persona?->est_pnv ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Ciudad</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->persona?->ciu_pnv ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Teléfono</p>
                        <p class="font-medium text-gray-900">{{ $afiliacion->persona?->tel_pnv ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Servicios derivados</h3>
                <p class="text-sm text-gray-500">
                    Aquí se mostrarán beneficios, trámites y federaciones asociados a esta afiliación en los siguientes módulos.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>