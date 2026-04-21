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
                @php
                    $colorMap = [
                        'ACTIVO'     => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                        'FINALIZADO' => ['bg' => 'bg-blue-100',  'text' => 'text-blue-800'],
                        'SUSPENDIDO' => ['bg' => 'bg-red-100',   'text' => 'text-red-800'],
                    ];
                    $colores = $colorMap[$acceso->est_asa] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $acceso->afiliacion->persona->nom_pnv ?? 'N/A' }}
                        <div class="text-xs text-gray-400">
                            CI: {{ $acceso->afiliacion->persona->cii_pnv ?? '—' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $acceso->institucion->nom_iex ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $acceso->fea_asa ? $acceso->fea_asa->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                        {{ Str::limit($acceso->mot_asa ?? '—', 40) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $colores['bg'] }} {{ $colores['text'] }}">
                            {{ $acceso->est_asa }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">

                        {{-- EDITAR --}}
                        <a href="{{ route('accesos-salud.edit', $acceso) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="EDITAR ACCESO">
                            ✏️
                        </a>

                        {{-- ELIMINAR --}}
                        <form id="form-eliminar-acceso-{{ $acceso->getKey() }}"
                              action="{{ route('accesos-salud.destroy', $acceso) }}"
                              method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="ELIMINAR ACCESO"
                                    onclick="confirmarEliminarAcceso(
                                        {{ $acceso->getKey() }},
                                        '{{ addslashes($acceso->afiliacion->persona->nom_pnv ?? '') }}'
                                    )">
                                🗑️
                            </button>
                        </form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-4xl">🏥</span>
                            <span class="font-medium">NO HAY ACCESOS REGISTRADOS</span>
                            <span class="text-xs">USE EL BOTÓN "NUEVO ACCESO" PARA AGREGAR</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="px-6 py-4 border-t border-gray-200">
    {{ $accesos->links() }}
</div>

{{-- ============================================================ --}}
{{-- SWEETALERT2 — ACCESOS SALUD                                  --}}
{{-- ============================================================ --}}
<script>
    /**
     * Confirmar eliminar acceso
     */
    function confirmarEliminarAcceso(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR ACCESO A SALUD?',
            html: `
                <span class="font-semibold">${nombre}</span><br>
                <small class="text-red-500 font-medium">ESTA ACCIÓN NO SE PUEDE DESHACER.</small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'SÍ, ELIMINAR',
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`form-eliminar-acceso-${id}`).submit();
            }
        });
    }

    {{-- Alertas flash --}}
    @if(session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
    @endif

    @if(session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    @endif
</script>