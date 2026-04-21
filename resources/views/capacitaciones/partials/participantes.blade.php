@php
    $transicionesParticipacion = [
        'INSCRITO'  => ['EN CURSO', 'ABANDONÓ'],
        'EN CURSO'  => ['APROBADO', 'REPROBADO', 'ABANDONÓ'],
    ];
    $estadosFinales = ['APROBADO', 'REPROBADO', 'ABANDONÓ'];

    $colorEstado = [
        'INSCRITO'  => 'bg-yellow-100 text-yellow-800',
        'EN CURSO'  => 'bg-blue-100 text-blue-800',
        'APROBADO'  => 'bg-green-100 text-green-800',
        'REPROBADO' => 'bg-red-100 text-red-800',
        'ABANDONÓ'  => 'bg-gray-100 text-gray-800',
    ];
@endphp

{{-- ── TABLA DE PARTICIPANTES ─────────────────────────────────── --}}
<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">CI</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Inscripción</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($capacitacion->participaciones as $participacion)
                <tr class="hover:bg-gray-50 transition">

                    {{-- NOMBRE --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ strtoupper(($participacion->persona->nom_pnv ?? '') . ' ' . ($participacion->persona->ape_pnv ?? '')) }}
                    </td>

                    {{-- CI --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                        {{ $participacion->persona->cii_pnv ?? '—' }}
                    </td>

                    {{-- FECHA INSCRIPCIÓN --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $participacion->fei_pca ? $participacion->fei_pca->format('d/m/Y') : '—' }}
                    </td>

                    {{-- ESTADO --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $colorEstado[$participacion->est_pca] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $participacion->est_pca }}
                        </span>
                    </td>

                    {{-- ACCIONES --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center space-x-2">

                        {{-- CAMBIAR ESTADO --}}
                        @if(!in_array($participacion->est_pca, $estadosFinales))
                            <button type="button"
                                    class="text-blue-600 hover:text-blue-900 font-medium"
                                    title="CAMBIAR ESTADO DE PARTICIPACIÓN"
                                    onclick="cambiarEstadoParticipacion(
                                        {{ $participacion->getKey() }},
                                        '{{ addslashes(strtoupper(($participacion->persona->nom_pnv ?? '') . ' ' . ($participacion->persona->ape_pnv ?? ''))) }}',
                                        '{{ $participacion->est_pca }}'
                                    )">
                                🔄
                            </button>
                        @endif

                        {{-- ELIMINAR --}}
                        <form id="form-elim-part-{{ $participacion->getKey() }}"
                              action="{{ route('capacitaciones.participaciones.destroy', [$capacitacion, $participacion]) }}"
                              method="POST"
                              class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="text-red-600 hover:text-red-900 font-medium"
                                    title="ELIMINAR PARTICIPACIÓN"
                                    onclick="eliminarParticipacion(
                                        {{ $participacion->getKey() }},
                                        '{{ addslashes(strtoupper(($participacion->persona->nom_pnv ?? '') . ' ' . ($participacion->persona->ape_pnv ?? ''))) }}'
                                    )">
                                🗑️
                            </button>
                        </form>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-3xl">👤</span>
                            <span class="font-medium uppercase">No hay participantes inscritos</span>
                            <span class="text-xs uppercase">Use el formulario de abajo para inscribir personas</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── FORMULARIO INSCRIPCIÓN INLINE ──────────────────────────── --}}
@if($capacitacion->estaDisponible())
<div class="border-t border-gray-200 px-6 py-5 bg-gray-50">
    <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Inscribir Nueva Persona</h4>

    <form id="form-inscribir-inline"
          action="{{ route('capacitaciones.participaciones.store', $capacitacion) }}"
          method="POST">
        @csrf
        <input type="hidden" name="cod_capacitaciones_pca" value="{{ $capacitacion->getKey() }}">
        <input type="hidden" name="est_pca" value="INSCRITO">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

            {{-- AUTOCOMPLETE PERSONA --}}
            <div class="sm:col-span-2 relative">
                <label for="buscar-persona-inline"
                       class="block text-xs font-medium text-gray-600 uppercase mb-1">
                    Persona <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="buscar-persona-inline"
                    autocomplete="off"
                    placeholder="Buscar por nombre o CI..."
                    title="ESCRIBA EL NOMBRE O CI DE LA PERSONA A INSCRIBIR"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
                <input type="hidden"
                       id="cod_personas_no_videntes_pca_inline"
                       name="cod_personas_no_videntes_pca"
                       required />
                {{-- Dropdown sugerencias --}}
                <ul id="lista-sugerencias"
                    class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                </ul>
                @error('cod_personas_no_videntes_pca')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- FECHA INSCRIPCIÓN --}}
            <div>
                <label for="fei_pca_inline"
                       class="block text-xs font-medium text-gray-600 uppercase mb-1">
                    Fecha Inscripción <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="fei_pca_inline"
                    name="fei_pca"
                    value="{{ old('fei_pca', now()->format('Y-m-d')) }}"
                    title="FECHA DE INSCRIPCIÓN (DD/MM/AAAA)"
                    class="w-full px-4 py-2 border @error('fei_pca') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    required
                />
                @error('fei_pca')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="mt-4">
            <button type="button"
                    onclick="confirmarInscripcion()"
                    class="px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-medium uppercase text-sm"
                    title="CONFIRMAR INSCRIPCIÓN DE LA PERSONA">
                + Inscribir
            </button>
        </div>
    </form>
</div>
@endif

{{-- ── FORMULARIO OCULTO — CAMBIO DE ESTADO PARTICIPACIÓN ────── --}}
<form id="form-cambio-part" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="est_pca" id="est_pca_cambio_input">
</form>

{{-- ============================================================ --}}
{{-- SWEETALERT2 + AUTOCOMPLETE — PARTICIPANTES                   --}}
{{-- ============================================================ --}}
<script>
    // ── Autocomplete personas ─────────────────────────────────────
    const inputBuscar  = document.getElementById('buscar-persona-inline');
    const inputHidden  = document.getElementById('cod_personas_no_videntes_pca_inline');
    const listaDropdown = document.getElementById('lista-sugerencias');
    let debounceTimer;

    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            inputHidden.value = '';

            if (q.length < 2) {
                listaDropdown.classList.add('hidden');
                listaDropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('personas.buscar') }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => {
                        listaDropdown.innerHTML = '';
                        if (!data.results || data.results.length === 0) {
                            listaDropdown.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 uppercase">Sin resultados</li>';
                            listaDropdown.classList.remove('hidden');
                            return;
                        }
                        data.results.forEach(p => {
                            const li = document.createElement('li');
                            li.textContent = p.text;
                            li.dataset.id  = p.id;
                            li.className   = 'px-4 py-2 text-sm text-gray-800 hover:bg-blue-50 cursor-pointer uppercase';
                            li.addEventListener('click', () => {
                                inputBuscar.value  = p.text;
                                inputHidden.value  = p.id;
                                listaDropdown.classList.add('hidden');
                            });
                            listaDropdown.appendChild(li);
                        });
                        listaDropdown.classList.remove('hidden');
                    })
                    .catch(() => listaDropdown.classList.add('hidden'));
            }, 300);
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!inputBuscar.contains(e.target) && !listaDropdown.contains(e.target)) {
                listaDropdown.classList.add('hidden');
            }
        });
    }

    // ── Confirmar inscripción ─────────────────────────────────────
    function confirmarInscripcion() {
        const persona = document.getElementById('buscar-persona-inline').value.trim();
        const id      = document.getElementById('cod_personas_no_videntes_pca_inline').value;

        if (!id) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'warning',
                title: 'SELECCIONE UNA PERSONA DE LA LISTA',
                showConfirmButton: false, timer: 3000,
            });
            return;
        }

        Swal.fire({
            title: '¿INSCRIBIR PERSONA?',
            html: `PERSONA: <strong>${persona}</strong><br>SE INSCRIBIRÁ EN ESTA CAPACITACIÓN.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2d6a27',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'SÍ, INSCRIBIR',
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-inscribir-inline').submit();
            }
        });
    }

    // ── Cambiar estado de participación ───────────────────────────
    function cambiarEstadoParticipacion(id, nombre, estadoActual) {
        const transiciones = {
            'INSCRITO': ['EN CURSO', 'ABANDONÓ'],
            'EN CURSO': ['APROBADO', 'REPROBADO', 'ABANDONÓ'],
        };
        const siguientes = transiciones[estadoActual] || [];
        const opciones   = siguientes.map(e =>
            `<option value="${e}">${e}</option>`
        ).join('');

        Swal.fire({
            title: 'CAMBIAR ESTADO DE PARTICIPACIÓN',
            html: `
                <p class="text-sm text-gray-600 mb-3">
                    PERSONA: <strong>${nombre}</strong><br>
                    ESTADO ACTUAL: <strong>${estadoActual}</strong>
                </p>
                <select id="swal-estado-part" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
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
                const val = document.getElementById('swal-estado-part').value;
                if (!val) Swal.showValidationMessage('DEBE SELECCIONAR UN NUEVO ESTADO');
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-cambio-part');
                form.action = `/capacitaciones/{{ $capacitacion->getKey() }}/participaciones/${id}`;
                document.getElementById('est_pca_cambio_input').value = result.value;
                form.submit();
            }
        });
    }

    // ── Eliminar participación ────────────────────────────────────
    function eliminarParticipacion(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR PARTICIPACIÓN?',
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
                document.getElementById(`form-elim-part-${id}`).submit();
            }
        });
    }
</script>
