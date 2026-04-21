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
                        <div>✉ {{ $institucion->cor_iex ?? '—' }}</div>
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

                        {{-- VER --}}
                        <a href="{{ route('instituciones-externas.show', $institucion) }}"
                           class="text-blue-600 hover:text-blue-900 font-medium"
                           title="VER DETALLES">
                            👁️
                        </a>

                        {{-- EDITAR --}}
                        <a href="{{ route('instituciones-externas.edit', $institucion) }}"
                           class="text-orange-600 hover:text-orange-900 font-medium"
                           title="EDITAR">
                            ✏️
                        </a>

                        {{-- TOGGLE ACTIVO/INACTIVO --}}
                        <form id="form-toggle-{{ $institucion->getKey() }}"
                              action="{{ route('instituciones-externas.toggle', $institucion) }}"
                              method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                    class="{{ $institucion->act_iex ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }} font-medium"
                                    title="{{ $institucion->act_iex ? 'DESACTIVAR' : 'ACTIVAR' }}"
                                    onclick="confirmarToggle(
                                        {{ $institucion->getKey() }},
                                        '{{ addslashes($institucion->nom_iex) }}',
                                        {{ $institucion->act_iex ? 'true' : 'false' }}
                                    )">
                                {{ $institucion->act_iex ? '🔴' : '🟢' }}
                            </button>
                        </form>

                        {{-- ELIMINAR --}}
                        <form id="form-eliminar-{{ $institucion->getKey() }}"
                              action="{{ route('instituciones-externas.destroy', $institucion) }}"
                              method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="ELIMINAR"
                                    onclick="confirmarEliminar(
                                        {{ $institucion->getKey() }},
                                        '{{ addslashes($institucion->nom_iex) }}'
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
                            <span class="text-4xl">🏢</span>
                            <span class="font-medium">NO HAY INSTITUCIONES REGISTRADAS</span>
                            <span class="text-xs">USE EL BOTÓN "NUEVA INSTITUCIÓN" PARA AGREGAR</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="px-6 py-4 border-t border-gray-200">
    {{ $instituciones->links() }}
</div>

{{-- ============================================================ --}}
{{-- SWEETALERT2 — INSTITUCIONES                                  --}}
{{-- ============================================================ --}}
<script>
    // Colores IBC
    const IBC_VERDE   = '#2d6a27';
    const IBC_AMARILLO = '#f0b429';

    /**
     * Confirmar activar / desactivar institución
     */
    function confirmarToggle(id, nombre, estaActiva) {
        const accion     = estaActiva ? 'DESACTIVAR' : 'ACTIVAR';
        const icono      = estaActiva ? 'warning'    : 'question';
        const colorBtn   = estaActiva ? '#f0b429'    : '#2d6a27';
        const textoBtn   = estaActiva ? 'SÍ, DESACTIVAR' : 'SÍ, ACTIVAR';
        const mensajeSub = estaActiva
            ? 'LA INSTITUCIÓN NO ESTARÁ DISPONIBLE PARA NUEVOS TRÁMITES.'
            : 'LA INSTITUCIÓN VOLVERÁ A ESTAR DISPONIBLE PARA TRÁMITES.';

        Swal.fire({
            title: `¿${accion} INSTITUCIÓN?`,
            html: `<span class="font-semibold">${nombre}</span><br><small class="text-gray-500">${mensajeSub}</small>`,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: colorBtn,
            cancelButtonColor: '#6b7280',
            confirmButtonText: textoBtn,
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`form-toggle-${id}`).submit();
            }
        });
    }

    /**
     * Confirmar eliminar institución
     */
    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR INSTITUCIÓN?',
            html: `
                <span class="font-semibold">${nombre}</span><br>
                <small class="text-gray-500">ESTA ACCIÓN ES IRREVERSIBLE.</small><br>
                <small class="text-red-500 font-medium">NO SE PUEDE ELIMINAR SI TIENE TRÁMITES ASOCIADOS.</small>
            `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'SÍ, ELIMINAR',
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`form-eliminar-${id}`).submit();
            }
        });
    }

    {{-- Alerta flash de éxito --}}
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

    {{-- Alerta flash de error --}}
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