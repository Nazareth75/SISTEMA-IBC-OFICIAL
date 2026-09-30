<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('rutas.index') }}" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 uppercase">
                        <i class="fas fa-route mr-2" style="color:#2d6a27"></i>
                        {{ $ruta->ide_rut }} — {{ $ruta->nom_rut }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1 uppercase">
                        {{ $ruta->ori_rut }} <i class="fas fa-arrow-right mx-1 text-xs"></i> {{ $ruta->dst_rut }}
                    </p>
                </div>
            </div>
            @php $color = \App\Models\Ruta::COLORES_ESTADO[$ruta->est_rut] ?? 'bg-gray-100 text-gray-800'; @endphp
            <span class="px-3 py-1 rounded-full text-sm font-bold uppercase {{ $color }}">
                {{ $ruta->est_rut }}
            </span>
        </div>
    </x-slot>

    @php $editable = in_array($ruta->est_rut, ['BORRADOR','DESACTUALIZADA']) && Auth::user()->puedeEditarRuta(); @endphp

    {{-- Formulario oculto para cambio de estado --}}
    <form id="form-cambio-estado" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="nuevo_estado" id="input-nuevo-estado">
        <input type="hidden" name="observaciones" id="input-observaciones">
    </form>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── FICHA GENERAL ─────────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">CÓDIGO</p>
                        <p class="font-mono font-bold text-gray-800">{{ $ruta->ide_rut }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">DISTANCIA</p>
                        <p class="font-bold text-gray-800">{{ $ruta->dis_rut ? number_format($ruta->dis_rut, 1).' M' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">TIEMPO EST.</p>
                        <p class="font-bold text-gray-800">{{ $ruta->tim_rut ? $ruta->tim_rut.' MIN' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">NIVEL SEGURIDAD</p>
                        @if($nivelGeneral)
                            <span class="inline-flex items-center gap-1 font-bold text-xs"
                                style="color:{{ $nivelGeneral->colorHex() }}">
                                <i class="fas fa-shield-alt"></i> {{ $nivelGeneral->nom_nse }}
                            </span>
                        @else
                            <p class="text-gray-400 text-xs">SIN EVALUAR</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">CREADO POR</p>
                        <p class="text-gray-700">{{ $ruta->creador?->nom_usu ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">FECHA CREACIÓN</p>
                        <p class="text-gray-700">{{ $ruta->fcr_rut?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @if($ruta->aprobador)
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">APROBADO POR</p>
                        <p class="text-gray-700">{{ $ruta->aprobador->nom_usu }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">FECHA APROBACIÓN</p>
                        <p class="text-gray-700">{{ $ruta->fap_rut?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @endif
                </div>

                @if($ruta->des_rut)
                    <p class="text-sm text-gray-600 uppercase border-t pt-3"><strong>DESCRIPCIÓN:</strong> {{ $ruta->des_rut }}</p>
                @endif
                @if($ruta->obs_rut)
                    <p class="text-sm text-gray-600 uppercase mt-2"><strong>OBSERVACIONES:</strong> {{ $ruta->obs_rut }}</p>
                @endif

                {{-- Botones de acción --}}
                <div class="mt-4 pt-4 border-t flex flex-wrap gap-2">
                    @if($editable)
                        <a href="{{ route('rutas.edit', $ruta) }}"
                            class="inline-flex items-center gap-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-sm font-bold px-4 py-2 rounded uppercase">
                            <i class="fas fa-pencil-alt"></i> EDITAR
                        </a>
                    @endif

                    @php
                        $transiciones = collect($ruta->transicionesPosibles())->filter(function($t) {
                            if (in_array($t, ['APROBADA','PUBLICADA','ARCHIVADA'])) return Auth::user()->puedeAprobarRuta();
                            return Auth::user()->puedeEnviarRutaARevision();
                        })->values()->toArray();
                    @endphp
                    @if(!empty($transiciones))
                        <button type="button"
                            onclick="confirmarCambioEstado({{ $ruta->getKey() }}, '{{ $ruta->est_rut }}', {{ json_encode($transiciones) }}, {{ $puedePublicarse === false ? 'false' : 'true' }})"
                            class="inline-flex items-center gap-1 text-white text-sm font-bold px-4 py-2 rounded uppercase"
                            style="background-color:#2d6a27">
                            <i class="fas fa-exchange-alt"></i> CAMBIAR ESTADO
                        </button>
                    @endif

                    @if(Auth::user()->puedeEliminarRuta() && $ruta->est_rut === 'BORRADOR')
                        <button type="button"
                            onclick="confirmarEliminar({{ $ruta->getKey() }}, '{{ addslashes($ruta->nom_rut) }}')"
                            class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-bold px-4 py-2 rounded uppercase">
                            <i class="fas fa-trash-alt"></i> ELIMINAR
                        </button>
                        <form id="form-eliminar-ruta" action="{{ route('rutas.destroy', $ruta) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>

                @if($puedePublicarse === false)
                    <div class="mt-3 bg-red-50 border border-red-200 rounded p-3 text-xs text-red-700 uppercase font-semibold">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        ESTA RUTA NO PUEDE PUBLICARSE AÚN: EXISTEN TRAMOS CON NIVEL DE SEGURIDAD INSUFICIENTE O SIN EVALUAR (RN-03/RN-04).
                    </div>
                @endif
            </div>

            {{-- ── VISTA PREVIA DEL TRAYECTO ───────────────────── --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h3 class="font-bold text-gray-700 uppercase">
                        <i class="fas fa-map-marked-alt mr-2" style="color:#2d6a27"></i>
                        VISTA PREVIA DEL TRAYECTO
                    </h3>
                    @if($trayectoDetalle && !empty($trayectoDetalle['distancia_metros']))
                        <span class="text-xs font-bold uppercase bg-green-100 text-green-700 px-2 py-1 rounded">
                            {{ number_format((float) $trayectoDetalle['distancia_metros'], 1) }} M · {{ (int) ($trayectoDetalle['tiempo_minutos'] ?? 0) }} MIN
                        </span>
                    @endif
                </div>

                <p class="text-xs text-gray-500 uppercase mb-3">
                    ORIGEN: {{ $ruta->ori_rut }} <span class="mx-1">→</span> DESTINO: {{ $ruta->dst_rut }}
                </p>

                @if($trayectoDetalle && !empty($trayectoDetalle['trayecto_coords']))
                    <div id="mapa-detalle-ruta" class="w-full rounded border border-green-100 h-[360px] lg:h-[460px]"></div>
                    <p class="text-[11px] text-green-700 font-semibold uppercase mt-2">
                        EL TRAZO MOSTRADO CORRESPONDE A LA RUTA CALCULADA SOBRE RED VIAL.
                    </p>
                @else
                    <div class="rounded border border-yellow-200 bg-yellow-50 p-4 text-xs text-yellow-700 uppercase font-semibold">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        NO SE PUDO GENERAR GEOMETRÍA DE TRAYECTO PARA ESTA COMBINACIÓN DE ORIGEN Y DESTINO.
                    </div>
                @endif
            </div>

            {{-- ── TRAMOS ──────────────────────────────────────────── --}}
            <div>
                <h3 class="font-bold text-gray-700 uppercase mb-3">
                    <i class="fas fa-road mr-2" style="color:#2d6a27"></i>
                    TRAMOS ({{ $ruta->tramos->count() }})
                </h3>

                @forelse($ruta->tramos as $tramo)
                    @php $ultimaEval = $tramo->evaluaciones->first(); @endphp
                    <div x-data="{ abierto: true }" class="bg-white rounded-lg shadow mb-4">
                        {{-- Cabecera del tramo --}}
                        <div @click="abierto = !abierto"
                            class="flex items-center justify-between px-5 py-3 cursor-pointer border-b"
                            style="background-color:#f0fdf4">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-white text-xs rounded-full w-7 h-7 flex items-center justify-center"
                                    style="background-color:#2d6a27">{{ $tramo->ord_ctr }}</span>
                                <div>
                                    <p class="font-bold uppercase text-gray-800 text-sm">{{ $tramo->nom_ctr }}</p>
                                    <p class="text-xs text-gray-500 uppercase">
                                        {{ $tramo->tip_ctr }}
                                        @if($tramo->lon_ctr) · {{ number_format($tramo->lon_ctr,1) }} M @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($ultimaEval)
                                    <span class="text-xs font-bold px-2 py-1 rounded"
                                        style="background-color:{{ $ultimaEval->nivelSeguridad->colorHex() }}20; color:{{ $ultimaEval->nivelSeguridad->colorHex() }}">
                                        <i class="fas fa-shield-alt mr-1"></i>{{ $ultimaEval->nivelSeguridad->nom_nse }}
                                    </span>
                                @endif
                                <i :class="abierto ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        <div x-show="abierto" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- CONDICIONES --}}
                            <div x-data="{ agregando: false }">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-xs font-bold text-gray-600 uppercase">
                                        <i class="fas fa-clipboard-list mr-1"></i> CONDICIONES ({{ $tramo->condiciones->count() }})
                                    </h4>
                                    @if($editable)
                                        <button @click="agregando = !agregando" type="button"
                                            class="text-xs text-green-700 hover:underline uppercase font-semibold">
                                            <i class="fas fa-plus"></i> AGREGAR
                                        </button>
                                    @endif
                                </div>
                                @forelse($tramo->condiciones as $c)
                                    <div class="flex items-start justify-between text-xs bg-gray-50 rounded p-2 mb-1">
                                        <div>
                                            <span class="{{ \App\Models\CondicionEntorno::COLORES_NIVEL[$c->niv_cen] ?? '' }} px-1 rounded text-xs font-bold mr-1">{{ $c->niv_cen }}</span>
                                            <span class="font-semibold uppercase">{{ $c->tip_cen }}:</span>
                                            <span class="text-gray-600 uppercase ml-1">{{ $c->des_cen }}</span>
                                        </div>
                                        @if($editable)
                                        <form action="{{ route('tramos.condiciones.destroy', [$tramo, $c]) }}" method="POST" class="ml-2 shrink-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600" title="ELIMINAR CONDICIÓN">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 uppercase italic">SIN CONDICIONES REGISTRADAS</p>
                                @endforelse
                                @if($editable)
                                <form x-show="agregando" x-cloak
                                    action="{{ route('tramos.condiciones.store', $tramo) }}" method="POST"
                                    class="mt-2 bg-green-50 rounded p-3 space-y-2">
                                    @csrf
                                    <select name="tip_cen" required class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none focus:ring-1 focus:ring-green-600">
                                        <option value="">TIPO *</option>
                                        @foreach(\App\Models\CondicionEntorno::TIPOS as $v => $l)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="des_cen" required maxlength="250"
                                        placeholder="DESCRIPCIÓN *"
                                        class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none focus:ring-1 focus:ring-green-600">
                                    <select name="niv_cen" required class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none focus:ring-1 focus:ring-green-600">
                                        @foreach(\App\Models\CondicionEntorno::NIVELES as $v => $l)
                                            <option value="{{ $v }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex gap-2">
                                        <button type="submit" class="text-white text-xs font-bold px-3 py-1 rounded uppercase" style="background-color:#2d6a27">GUARDAR</button>
                                        <button @click="agregando=false" type="button" class="bg-gray-200 text-xs font-bold px-3 py-1 rounded uppercase">CANCELAR</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            {{-- EVALUACIÓN DE RIESGO --}}
                            <div x-data="{ agregando: false }">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-xs font-bold text-gray-600 uppercase">
                                        <i class="fas fa-shield-alt mr-1"></i> EVALUACIONES ({{ $tramo->evaluaciones->count() }})
                                    </h4>
                                    @if(Auth::user()->puedeEditarRuta())
                                        <button @click="agregando = !agregando" type="button"
                                            class="text-xs text-green-700 hover:underline uppercase font-semibold">
                                            <i class="fas fa-plus"></i> EVALUAR
                                        </button>
                                    @endif
                                </div>
                                @forelse($tramo->evaluaciones->take(3) as $ev)
                                    <div class="flex items-start justify-between text-xs bg-gray-50 rounded p-2 mb-1">
                                        <div>
                                            <span class="font-bold" style="color:{{ $ev->nivelSeguridad->colorHex() }}">
                                                {{ $ev->nivelSeguridad->nom_nse }}
                                            </span>
                                            <span class="text-gray-500 ml-1">— {{ $ev->fechaFormateada() }}</span>
                                            @if($ev->met_eri) <span class="text-gray-400 uppercase ml-1">| {{ $ev->met_eri }}</span> @endif
                                        </div>
                                        @if(Auth::user()->puedeEditarRuta())
                                        <form action="{{ route('tramos.evaluaciones.destroy', [$tramo, $ev]) }}" method="POST" class="ml-2 shrink-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 uppercase italic">SIN EVALUACIONES REGISTRADAS</p>
                                @endforelse
                                @if(Auth::user()->puedeEditarRuta())
                                <form x-show="agregando" x-cloak
                                    action="{{ route('tramos.evaluaciones.store', $tramo) }}" method="POST"
                                    class="mt-2 bg-green-50 rounded p-3 space-y-2">
                                    @csrf
                                    <select name="cod_niveles_seguridad_eri" required class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none">
                                        <option value="">NIVEL DE SEGURIDAD *</option>
                                        @foreach(\App\Models\NivelSeguridad::orderBy('niv_nse')->get() as $ns)
                                            <option value="{{ $ns->getKey() }}">{{ $ns->nom_nse }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="fee_eri" required value="{{ date('Y-m-d') }}"
                                        class="w-full border rounded px-2 py-1 text-xs focus:outline-none">
                                    <input type="text" name="met_eri" maxlength="150" placeholder="MÉTODO (OPCIONAL)"
                                        class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none">
                                    <div class="flex gap-2">
                                        <button type="submit" class="text-white text-xs font-bold px-3 py-1 rounded uppercase" style="background-color:#2d6a27">GUARDAR</button>
                                        <button @click="agregando=false" type="button" class="bg-gray-200 text-xs font-bold px-3 py-1 rounded uppercase">CANCELAR</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            {{-- OBSTÁCULOS --}}
                            <div x-data="{ agregando: false }" class="md:col-span-2">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-xs font-bold text-gray-600 uppercase">
                                        <i class="fas fa-exclamation-triangle mr-1 text-yellow-500"></i>
                                        OBSTÁCULOS ({{ $tramo->obstaculos->count() }})
                                    </h4>
                                    @if(Auth::user()->puedeGestionarObstaculos())
                                        <button @click="agregando = !agregando" type="button"
                                            class="text-xs text-yellow-600 hover:underline uppercase font-semibold">
                                            <i class="fas fa-plus"></i> REPORTAR
                                        </button>
                                    @endif
                                </div>
                                @forelse($tramo->obstaculos as $ob)
                                    @php $obColor = \App\Models\Obstaculo::COLORES_ESTADO[$ob->est_obs] ?? ''; @endphp
                                    <div class="flex items-center justify-between text-xs bg-gray-50 rounded p-2 mb-1">
                                        <div>
                                            <span class="{{ $obColor }} px-1 rounded font-bold mr-1">{{ $ob->est_obs }}</span>
                                            <span class="font-semibold uppercase">{{ $ob->tip_obs }}:</span>
                                            <span class="text-gray-600 uppercase ml-1">{{ Str::limit($ob->des_obs, 60) }}</span>
                                        </div>
                                        @if(Auth::user()->puedeGestionarObstaculos() && $ob->est_obs === 'REPORTADO')
                                            <div class="flex gap-1 ml-2 shrink-0">
                                                <form action="{{ route('obstaculos.confirmar', $ob) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" title="CONFIRMAR OBSTÁCULO"
                                                        class="text-red-500 hover:text-red-700 font-bold text-xs px-2 py-0.5 bg-red-50 rounded">CONFIRMAR</button>
                                                </form>
                                                <form action="{{ route('obstaculos.rechazar', $ob) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="RECHAZAR REPORTE"
                                                        class="text-gray-500 hover:text-gray-700 font-bold text-xs px-2 py-0.5 bg-gray-100 rounded">RECHAZAR</button>
                                                </form>
                                            </div>
                                        @elseif(Auth::user()->puedeGestionarObstaculos() && $ob->est_obs === 'ACTIVO')
                                            <form action="{{ route('obstaculos.resolver', $ob) }}" method="POST" class="ml-2 shrink-0">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="MARCAR COMO RESUELTO"
                                                    class="text-green-600 hover:text-green-800 font-bold text-xs px-2 py-0.5 bg-green-50 rounded">RESOLVER</button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 uppercase italic">SIN OBSTÁCULOS REGISTRADOS</p>
                                @endforelse
                                @if(Auth::user()->puedeGestionarObstaculos())
                                <form x-show="agregando" x-cloak
                                    action="{{ route('tramos.obstaculos.store', $tramo) }}" method="POST"
                                    enctype="multipart/form-data"
                                    class="mt-2 bg-yellow-50 rounded p-3 space-y-2">
                                    @csrf
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="tip_obs" required class="border rounded px-2 py-1 text-xs uppercase focus:outline-none">
                                            <option value="">TIPO *</option>
                                            @foreach(\App\Models\Obstaculo::TIPOS as $v => $l)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                        <input type="file" name="fot_obs" accept="image/*"
                                            class="border rounded px-2 py-1 text-xs focus:outline-none">
                                    </div>
                                    <input type="text" name="des_obs" required maxlength="250"
                                        placeholder="DESCRIPCIÓN DEL OBSTÁCULO *"
                                        class="w-full border rounded px-2 py-1 text-xs uppercase focus:outline-none">
                                    <div class="flex gap-2">
                                        <button type="submit" class="text-white text-xs font-bold px-3 py-1 rounded uppercase" style="background-color:#f0b429; color:#1a1a1a">REPORTAR</button>
                                        <button @click="agregando=false" type="button" class="bg-gray-200 text-xs font-bold px-3 py-1 rounded uppercase">CANCELAR</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            {{-- ── PERFIL DE RIESGO DEL TRAMO ── --}}
                            @php $perfilRiesgo = $tramo->perfilRiesgo(); @endphp
                            <div class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-lg p-4 mt-2">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                                    <h4 class="text-xs font-bold text-slate-800 uppercase flex items-center gap-1.5">
                                        <i class="fas fa-chart-bar text-slate-600"></i>
                                        PERFIL DE RIESGO DEL TRAMO
                                    </h4>
                                    @php
                                        $badgeCompletitud = match($perfilRiesgo['completitud_datos']) {
                                            'COMPLETO' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                            'PARCIAL'  => 'bg-amber-100 text-amber-800 border-amber-300',
                                            default    => 'bg-slate-200 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded border {{ $badgeCompletitud }}">
                                        COMPLETITUD: {{ $perfilRiesgo['completitud_datos'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    {{-- EVALUACIÓN TÉCNICA --}}
                                    <div class="bg-white p-2.5 rounded border border-slate-200 shadow-sm">
                                        <p class="text-[10px] text-slate-500 font-semibold uppercase">EVALUACIÓN TÉCNICA</p>
                                        @if($perfilRiesgo['estado_evaluacion'] === 'NO_EVALUADO')
                                            <p class="font-bold text-slate-500 mt-1 uppercase">NO EVALUADO</p>
                                            <p class="text-[10px] text-slate-400">Riesgo eval: <span class="font-mono">N/A</span></p>
                                        @else
                                            <p class="font-bold text-emerald-700 mt-1 uppercase">{{ $perfilRiesgo['nivel_seguridad_nombre'] }}</p>
                                            <p class="text-[10px] text-slate-600">Riesgo eval: <span class="font-mono font-bold">{{ number_format($perfilRiesgo['riesgo_evaluacion'], 2) }}</span></p>
                                        @endif
                                    </div>

                                    {{-- CONDICIONES --}}
                                    <div class="bg-white p-2.5 rounded border border-slate-200 shadow-sm">
                                        <p class="text-[10px] text-slate-500 font-semibold uppercase">CONDICIONES DEL ENTORNO</p>
                                        @if($perfilRiesgo['riesgo_condiciones'] === null)
                                            <p class="font-bold text-slate-500 mt-1 uppercase">0 REGISTRADAS</p>
                                            <p class="text-[10px] text-slate-400">Promedio: <span class="font-mono">N/A</span></p>
                                        @else
                                            <p class="font-bold text-slate-800 mt-1 uppercase">{{ $perfilRiesgo['condiciones_count'] }} REGISTRADAS</p>
                                            <p class="text-[10px] text-slate-600">Promedio riesg: <span class="font-mono font-bold">{{ number_format($perfilRiesgo['riesgo_condiciones'], 2) }}</span></p>
                                        @endif
                                    </div>

                                    {{-- ZONAS ACTIVAS --}}
                                    <div class="bg-white p-2.5 rounded border border-slate-200 shadow-sm">
                                        <p class="text-[10px] text-slate-500 font-semibold uppercase">ZONAS DE RIESGO</p>
                                        @if($perfilRiesgo['riesgo_zonas_max'] === null)
                                            <p class="font-bold text-slate-500 mt-1 uppercase">0 ACTIVAS</p>
                                            <p class="text-[10px] text-slate-400">Riesgo máx: <span class="font-mono">N/A</span></p>
                                        @else
                                            <p class="font-bold text-amber-700 mt-1 uppercase">{{ $perfilRiesgo['zonas_activas_count'] }} ACTIVAS</p>
                                            <p class="text-[10px] text-slate-600">Riesgo máx: <span class="font-mono font-bold">{{ number_format($perfilRiesgo['riesgo_zonas_max'], 2) }}</span></p>
                                        @endif
                                    </div>

                                    {{-- OBSTÁCULOS --}}
                                    <div class="bg-white p-2.5 rounded border border-slate-200 shadow-sm">
                                        <p class="text-[10px] text-slate-500 font-semibold uppercase">OBSTÁCULOS E INCIDENCIAS</p>
                                        <p class="font-bold text-slate-800 mt-1 uppercase">
                                            {{ $perfilRiesgo['obstaculos_activos_count'] }} ACTIVOS · {{ $perfilRiesgo['obstaculos_reportados_count'] }} REP.
                                        </p>
                                        <p class="text-[10px] text-slate-600">
                                            Prio. máx: 
                                            @if($perfilRiesgo['prioridad_maxima'] !== null)
                                                <span class="font-mono font-bold">{{ $perfilRiesgo['prioridad_maxima'] }}/4 (Norm: {{ number_format($perfilRiesgo['prioridad_normalizada'], 2) }})</span>
                                            @else
                                                <span class="font-mono text-slate-400">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 italic mt-2.5">
                                    * Nota técnica: Los indicadores se representan individualmente en escala [0.00 - 1.00]. La ausencia de datos devuelve N/A sin asumir 0.00% de riesgo (Fase 3A).
                                </p>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow p-10 text-center text-gray-400">
                        <div class="text-5xl mb-3">🛣️</div>
                        <p class="font-semibold uppercase">ESTA RUTA NO TIENE TRAMOS REGISTRADOS</p>
                        @if($editable)
                            <a href="{{ route('rutas.edit', $ruta) }}"
                                class="inline-block mt-3 text-white text-sm font-bold px-4 py-2 rounded uppercase"
                                style="background-color:#2d6a27">
                                <i class="fas fa-plus mr-1"></i> AGREGAR TRAMOS
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    function confirmarCambioEstado(id, estadoActual, transiciones, puedePublicar) {
        const opciones = transiciones.map(e => `<option value="${e}">${e}</option>`).join('');
        let advertencia = '';
        if (transiciones.includes('PUBLICADA') && !puedePublicar) {
            advertencia = `<div class="bg-red-50 border border-red-200 rounded p-2 text-xs text-red-600 uppercase mt-2 text-left">
                ⚠️ LA RUTA NO CUMPLE REQUISITOS MÍNIMOS DE SEGURIDAD (RN-03/RN-04). NO PODRÁ PUBLICARSE.
            </div>`;
        }
        Swal.fire({
            title: 'CAMBIAR ESTADO DE RUTA',
            html: `<p class="text-sm text-gray-500 mb-2">ESTADO ACTUAL: <strong>${estadoActual}</strong></p>
                   <select id="swal-estado" class="w-full border rounded px-3 py-2 text-sm uppercase mb-2">
                       <option value="">— SELECCIONE NUEVO ESTADO —</option>
                       ${opciones}
                   </select>
                   <textarea id="swal-obs" rows="2" placeholder="OBSERVACIONES (OPCIONAL)"
                       class="w-full border rounded px-3 py-2 text-sm uppercase"></textarea>
                   ${advertencia}`,
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
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: 'SÍ, ELIMINAR', cancelButtonText: 'CANCELAR', reverseButtons: true,
        }).then(r => { if (r.isConfirmed) document.getElementById('form-eliminar-ruta').submit(); });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const s = document.querySelector('meta[name="flash-success"]');
        if (s) Swal.fire({ toast:true, position:'top-end', icon:'success', title: s.content, showConfirmButton:false, timer:3500, timerProgressBar:true });
        const e = document.querySelector('meta[name="flash-error"]');
        if (e) Swal.fire({ toast:true, position:'top-end', icon:'error', title: e.content, showConfirmButton:false, timer:4000, timerProgressBar:true });

        const trayectoDetalle = @json($trayectoDetalle);
        const mapContainer = document.getElementById('mapa-detalle-ruta');

        if (!mapContainer || !trayectoDetalle || !Array.isArray(trayectoDetalle.trayecto_coords) || trayectoDetalle.trayecto_coords.length < 2) {
            return;
        }

        const initLeafletAssets = () => {
            if (!document.querySelector('link[data-leaflet="1"]')) {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                css.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
                css.crossOrigin = '';
                css.setAttribute('data-leaflet', '1');
                document.head.appendChild(css);
            }

            if (window.L) {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                script.crossOrigin = '';
                script.onload = resolve;
                script.onerror = () => reject(new Error('NO SE PUDO CARGAR LEAFLET.'));
                document.body.appendChild(script);
            });
        };

        initLeafletAssets().then(() => {
            const map = window.L.map(mapContainer, { zoomControl: true }).setView([-16.5, -68.15], 13);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OPENSTREETMAP CONTRIBUTORS',
            }).addTo(map);

            const puntos = trayectoDetalle.trayecto_coords
                .filter((p) => Number.isFinite(p.lat) && Number.isFinite(p.lng))
                .map((p) => [p.lat, p.lng]);

            if (puntos.length < 2) {
                return;
            }

            const line = window.L.polyline(puntos, {
                color: '#2d6a27',
                weight: 5,
                opacity: 0.9,
            }).addTo(map);

            const origen = trayectoDetalle.origen_coords;
            const destino = trayectoDetalle.destino_coords;

            if (origen && Number.isFinite(origen.lat) && Number.isFinite(origen.lng)) {
                window.L.marker([origen.lat, origen.lng]).addTo(map).bindPopup('ORIGEN');
            }

            if (destino && Number.isFinite(destino.lat) && Number.isFinite(destino.lng)) {
                window.L.marker([destino.lat, destino.lng]).addTo(map).bindPopup('DESTINO');
            }

            map.fitBounds(line.getBounds(), { padding: [30, 30] });
            setTimeout(() => map.invalidateSize(), 120);
        }).catch(() => {
            // No interrumpir la vista de detalle si falla la carga del mapa.
        });
    });
    </script>
    @endpush
</x-app-layout>
