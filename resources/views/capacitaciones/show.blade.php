<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">
                    Detalle de Capacitación
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">
                    {{ $capacitacion->ide_cap }} — {{ $capacitacion->nom_cap }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if(!in_array($capacitacion->est_cap, ['FINALIZADA', 'CANCELADA']))
                    <button
                        type="button"
                        onclick="confirmarCambioEstado(
                            {{ $capacitacion->getKey() }},
                            '{{ addslashes($capacitacion->nom_cap) }}',
                            '{{ $capacitacion->est_cap }}'
                        )"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
                        title="CAMBIAR ESTADO DE LA CAPACITACIÓN">
                        🔄 Cambiar Estado
                    </button>
                @endif

                <a href="{{ route('capacitaciones.edit', $capacitacion) }}"
                   class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 transition"
                   title="EDITAR CAPACITACIÓN">
                    ✏️ Editar
                </a>

                <a href="{{ route('capacitaciones.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $colorMap = [
            'PLANIFICADA' => 'bg-gray-100 text-gray-800',
            'EN CURSO'    => 'bg-blue-100 text-blue-800',
            'FINALIZADA'  => 'bg-green-100 text-green-800',
            'CANCELADA'   => 'bg-red-100 text-red-800',
        ];
        $colorBadge = $colorMap[$capacitacion->est_cap] ?? 'bg-gray-100 text-gray-800';
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================================================ --}}
            {{-- SECCIÓN 1 — Datos de la capacitación             --}}
            {{-- ================================================ --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 uppercase">
                        Datos de la Capacitación
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $colorBadge }}">
                        {{ $capacitacion->est_cap }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Código</span>
                        <p class="font-semibold text-gray-900 font-mono">{{ $capacitacion->ide_cap }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Tipo de Capacitación</span>
                        <p class="text-gray-900">{{ $capacitacion->tipoCapacitacion->nom_tca ?? '—' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Nombre</span>
                        <p class="font-semibold text-gray-900 text-base">{{ $capacitacion->nom_cap }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Objetivo</span>
                        <p class="text-gray-700">{{ $capacitacion->obj_cap ?? '—' }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Fecha de Inicio</span>
                        <p class="text-gray-900">{{ $capacitacion->fei_cap ? $capacitacion->fei_cap->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Fecha de Fin</span>
                        <p class="text-gray-900">{{ $capacitacion->fef_cap ? $capacitacion->fef_cap->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Lugar</span>
                        <p class="text-gray-900">{{ $capacitacion->lug_cap ?? '—' }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Responsable</span>
                        <p class="text-gray-900">
                            @if($capacitacion->responsable)
                                {{ strtoupper($capacitacion->responsable->nom_usu . ' ' . $capacitacion->responsable->ape_usu) }}
                            @else
                                <span class="text-gray-400">Sin asignar</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Registrado</span>
                        <p class="text-gray-900">{{ $capacitacion->fcr_cap->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Última Modificación</span>
                        <p class="text-gray-900">{{ $capacitacion->fmo_cap->format('d/m/Y H:i') }}</p>
                    </div>

                </div>
            </div>

            {{-- ================================================ --}}
            {{-- SECCIÓN 2 — Participantes inscritos              --}}
            {{-- ================================================ --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 uppercase">
                            Participantes Inscritos
                        </h3>
                        <p class="text-xs text-gray-500 uppercase mt-0.5">
                            Total: {{ $capacitacion->participaciones->count() }} participante(s)
                        </p>
                    </div>

                    @if($capacitacion->estaDisponible())
                        <button
                            type="button"
                            onclick="abrirModalInscripcion()"
                            class="inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800 transition"
                            title="INSCRIBIR UNA NUEVA PERSONA EN ESTA CAPACITACIÓN">
                            + Inscribir Persona
                        </button>
                    @endif
                </div>

                @include('capacitaciones.partials.participantes')
            </div>

        </div>
    </div>

    {{-- ================================================ --}}
    {{-- MODAL — Inscribir Persona                        --}}
    {{-- ================================================ --}}
    <div id="modal-inscripcion"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
            <div class="flex items-center justify-between mb-5">
                <h4 class="text-lg font-semibold text-gray-800 uppercase">Inscribir Persona</h4>
                <button type="button" onclick="cerrarModalInscripcion()"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <form id="form-inscripcion"
                  action="{{ route('capacitaciones.participaciones.store', $capacitacion) }}"
                  method="POST">
                @csrf

                <input type="hidden" name="cod_capacitaciones_pca" value="{{ $capacitacion->getKey() }}">

                <div class="space-y-4">

                    {{-- PERSONA (Select2 AJAX) --}}
                    <div>
                        <label for="cod_personas_no_videntes_pca"
                               class="block text-sm font-medium text-gray-700 mb-1 uppercase">
                            Persona <span class="text-red-500">*</span>
                        </label>
                        <select id="cod_personas_no_videntes_pca"
                                name="cod_personas_no_videntes_pca"
                                class="w-full"
                                style="width:100%"
                                title="BUSQUE Y SELECCIONE LA PERSONA A INSCRIBIR"
                                required>
                            <option value="">Buscar persona...</option>
                        </select>
                        @error('cod_personas_no_videntes_pca')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FECHA DE INSCRIPCIÓN --}}
                    <div>
                        <label for="fei_pca"
                               class="block text-sm font-medium text-gray-700 mb-1 uppercase">
                            Fecha de Inscripción <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="fei_pca"
                            name="fei_pca"
                            value="{{ old('fei_pca', now()->format('Y-m-d')) }}"
                            title="FECHA EN QUE SE REGISTRA LA INSCRIPCIÓN"
                            class="w-full px-4 py-2 border @error('fei_pca') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        @error('fei_pca')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- OBSERVACIONES --}}
                    <div>
                        <label for="obs_pca"
                               class="block text-sm font-medium text-gray-700 mb-1 uppercase">
                            Observaciones
                        </label>
                        <textarea
                            id="obs_pca"
                            name="obs_pca"
                            rows="2"
                            title="OBSERVACIONES OPCIONALES SOBRE LA INSCRIPCIÓN"
                            placeholder="Observaciones opcionales..."
                            class="w-full px-4 py-2 border @error('obs_pca') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >{{ old('obs_pca') }}</textarea>
                        @error('obs_pca')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit"
                            class="flex-1 px-5 py-2.5 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-medium uppercase text-sm">
                        Inscribir
                    </button>
                    <button type="button"
                            onclick="cerrarModalInscripcion()"
                            class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium uppercase text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Formulario oculto para cambio de estado --}}
    <form id="form-cambio-estado-show" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="nuevo_estado" id="nuevo_estado_show_input">
    </form>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // ── Select2 búsqueda de personas ──────────────────────────
        $(document).ready(function () {
            $('#cod_personas_no_videntes_pca').select2({
                dropdownParent: $('#modal-inscripcion'),
                ajax: {
                    url: '{{ route("personas.buscar") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) { return { results: data.results }; },
                },
                minimumInputLength: 1,
                placeholder: 'Buscar por nombre o CI...',
            });
        });

        // ── Modal inscripción ─────────────────────────────────────
        function abrirModalInscripcion() {
            document.getElementById('modal-inscripcion').classList.remove('hidden');
        }

        function cerrarModalInscripcion() {
            document.getElementById('modal-inscripcion').classList.add('hidden');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modal-inscripcion').addEventListener('click', function (e) {
            if (e.target === this) cerrarModalInscripcion();
        });

        // ── Cambiar estado ────────────────────────────────────────
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
                    <select id="swal-estado-show" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
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
                    const val = document.getElementById('swal-estado-show').value;
                    if (!val) Swal.showValidationMessage('DEBE SELECCIONAR UN NUEVO ESTADO');
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-cambio-estado-show');
                    form.action = `/capacitaciones/${id}/cambiar-estado`;
                    document.getElementById('nuevo_estado_show_input').value = result.value;
                    form.submit();
                }
            });
        }

        // ── Toast flash ───────────────────────────────────────────
        @if(session('success'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false, timer: 3500, timerProgressBar: true,
            });
        @endif

        @if(session('error'))
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false, timer: 4000, timerProgressBar: true,
            });
        @endif

        // ── Abrir modal automáticamente si hubo error de validación ──
        @if($errors->any())
            abrirModalInscripcion();
        @endif
    </script>
    @endpush
</x-app-layout>
