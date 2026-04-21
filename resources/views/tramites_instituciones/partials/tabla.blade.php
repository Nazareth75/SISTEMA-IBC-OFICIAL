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
                @php
                    $colorMap = [
                        'PENDIENTE'  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                        'EN PROCESO' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800'],
                        'RESUELTO'   => ['bg' => 'bg-green-100',  'text' => 'text-green-800'],
                        'RECHAZADO'  => ['bg' => 'bg-red-100',    'text' => 'text-red-800'],
                        'ABANDONADO' => ['bg' => 'bg-gray-100',   'text' => 'text-gray-800'],
                    ];
                    $colores = $colorMap[$tramite->est_tin] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $tramite->afiliacion->persona->nom_pnv ?? 'N/A' }}
                        <div class="text-xs text-gray-400">
                            CI: {{ $tramite->afiliacion->persona->cii_pnv ?? '—' }}
                        </div>
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $colores['bg'] }} {{ $colores['text'] }}">
                            {{ $tramite->est_tin }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <div>Inicia: {{ $tramite->fei_tin ? $tramite->fei_tin->format('d/m/Y') : '—' }}</div>
                        <div>Resuelve: {{ $tramite->fer_tin ? $tramite->fer_tin->format('d/m/Y') : '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">

                        {{-- EDITAR --}}
                        <a href="{{ route('tramites-instituciones.edit', $tramite) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="EDITAR TRÁMITE">
                            ✏️
                        </a>

                        {{-- CAMBIAR ESTADO --}}
                        @if(!in_array($tramite->est_tin, ['RESUELTO', 'RECHAZADO', 'ABANDONADO']))
                            <button type="button"
                                    class="text-blue-600 hover:text-blue-900 font-medium"
                                    title="CAMBIAR ESTADO"
                                    onclick="confirmarCambioEstado(
                                        {{ $tramite->getKey() }},
                                        '{{ addslashes($tramite->afiliacion->persona->nom_pnv ?? '') }}',
                                        '{{ $tramite->est_tin }}'
                                    )">
                                🔄
                            </button>
                        @endif

                        {{-- ELIMINAR --}}
                        <form id="form-eliminar-tramite-{{ $tramite->getKey() }}"
                              action="{{ route('tramites-instituciones.destroy', $tramite) }}"
                              method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="ELIMINAR TRÁMITE"
                                    onclick="confirmarEliminarTramite(
                                        {{ $tramite->getKey() }},
                                        '{{ addslashes($tramite->afiliacion->persona->nom_pnv ?? '') }}'
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
                            <span class="text-4xl">📋</span>
                            <span class="font-medium">NO HAY TRÁMITES REGISTRADOS</span>
                            <span class="text-xs">USE EL BOTÓN "NUEVO TRÁMITE" PARA AGREGAR</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="px-6 py-4 border-t border-gray-200">
    {{ $tramites->links() }}
</div>

{{-- Formulario oculto para cambio de estado --}}
<form id="form-cambio-estado" method="POST" class="hidden">
    @csrf
    @method('POST')
    <input type="hidden" name="nuevo_estado" id="nuevo_estado_input">
</form>

{{-- ============================================================ --}}
{{-- SWEETALERT2 — TRÁMITES                                       --}}
{{-- ============================================================ --}}
<script>
    /**
     * Confirmar cambio de estado de un trámite
     */
    function confirmarCambioEstado(id, nombre, estadoActual) {
        const estados = {
            'PENDIENTE':  ['EN PROCESO', 'ABANDONADO'],
            'EN PROCESO': ['RESUELTO', 'RECHAZADO', 'ABANDONADO'],
        };
        const siguientes = estados[estadoActual] || [];
        const opciones   = siguientes.map(e =>
            `<option value="${e}">${e}</option>`
        ).join('');

        Swal.fire({
            title: 'CAMBIAR ESTADO DEL TRÁMITE',
            html: `
                <p class="text-sm text-gray-600 mb-3">
                    AFILIADO: <strong>${nombre}</strong><br>
                    ESTADO ACTUAL: <strong>${estadoActual}</strong>
                </p>
                <select id="swal-estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— SELECCIONE NUEVO ESTADO —</option>
                    ${opciones}
                </select>
            `,
            showCancelButton: true,
            confirmButtonColor: '#2d6a27',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'CAMBIAR ESTADO',
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
            preConfirm: () => {
                const val = document.getElementById('swal-estado').value;
                if (!val) {
                    Swal.showValidationMessage('DEBE SELECCIONAR UN NUEVO ESTADO');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-cambio-estado');
                form.action = `/tramites-instituciones/${id}/cambiar-estado`;
                document.getElementById('nuevo_estado_input').value = result.value;
                form.submit();
            }
        });
    }

    /**
     * Confirmar eliminar trámite
     */
    function confirmarEliminarTramite(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR TRÁMITE?',
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
                document.getElementById(`form-eliminar-tramite-${id}`).submit();
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