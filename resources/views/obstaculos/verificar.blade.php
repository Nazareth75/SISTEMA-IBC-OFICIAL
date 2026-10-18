<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-gray-800 leading-tight uppercase flex items-center gap-2">
                <i class="fas fa-clipboard-check text-emerald-600"></i>
                VERIFICACIÓN COLABORATIVA DE OBSTÁCULO
            </h2>
            <a href="javascript:history.back()" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 uppercase">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Tarjeta de Información del Obstáculo --}}
        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">OBSTÁCULO VIAL #{{ $obstaculo->getKey() }}</span>
                    <h3 class="text-base font-black text-slate-900 uppercase mt-0.5 flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            {{ $obstaculo->tip_obs }}
                        </span>
                        <span>{{ $obstaculo->tramo?->nom_ctr ?? 'Tramo de Ruta' }}</span>
                    </h3>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $obstaculo->colorEstado() }}">
                    ESTADO: {{ $obstaculo->est_obs }}
                </span>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">DESCRIPCIÓN DEL PELIGRO REPORTADO</p>
                    <p class="text-sm text-slate-800 mt-1 font-medium bg-slate-50 p-3 rounded-lg border border-slate-100">
                        "{{ $obstaculo->des_obs }}"
                    </p>
                </div>

                @if($obstaculo->tramo && $obstaculo->tramo->rutas->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">RUTAS PEATONALES ASOCIADAS</p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($obstaculo->tramo->rutas as $r)
                                <a href="{{ route('rutas.show', $r) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 border border-slate-300">
                                    <i class="fas fa-route text-emerald-600"></i>
                                    {{ $r->ide_rut }} — {{ $r->nom_rut }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Formulario Accesible para el Voluntario --}}
        <div class="bg-white rounded-xl shadow border-2 border-emerald-500/40 overflow-hidden">
            <div class="px-6 py-4 bg-emerald-50/60 border-b border-emerald-100">
                <h4 class="text-sm font-black text-emerald-950 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-walking text-emerald-600"></i>
                    ¿EL OBSTÁCULO CONTINÚA PRESENTE EN LA VÍA?
                </h4>
                <p class="text-xs text-emerald-800 mt-0.5">
                    Tu verificación ciudadana ayuda al sistema a actualizar las recomendaciones de viaje seguro para personas con discapacidad visual.
                </p>
            </div>

            <form action="{{ route('obstaculos.verificar.store', $obstaculo) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                {{-- Campos GPS listos para futura app móvil (null por defecto en web) --}}
                <input type="hidden" name="latitud" id="campo-latitud" value="">
                <input type="hidden" name="longitud" id="campo-longitud" value="">
                <input type="hidden" name="precision_gps" id="campo-precision" value="">

                {{-- Botones de Selección Directa (Accesibilidad para Voluntarios) --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        SELECCIONA TU VERIFICACIÓN <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- Opción 1: YA NO ESTÁ --}}
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:bg-emerald-50/50 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/80 border-slate-200 shadow-sm">
                            <input type="radio" name="respuesta" value="YA_NO_ESTA" class="sr-only" required>
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">
                                    <i class="fas fa-check"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-black uppercase text-emerald-900">YA NO ESTÁ</p>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">La vía está libre y despejada</p>
                                </div>
                            </div>
                        </label>

                        {{-- Opción 2: SIGUE PRESENTE --}}
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:bg-rose-50/50 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/80 border-slate-200 shadow-sm">
                            <input type="radio" name="respuesta" value="SIGUE_PRESENTE" class="sr-only">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-base">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-black uppercase text-rose-900">SÍ, SIGUE PRESENTE</p>
                                    <p class="text-[11px] text-rose-700 mt-0.5">El peligro persiste en el tramo</p>
                                </div>
                            </div>
                        </label>

                        {{-- Opción 3: NO PUEDO VERIFICAR --}}
                        <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition hover:bg-slate-100/50 has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100 border-slate-200 shadow-sm">
                            <input type="radio" name="respuesta" value="NO_PUEDE_VERIFICAR" class="sr-only">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-base">
                                    <i class="fas fa-question"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-black uppercase text-slate-900">NO PUEDO VERIFICAR</p>
                                    <p class="text-[11px] text-slate-600 mt-0.5">Visibilidad nula o zona cerrada</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    @error('respuesta')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observación Opcional --}}
                <div>
                    <label for="observacion" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        OBSERVACIÓN ADICIONAL <span class="text-slate-400 font-normal">(OPCIONAL)</span>
                    </label>
                    <textarea name="observacion" id="observacion" rows="3"
                        placeholder="Describe detalles sobre el estado del tramo, reparaciones municipales observadas o precauciones..."
                        class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition"></textarea>
                    @error('observacion')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Foto Opcional --}}
                <div>
                    <label for="foto" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        FOTOGRAFÍA DE EVIDENCIA <span class="text-slate-400 font-normal">(OPCIONAL, MÁX. 2 MB)</span>
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    @error('foto')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botón de Enviar --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="javascript:history.back()" class="px-4 py-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 uppercase">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-xs font-bold text-white shadow uppercase transition hover:opacity-95" style="background-color: #2d6a27">
                        <i class="fas fa-paper-plane mr-1.5"></i> Registrar Verificación
                    </button>
                </div>
            </form>
        </div>

        {{-- Historial de Verificaciones de la Comunidad --}}
        @if($consenso['total_verificaciones'] > 0)
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center justify-between">
                    <span>
                        <i class="fas fa-users text-indigo-600 mr-1.5"></i>
                        VERIFICACIONES REGISTRADAS POR LA COMUNIDAD ({{ $consenso['total_verificaciones'] }})
                    </span>
                    <span class="text-[11px] font-semibold text-slate-500">
                        {{ $consenso['usuarios_unicos'] }} voluntarios distintos
                    </span>
                </h5>

                <div class="space-y-2">
                    @foreach($consenso['ultimas_verificaciones'] as $v)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $v->badgeClase() }}">
                                    {{ $v->etiquetaRespuesta() }}
                                </span>
                                <div>
                                    <p class="font-bold text-slate-800">{{ $v->usuario?->name ?? $v->usuario?->nom_usu ?? 'Voluntario' }}</p>
                                    @if($v->observacion)
                                        <p class="text-slate-600 text-[11px] italic mt-0.5">"{{ $v->observacion }}"</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ $v->created_at->format('d/m/Y H:i') }} ({{ $v->canal }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
