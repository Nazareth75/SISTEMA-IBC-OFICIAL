{{-- Formulario oculto para cambio de estado --}}
<form id="form-cambio-estado" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="nuevo_estado" id="input-nuevo-estado">
    <input type="hidden" name="observaciones" id="input-observaciones">
</form>

{{-- Formularios ocultos para eliminar --}}
@foreach($rutas as $ruta)
    <form id="form-eliminar-{{ $ruta->getKey() }}"
        action="{{ route('rutas.destroy', $ruta) }}"
        method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach

@if($rutas->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
        <div class="text-5xl mb-4">🗺️</div>
        <p class="font-semibold uppercase text-lg">NO SE ENCONTRARON RUTAS</p>
        <p class="text-sm mt-1">REGISTRA LA PRIMERA RUTA SEGURA DEL SISTEMA</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead style="background-color:#2d6a27">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">CÓDIGO</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase">NOMBRE</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase hidden md:table-cell">ORIGEN → DESTINO</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ESTADO</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden lg:table-cell">TRAMOS</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase hidden sm:table-cell">ESTADO ACO</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rutas as $ruta)
                    @php
                        $color = \App\Models\Ruta::COLORES_ESTADO[$ruta->est_rut] ?? 'bg-gray-100 text-gray-800';
                        $analisisAcoFila = isset($constructorAco) ? $constructorAco->analizar($ruta) : $ruta->analisisAco();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-bold text-gray-700">{{ $ruta->ide_rut }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800 uppercase">{{ $ruta->nom_rut }}</td>
                        <td class="px-4 py-3 text-gray-600 uppercase hidden md:table-cell">
                            <span class="text-xs">{{ Str::limit($ruta->ori_rut, 20) }}</span>
                            <i class="fas fa-arrow-right text-gray-400 mx-1 text-xs"></i>
                            <span class="text-xs">{{ Str::limit($ruta->dst_rut, 20) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $color }}">
                                {{ $ruta->est_rut }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 hidden lg:table-cell">
                            {{ $ruta->tramos->count() }}
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $analisisAcoFila->colorBadge }}"
                                title="{{ implode(' | ', $analisisAcoFila->motivosBloqueo) ?: 'Ruta lista para optimización bioinspirada ACO.' }}">
                                <i class="{{ $analisisAcoFila->iconoFa }} text-[11px]"></i>
                                <span>{{ $analisisAcoFila->etiquetaCorta }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                {{-- VER --}}
                                @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<button type="button" data-asistente-ruta="{{ route('rutas.asistente', $ruta) }}"
                                    data-nombre-ruta="{{ $ruta->nom_rut }}. Desde {{ $ruta->ori_rut }} hasta {{ $ruta->dst_rut }}"
                                    aria-label="Iniciar asistente para {{ $ruta->nom_rut }}"
                                    style="background-color:#2d6a27;color:white" class="bg-green-800 text-white font-bold px-4 py-3 rounded uppercase">
                                    INICIAR ASISTENTE
                                </button>
@endif
                                <a href="{{ route('rutas.show', $ruta) }}"
                                    title="Consultar ficha, mapa, tramos y recomendación ACO"
                                    class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-2 py-1 rounded shadow-sm transition">
                                    <i class="fas fa-eye text-[11px]"></i>
                                    <span>VER</span>
                                </a>

                                {{-- EDITAR --}}
                                @if(Auth::user()?->puedeEditarRuta())
                                    <a href="{{ route('rutas.edit', $ruta) }}"
                                        title="Editar metadatos, tramos y geometría de la ruta"
                                        class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold px-2 py-1 rounded shadow-sm transition">
                                        <i class="fas fa-pencil-alt text-[11px]"></i>
                                        <span>EDITAR</span>
                                    </a>
                                @else
                                    <span title="Requiere rol con permisos de edición para modificar rutas."
                                        class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-semibold px-2 py-1 rounded cursor-not-allowed select-none">
                                        <i class="fas fa-pencil-alt text-[11px]"></i>
                                        <span>EDITAR</span>
                                    </span>
                                @endif

                                {{-- CAMBIAR ESTADO --}}
                                @php
                                    $transiciones = $ruta->transicionesPosibles();
                                    $transPermitidas = collect($transiciones)->filter(function($t) {
                                        if (in_array($t, ['APROBADA','PUBLICADA','ARCHIVADA'])) return Auth::user()?->puedeAprobarRuta();
                                        return Auth::user()?->puedeEnviarRutaARevision();
                                    })->values()->toArray();
                                @endphp
                                @if(!empty($transPermitidas))
                                    <button type="button"
                                        title="Cambiar estado operacional de la ruta"
                                        onclick="confirmarCambioEstado({{ $ruta->getKey() }}, '{{ $ruta->est_rut }}', {{ json_encode($transPermitidas) }})"
                                        class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-bold px-2 py-1 rounded shadow-sm transition">
                                        <i class="fas fa-exchange-alt text-[11px]"></i>
                                        <span>ESTADO</span>
                                    </button>
                                @else
                                    <span title="No hay transiciones de estado disponibles para su rol en el estado actual"
                                        class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-semibold px-2 py-1 rounded cursor-not-allowed select-none">
                                        <i class="fas fa-exchange-alt text-[11px]"></i>
                                        <span>ESTADO</span>
                                    </span>
                                @endif

                                {{-- ELIMINAR --}}
                                @if(Auth::user()?->puedeEliminarRuta())
                                    <button type="button"
                                        title="Eliminar ruta de forma segura (desvincula tramos sin destruirlos)"
                                        onclick="confirmarEliminar({{ $ruta->getKey() }}, '{{ addslashes($ruta->nom_rut) }}')"
                                        class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold px-2 py-1 rounded shadow-sm transition">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                        <span>ELIMINAR</span>
                                    </button>
                                @else
                                    <span title="Requiere rol de Administrador para eliminar rutas."
                                        class="inline-flex items-center gap-1 bg-gray-100 text-gray-400 border border-gray-200 text-xs font-semibold px-2 py-1 rounded cursor-not-allowed select-none">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                        <span>ELIMINAR</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rutas->links() }}</div>
@endif

@push('scripts')
<script>
function confirmarCambioEstado(id, estadoActual, transiciones) {
    const opciones = transiciones.map(e => `<option value="${e}">${e}</option>`).join('');
    Swal.fire({
        title: 'CAMBIAR ESTADO DE RUTA',
        html: `<p class="text-sm text-gray-500 mb-3">ESTADO ACTUAL: <strong>${estadoActual}</strong></p>
               <select id="swal-estado" class="w-full border rounded px-3 py-2 text-sm uppercase mb-3">
                   <option value="">— SELECCIONE NUEVO ESTADO —</option>
                   ${opciones}
               </select>
               <textarea id="swal-obs" rows="2" placeholder="OBSERVACIONES (OPCIONAL)"
                   class="w-full border rounded px-3 py-2 text-sm uppercase"></textarea>`,
        showCancelButton: true,
        confirmButtonColor: '#2d6a27',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'CONFIRMAR',
        cancelButtonText: 'CANCELAR',
        reverseButtons: true,
        preConfirm: () => {
            const val = document.getElementById('swal-estado').value;
            if (!val) { Swal.showValidationMessage('DEBE SELECCIONAR UN NUEVO ESTADO'); return false; }
            return { estado: val, obs: document.getElementById('swal-obs').value };
        }
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.getElementById('form-cambio-estado');
            form.action = `/rutas/${id}/cambiar-estado`;
            document.getElementById('input-nuevo-estado').value = result.value.estado;
            document.getElementById('input-observaciones').value = result.value.obs;
            form.submit();
        }
    });
}

function confirmarEliminar(id, nombre) {
    Swal.fire({
        title: '¿ELIMINAR RUTA?',
        html: `<span class="font-semibold">${nombre}</span><br>
               <small class="text-red-500">ESTA ACCIÓN NO SE PUEDE DESHACER.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'SÍ, ELIMINAR',
        cancelButtonText: 'CANCELAR',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) document.getElementById(`form-eliminar-${id}`).submit();
    });
}

// Flash messages
document.addEventListener('DOMContentLoaded', () => {
    const s = document.querySelector('meta[name="flash-success"]');
    if (s) Swal.fire({ toast:true, position:'top-end', icon:'success', title: s.content, showConfirmButton:false, timer:3500, timerProgressBar:true });
    const e = document.querySelector('meta[name="flash-error"]');
    if (e) Swal.fire({ toast:true, position:'top-end', icon:'error', title: e.content, showConfirmButton:false, timer:4000, timerProgressBar:true });
});
</script>
@endpush
