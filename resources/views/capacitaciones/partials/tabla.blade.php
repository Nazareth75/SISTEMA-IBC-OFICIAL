<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Código</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fechas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Responsable</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($capacitaciones as $cap)
                @php
                    $colorMap = [
                        'PLANIFICADA' => 'bg-gray-100 text-gray-800',
                        'EN CURSO'    => 'bg-blue-100 text-blue-800',
                        'FINALIZADA'  => 'bg-green-100 text-green-800',
                        'CANCELADA'   => 'bg-red-100 text-red-800',
                    ];
                    $colorBadge = $colorMap[$cap->est_cap] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <tr class="hover:bg-gray-50 transition">

                    {{-- CÓDIGO --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-700">
                        {{ $cap->ide_cap }}
                    </td>

                    {{-- NOMBRE --}}
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="font-medium">{{ $cap->nom_cap }}</div>
                        @if($cap->obj_cap)
                            <div class="text-xs text-gray-400 truncate max-w-xs" title="{{ $cap->obj_cap }}">
                                {{ $cap->obj_cap }}
                            </div>
                        @endif
                    </td>

                    {{-- TIPO --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $cap->tipoCapacitacion->nom_tca ?? '—' }}
                        </span>
                    </td>

                    {{-- FECHAS --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <div>Inicio: {{ $cap->fei_cap ? $cap->fei_cap->format('d/m/Y') : '—' }}</div>
                        <div>Fin: {{ $cap->fef_cap ? $cap->fef_cap->format('d/m/Y') : '—' }}</div>
                    </td>

                    {{-- RESPONSABLE --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        @if($cap->responsable)
                            {{ $cap->responsable->nom_usu }} {{ $cap->responsable->ape_usu }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    {{-- ESTADO --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $colorBadge }}">
                            {{ $cap->est_cap }}
                        </span>
                    </td>

                    {{-- ACCIONES --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">

                        {{-- VER --}}
                        <a href="{{ route('capacitaciones.show', $cap) }}"
                           class="text-gray-600 hover:text-gray-900 font-medium"
                           title="VER DETALLE">
                            👁️
                        </a>

                        {{-- EDITAR --}}
                        <a href="{{ route('capacitaciones.edit', $cap) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="EDITAR CAPACITACIÓN">
                            ✏️
                        </a>

                        {{-- CAMBIAR ESTADO --}}
                        @if(!in_array($cap->est_cap, ['FINALIZADA', 'CANCELADA']))
                            <button type="button"
                                    class="text-blue-600 hover:text-blue-900 font-medium"
                                    title="CAMBIAR ESTADO"
                                    onclick="confirmarCambioEstado(
                                        {{ $cap->getKey() }},
                                        '{{ addslashes($cap->nom_cap) }}',
                                        '{{ $cap->est_cap }}'
                                    )">
                                🔄
                            </button>
                        @endif

                        {{-- ELIMINAR --}}
                        <form id="form-eliminar-cap-{{ $cap->getKey() }}"
                              action="{{ route('capacitaciones.destroy', $cap) }}"
                              method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="ELIMINAR CAPACITACIÓN"
                                    onclick="confirmarEliminar(
                                        {{ $cap->getKey() }},
                                        '{{ addslashes($cap->nom_cap) }}'
                                    )">
                                🗑️
                            </button>
                        </form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-4xl">🎓</span>
                            <span class="font-medium uppercase">No hay capacitaciones registradas</span>
                            <span class="text-xs uppercase">Use el botón "Nueva Capacitación" para agregar</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="px-6 py-4 border-t border-gray-200">
    {{ $capacitaciones->links() }}
</div>

{{-- Formulario oculto para cambio de estado --}}
<form id="form-cambio-estado-cap" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="nuevo_estado" id="nuevo_estado_cap_input">
</form>

{{-- ============================================================ --}}
{{-- SWEETALERT2 — CAPACITACIONES                                 --}}
{{-- ============================================================ --}}
<script>
    function confirmarCambioEstado(id, nombre, estadoActual) {
        const transiciones = {
            'PLANIFICADA': ['EN CURSO', 'CANCELADA'],
            'EN CURSO':    ['FINALIZADA', 'CANCELADA'],
        };
        const siguientes = transiciones[estadoActual] || [];
        const opciones   = siguientes.map(e =>
            `<option value="${e}">${e}</option>`
        ).join('');

        Swal.fire({
            title: 'CAMBIAR ESTADO DE CAPACITACIÓN',
            html: `
                <p class="text-sm text-gray-600 mb-3">
                    CAPACITACIÓN: <strong>${nombre}</strong><br>
                    ESTADO ACTUAL: <strong>${estadoActual}</strong>
                </p>
                <select id="swal-estado-cap" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
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
                const val = document.getElementById('swal-estado-cap').value;
                if (!val) {
                    Swal.showValidationMessage('DEBE SELECCIONAR UN NUEVO ESTADO');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-cambio-estado-cap');
                form.action = `/capacitaciones/${id}/cambiar-estado`;
                document.getElementById('nuevo_estado_cap_input').value = result.value;
                form.submit();
            }
        });
    }

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR CAPACITACIÓN?',
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
                document.getElementById(`form-eliminar-cap-${id}`).submit();
            }
        });
    }

    {{-- Toast flash --}}
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
