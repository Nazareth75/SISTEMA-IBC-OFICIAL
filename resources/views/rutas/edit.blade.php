<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rutas.show', $ruta) }}" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-bold text-xl text-gray-800 uppercase">
                    <i class="fas fa-pencil-alt mr-2" style="color:#2d6a27"></i>
                    EDITAR RUTA — {{ $ruta->ide_rut }}
                </h2>
                <p class="text-sm text-gray-500 mt-1 uppercase">{{ $ruta->nom_rut }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── FORMULARIO DATOS GENERALES ─────────────────────── --}}
            <form action="{{ route('rutas.update', $ruta) }}" method="POST">
                @csrf @method('PUT')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 border-b pb-2">
                        DATOS GENERALES DE LA RUTA
                    </h3>
                    @include('rutas.partials.form')
                </div>
                <div class="mt-3 flex justify-end gap-3">
                    <a href="{{ route('rutas.show', $ruta) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-5 py-2 rounded uppercase text-sm">
                        CANCELAR
                    </a>
                    <button type="submit"
                        class="text-white font-bold px-5 py-2 rounded uppercase text-sm shadow"
                        style="background-color:#2d6a27">
                        <i class="fas fa-save mr-1"></i> GUARDAR CAMBIOS
                    </button>
                </div>
            </form>

            {{-- ── GESTIÓN DE TRAMOS ────────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex justify-between items-center" style="background-color:#f0fdf4">
                    <h3 class="font-bold text-gray-700 uppercase">
                        <i class="fas fa-road mr-2" style="color:#2d6a27"></i>
                        TRAMOS DE LA RUTA ({{ $ruta->tramos->count() }})
                    </h3>
                </div>

                @if($ruta->tramos->isEmpty())
                    <div class="p-8 text-center text-gray-400">
                        <div class="text-4xl mb-2">🛣️</div>
                        <p class="uppercase font-semibold">AÚN NO HAY TRAMOS REGISTRADOS</p>
                        <p class="text-sm mt-1">AGREGA EL PRIMER TRAMO USANDO EL FORMULARIO DE ABAJO</p>
                    </div>
                @else
                    <div class="p-4 space-y-3">
                        @foreach($ruta->tramos as $tramo)
                            <div x-data="{ editando: false }" class="border rounded-lg">
                                {{-- Vista normal del tramo --}}
                                <div x-show="!editando" class="flex items-center justify-between p-3">
                                    <div class="flex items-center gap-3">
                                        <span class="bg-gray-200 text-gray-600 font-bold text-xs rounded-full w-7 h-7 flex items-center justify-center">
                                            {{ $tramo->ord_ctr }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-sm uppercase text-gray-800">{{ $tramo->nom_ctr }}</p>
                                            <p class="text-xs text-gray-500 uppercase">
                                                {{ $tramo->tip_ctr }}
                                                @if($tramo->lon_ctr) · {{ number_format($tramo->lon_ctr, 1) }} M @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="editando = true" type="button"
                                            class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-bold px-3 py-1 rounded uppercase">
                                            <i class="fas fa-pencil-alt"></i> EDITAR
                                        </button>
                                        <button type="button"
                                            onclick="confirmarEliminarTramo({{ $tramo->getKey() }}, '{{ addslashes($tramo->nom_ctr) }}')"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold px-3 py-1 rounded uppercase">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="form-tramo-{{ $tramo->getKey() }}"
                                            action="{{ route('rutas.tramos.destroy', [$ruta, $tramo]) }}"
                                            method="POST" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </div>

                                {{-- Formulario edición inline --}}
                                <form x-show="editando" x-cloak
                                    action="{{ route('rutas.tramos.update', [$ruta, $tramo]) }}"
                                    method="POST" class="p-4 bg-yellow-50 border-t">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">NOMBRE *</label>
                                            <input type="text" name="nom_ctr" value="{{ $tramo->nom_ctr }}" required maxlength="150"
                                                class="w-full border rounded px-2 py-1 text-sm uppercase focus:ring-1 focus:ring-green-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">TIPO *</label>
                                            <select name="tip_ctr" required class="w-full border rounded px-2 py-1 text-sm uppercase focus:ring-1 focus:ring-green-600 focus:outline-none">
                                                @foreach(\App\Models\CalleTramo::TIPOS as $val => $label)
                                                    <option value="{{ $val }}" @selected($tramo->tip_ctr === $val)>{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">LONGITUD (M)</label>
                                            <input type="number" name="lon_ctr" value="{{ $tramo->lon_ctr }}" step="0.01" min="0"
                                                class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-green-600 focus:outline-none">
                                        </div>
                                        <div class="md:col-span-3 mt-1" x-data="{ verGeoEdit: false }">
                                            <button type="button" @click="verGeoEdit = !verGeoEdit" class="text-xs text-yellow-800 hover:underline font-semibold flex items-center gap-1">
                                                <i class="fas" :class="verGeoEdit ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                <span x-text="verGeoEdit ? 'OCULTAR COORDENADAS GEO' : 'MODIFICAR COORDENADAS GEO_CTR (JSON)'"></span>
                                            </button>
                                            <div x-show="verGeoEdit" x-cloak class="mt-1">
                                                <textarea name="geo_ctr" rows="2" placeholder='[{"lat": ..., "lng": ...}]'
                                                    class="w-full font-mono text-xs border rounded p-1.5 focus:outline-none">{{ $tramo->geo_ctr ? json_encode($tramo->geo_ctr) : '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2 justify-end">
                                        <button @click="editando = false" type="button"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold px-3 py-1 rounded uppercase">
                                            CANCELAR
                                        </button>
                                        <button type="submit"
                                            class="text-white text-xs font-bold px-3 py-1 rounded uppercase"
                                            style="background-color:#2d6a27">
                                            GUARDAR
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Formulario agregar nuevo tramo --}}
                <div class="border-t p-4 bg-gray-50">
                    <h4 class="text-xs font-bold text-gray-600 uppercase mb-3">
                        <i class="fas fa-plus-circle mr-1" style="color:#2d6a27"></i> AGREGAR NUEVO TRAMO
                    </h4>
                    <form action="{{ route('rutas.tramos.store', $ruta) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <input type="text" name="nom_ctr" required maxlength="150"
                                    placeholder="NOMBRE DEL TRAMO *"
                                    title="NOMBRE DEL TRAMO — OBLIGATORIO"
                                    class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">
                            </div>
                            <div>
                                <select name="tip_ctr" required
                                    title="TIPO DE TRAMO — OBLIGATORIO"
                                    class="w-full border rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">
                                    <option value="">TIPO *</option>
                                    @foreach(\App\Models\CalleTramo::TIPOS as $val => $label)
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" name="lon_ctr" step="0.01" min="0"
                                    placeholder="LONGITUD (M)"
                                    title="LONGITUD DEL TRAMO EN METROS — OPCIONAL SI TIENE GEOMETRÍA"
                                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-green-600 focus:outline-none">
                            </div>
                            <div class="md:col-span-4" x-data="{ verGeo: false }">
                                <button type="button" @click="verGeo = !verGeo" class="text-xs text-green-700 hover:underline font-semibold flex items-center gap-1">
                                    <i class="fas" :class="verGeo ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    <span x-text="verGeo ? 'OCULTAR GEOMETRÍA DEL TRAMO (COORDENADAS GPS)' : '+ DEFINIR GEOMETRÍA DEL TRAMO (OPCIONAL)'"></span>
                                </button>
                                <div x-show="verGeo" x-cloak class="mt-2 bg-white p-3 rounded border border-gray-200">
                                    <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">
                                        COORDENADAS GEO_CTR (JSON CON MÍNIMO 2 PUNTOS: [{"lat": -16.5, "lng": -68.1}, ...])
                                    </label>
                                    <textarea name="geo_ctr" rows="2"
                                        placeholder='[{"lat": -16.5029, "lng": -68.1363}, {"lat": -16.4992, "lng": -68.1333}]'
                                        class="w-full font-mono text-xs border rounded p-2 focus:ring-2 focus:ring-green-600 focus:outline-none"></textarea>
                                    <p class="text-[10px] text-gray-500 mt-1">
                                        * Si define la geometría, la longitud en metros se calculará automáticamente mediante la distancia geodésica entre coordenadas.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                class="text-white font-bold px-5 py-2 rounded uppercase text-sm shadow"
                                style="background-color:#2d6a27">
                                <i class="fas fa-plus mr-1"></i> AGREGAR TRAMO
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function confirmarEliminarTramo(id, nombre) {
        Swal.fire({
            title: '¿ELIMINAR TRAMO?',
            html: `<span class="font-semibold">${nombre}</span><br>
                   <small class="text-red-500">SE ELIMINARÁN TAMBIÉN SUS CONDICIONES Y DATOS ASOCIADOS.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'SÍ, ELIMINAR',
            cancelButtonText: 'CANCELAR',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) document.getElementById(`form-tramo-${id}`).submit(); });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const s = document.querySelector('meta[name="flash-success"]');
        if (s) Swal.fire({ toast:true, position:'top-end', icon:'success', title: s.content, showConfirmButton:false, timer:3500, timerProgressBar:true });
        const e = document.querySelector('meta[name="flash-error"]');
        if (e) Swal.fire({ toast:true, position:'top-end', icon:'error', title: e.content, showConfirmButton:false, timer:4000, timerProgressBar:true });
    });
    </script>
    @endpush
</x-app-layout>
