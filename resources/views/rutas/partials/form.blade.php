{{-- Campos comunes para crear y editar ruta --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nombre --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            NOMBRE DE LA RUTA <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nom_rut"
            value="{{ old('nom_rut', $ruta->nom_rut ?? '') }}"
            placeholder="EJ: RUTA AL HOSPITAL DE CLÍNICAS"
            title="NOMBRE DESCRIPTIVO DE LA RUTA — OBLIGATORIO"
            maxlength="150"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('nom_rut') border-red-500 @enderror">
        @error('nom_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Origen --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            ORIGEN <span class="text-red-500">*</span>
        </label>
        <input type="text" name="ori_rut" id="ori_rut"
            value="{{ old('ori_rut', $ruta->ori_rut ?? '') }}"
            placeholder="EJ: PLAZA MURILLO"
            title="PUNTO DE PARTIDA DE LA RUTA — OBLIGATORIO"
            maxlength="200"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('ori_rut') border-red-500 @enderror">
        @error('ori_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Destino --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            DESTINO <span class="text-red-500">*</span>
        </label>
        <input type="text" name="dst_rut" id="dst_rut"
            value="{{ old('dst_rut', $ruta->dst_rut ?? '') }}"
            placeholder="EJ: HOSPITAL DE CLÍNICAS"
            title="PUNTO DE LLEGADA DE LA RUTA — OBLIGATORIO"
            maxlength="200"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('dst_rut') border-red-500 @enderror">
        @error('dst_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2 rounded-lg border border-blue-200 bg-blue-50 p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex-1 relative">
                <label class="block text-sm font-semibold text-blue-800 uppercase mb-1">
                    BUSCAR PUNTO DE INTERÉS
                </label>
                <input type="text" id="buscador-poi"
                    placeholder="EJ: HOSPITAL, PLAZA, MERCADO, UNIVERSIDAD..."
                    title="BUSQUE UN PUNTO DE INTERÉS PARA USARLO COMO ORIGEN O DESTINO"
                    autocomplete="off"
                    class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <div id="poi-autocomplete"
                    class="absolute left-0 right-0 top-full z-30 mt-1 hidden overflow-hidden rounded-lg border border-blue-200 bg-white shadow-xl"></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="usar-poi-como-origen"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-3 py-2 rounded uppercase text-xs shadow">
                    USAR COMO ORIGEN
                </button>
                <button type="button" id="usar-poi-como-destino"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2 rounded uppercase text-xs shadow">
                    USAR COMO DESTINO
                </button>
                <button type="button" id="limpiar-poi"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-3 py-2 rounded uppercase text-xs shadow">
                    LIMPIAR POI
                </button>
            </div>
        </div>
        <p class="text-[11px] text-blue-800 mt-2 uppercase font-semibold">
            ESCRIBA Y ELIGE UN RESULTADO; EL AUTOCOMPLETADO LO ASIGNARÁ AL ORIGEN O DESTINO ACTIVO.
        </p>
        <p id="estado-poi" class="text-xs text-blue-900 mt-2 uppercase font-semibold hidden"></p>
    </div>

    <div class="md:col-span-2 rounded-lg border border-green-200 bg-green-50 p-4 lg:p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <p class="text-sm font-bold text-green-800 uppercase">
                EDITOR DE PUNTOS: ORIGEN → DESTINO → VISTA PREVIA
            </p>
            <div class="flex flex-wrap gap-2 items-center">
                <button type="button" id="btn-iniciar-asistente"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded uppercase text-xs shadow"
                    title="INICIA EL FLUJO GUIADO: PRIMERO ORIGEN Y LUEGO DESTINO">
                    <i class="fas fa-map-pin mr-1"></i> INICIAR SELECCIÓN DE PUNTOS
                </button>
                <button type="button" id="btn-seleccionar-origen"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-3 py-2 rounded uppercase text-xs shadow"
                    title="ACTIVE ESTE MODO Y HAGA CLIC EN EL MAPA PARA FIJAR EL ORIGEN">
                    <i class="fas fa-crosshairs mr-1"></i> SELECCIONAR ORIGEN
                </button>
                <button type="button" id="btn-seleccionar-destino"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2 rounded uppercase text-xs shadow"
                    title="ACTIVE ESTE MODO Y HAGA CLIC EN EL MAPA PARA FIJAR EL DESTINO">
                    <i class="fas fa-bullseye mr-1"></i> SELECCIONAR DESTINO
                </button>
                <button type="button" id="btn-limpiar-puntos"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-3 py-2 rounded uppercase text-xs shadow"
                    title="LIMPIAR PUNTOS MANUALES DE ORIGEN Y DESTINO EN EL MAPA">
                    <i class="fas fa-eraser mr-1"></i> LIMPIAR PUNTOS
                </button>
                <button type="button" id="btn-estimar-ruta"
                    class="text-white font-bold px-4 py-2 rounded uppercase text-xs shadow disabled:opacity-50"
                    style="background-color:#2d6a27"
                    title="CALCULAR DISTANCIA Y TIEMPO ESTIMADO EN BASE A ORIGEN Y DESTINO">
                    <i class="fas fa-map-marked-alt mr-1"></i> CALCULAR DESDE TEXTO
                </button>
            </div>
        </div>

        <p class="mt-3 text-sm">ESTA VISTA PREVIA ESTIMA UN TRAYECTO. ACO COMPARA LOS CAMINOS DE LOS TRAMOS GUARDADOS EN LA FICHA DE LA RUTA.</p>
        @if(isset($ruta) && $ruta->exists)
            <div class="mt-3 flex flex-wrap gap-3" aria-label="Asistente hablado y caminos ACO">
                @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<button type="button" data-asistente-ruta="{{ route('rutas.asistente',$ruta) }}" style="background:#166534;color:white;padding:14px;border-radius:8px">INICIAR ASISTENTE DE VOZ</button>
                <button type="button" data-detener-asistente class="border rounded p-3">DETENER AUDIO</button>
@endif
                <a href="{{ route('rutas.show',$ruta) }}#mapa-detalle-ruta" class="underline p-3">VER CAMINOS ACO Y TRAMOS GUARDADOS</a>
            </div>
            @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<p id="estado-asistente-rutas" role="status" aria-live="polite" class="mt-2">ESCUCHA LA RECOMENDACIÓN Y LOS OBSTÁCULOS DE LA RUTA GUARDADA.</p>
            <script src="{{ asset('js/asistente-rutas.js') }}" defer></script>
@endif
        @endif

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-2" id="pasos-ruta">
            <div id="paso-origen" class="rounded border border-yellow-200 bg-yellow-50 px-3 py-2">
                <p class="text-[11px] font-bold text-yellow-700 uppercase">PASO 1</p>
                <p class="text-xs font-semibold text-yellow-800 uppercase">SELECCIONAR ORIGEN</p>
            </div>
            <div id="paso-destino" class="rounded border border-blue-200 bg-blue-50 px-3 py-2">
                <p class="text-[11px] font-bold text-blue-700 uppercase">PASO 2</p>
                <p class="text-xs font-semibold text-blue-800 uppercase">SELECCIONAR DESTINO</p>
            </div>
            <div id="paso-previa" class="rounded border border-green-200 bg-green-50 px-3 py-2">
                <p class="text-[11px] font-bold text-green-700 uppercase">PASO 3</p>
                <p class="text-xs font-semibold text-green-800 uppercase">REVISAR VISTA PREVIA</p>
            </div>
        </div>

        <p id="mensaje-estimacion-ruta" class="text-xs text-gray-600 mt-2 uppercase"></p>
        <p id="aviso-guardado-ruta" class="text-xs text-red-600 mt-1 uppercase font-semibold hidden">
            DEBE GENERAR Y VISUALIZAR LA RUTA EN EL MAPA ANTES DE GUARDAR.
        </p>

        <div class="mt-3 rounded-lg border border-green-100 bg-white p-2">
            <div id="mapa-estimacion-ruta" class="w-full rounded h-[360px] lg:h-[460px]"></div>
            <p id="mensaje-mapa-ruta" class="text-[11px] text-gray-500 mt-2 uppercase">
                AL CALCULAR, SE MOSTRARÁ EL TRAZO ESTIMADO EN EL MAPA.
            </p>
        </div>
    </div>

    {{-- Distancia --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            DISTANCIA (METROS)
        </label>
        <input type="number" name="dis_rut" id="dis_rut" step="0.01" min="0"
            value="{{ old('dis_rut', $ruta->dis_rut ?? '') }}"
            placeholder="EJ: 850.50"
            title="DISTANCIA TOTAL DE LA RUTA EN METROS — OPCIONAL"
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-600 focus:outline-none @error('dis_rut') border-red-500 @enderror">
        @error('dis_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <input type="hidden" name="geo_rut" id="geo_rut" value="{{ old('geo_rut', isset($ruta) && is_array($ruta->geo_rut) ? json_encode($ruta->geo_rut) : '') }}">

    {{-- Tiempo --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">
            TIEMPO ESTIMADO (MINUTOS)
        </label>
        <input type="number" name="tim_rut" id="tim_rut" min="1"
            value="{{ old('tim_rut', $ruta->tim_rut ?? '') }}"
            placeholder="EJ: 15"
            title="TIEMPO ESTIMADO DE RECORRIDO EN MINUTOS — OPCIONAL"
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-600 focus:outline-none @error('tim_rut') border-red-500 @enderror">
        @error('tim_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">DESCRIPCIÓN</label>
        <textarea name="des_rut" rows="3" maxlength="300"
            placeholder="DESCRIPCIÓN GENERAL DE LA RUTA (OPCIONAL)"
            title="DESCRIPCIÓN OPCIONAL DE LA RUTA"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none @error('des_rut') border-red-500 @enderror">{{ old('des_rut', $ruta->des_rut ?? '') }}</textarea>
        @error('des_rut')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Observaciones --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 uppercase mb-1">OBSERVACIONES</label>
        <textarea name="obs_rut" rows="2" maxlength="1000"
            placeholder="OBSERVACIONES ADICIONALES (OPCIONAL)"
            title="OBSERVACIONES ADICIONALES"
            class="w-full border rounded px-3 py-2 uppercase focus:ring-2 focus:ring-green-600 focus:outline-none">{{ old('obs_rut', $ruta->obs_rut ?? '') }}</textarea>
    </div>
</div>

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-estimar-ruta');
        if (!btn) return;

        const origenInput = document.getElementById('ori_rut');
        const destinoInput = document.getElementById('dst_rut');
        const distanciaInput = document.getElementById('dis_rut');
        const tiempoInput = document.getElementById('tim_rut');
        const geoRutaInput = document.getElementById('geo_rut');
        const buscadorPoi = document.getElementById('buscador-poi');
        const poiAutocomplete = document.getElementById('poi-autocomplete');
        const estadoPoi = document.getElementById('estado-poi');
        const usarPoiComoOrigen = document.getElementById('usar-poi-como-origen');
        const usarPoiComoDestino = document.getElementById('usar-poi-como-destino');
        const limpiarPoi = document.getElementById('limpiar-poi');
        const mensaje = document.getElementById('mensaje-estimacion-ruta');
        const mensajeMapa = document.getElementById('mensaje-mapa-ruta');
        const mapContainer = document.getElementById('mapa-estimacion-ruta');
        const avisoGuardado = document.getElementById('aviso-guardado-ruta');
        const pasoOrigen = document.getElementById('paso-origen');
        const pasoDestino = document.getElementById('paso-destino');
        const pasoPrevia = document.getElementById('paso-previa');
        const btnIniciarAsistente = document.getElementById('btn-iniciar-asistente');
        const btnSeleccionarOrigen = document.getElementById('btn-seleccionar-origen');
        const btnSeleccionarDestino = document.getElementById('btn-seleccionar-destino');
        const btnLimpiarPuntos = document.getElementById('btn-limpiar-puntos');
        const formPrincipal = btn.closest('form');
        const submitPrincipal = formPrincipal?.querySelector('button[type="submit"]');
        const esEdicion = !!formPrincipal?.querySelector('input[name="_method"][value="PUT"]');

        let map = null;
        let markersLayer = null;
        let routeLayer = null;
        let modoSeleccion = null;
        let origenManual = null;
        let destinoManual = null;
        let vistaPreviaGenerada = false;
        let poiSeleccionado = null;
        let objetivoPoi = 'origen';
        let poiTimer = null;
        let poiAbortController = null;
        let poiResultados = [];
        let poiIndiceActivo = -1;

        const clasePasoPendiente = 'opacity-60';
        const clasesPasoCompleto = ['ring-2', 'ring-offset-1', 'ring-green-700', 'opacity-100'];
        const clasesActivasSeleccion = ['ring-2', 'ring-offset-1', 'ring-green-700'];

        const aplicarGrupoClases = (element, classes, activar) => {
            if (!element) return;
            classes.forEach((className) => {
                if (activar) {
                    element.classList.add(className);
                } else {
                    element.classList.remove(className);
                }
            });
        };

        const setPasoEstado = (element, completo) => {
            if (!element) return;
            element.classList.toggle(clasePasoPendiente, !completo);
            aplicarGrupoClases(element, clasesPasoCompleto, !!completo);
        };

        const refrescarPasos = () => {
            setPasoEstado(pasoOrigen, !!origenManual || !!(origenInput?.value || '').trim());
            setPasoEstado(pasoDestino, !!destinoManual || !!(destinoInput?.value || '').trim());
            setPasoEstado(pasoPrevia, vistaPreviaGenerada);
        };

        const actualizarEstadoGuardado = () => {
            if (!submitPrincipal) return;

            const debeExigirPrevia = !esEdicion;
            const bloquear = debeExigirPrevia && !vistaPreviaGenerada;

            submitPrincipal.disabled = bloquear;
            submitPrincipal.classList.toggle('opacity-50', bloquear);
            submitPrincipal.classList.toggle('cursor-not-allowed', bloquear);

            if (avisoGuardado) {
                avisoGuardado.classList.toggle('hidden', !bloquear);
            }
        };

        const mostrarEstadoPoi = (texto) => {
            if (!estadoPoi) return;
            estadoPoi.textContent = texto;
            estadoPoi.classList.remove('hidden');
        };

        const guardarGeoRut = (data) => {
            if (!geoRutaInput) return;

            if (!data || !Array.isArray(data.trayecto_coords) || data.trayecto_coords.length < 2) {
                geoRutaInput.value = '';
                return;
            }

            geoRutaInput.value = JSON.stringify({
                distancia_metros: data.distancia_metros ?? null,
                tiempo_minutos: data.tiempo_minutos ?? null,
                origen_resuelto: data.origen_resuelto ?? '',
                destino_resuelto: data.destino_resuelto ?? '',
                origen_coords: data.origen_coords ?? null,
                destino_coords: data.destino_coords ?? null,
                trayecto_coords: data.trayecto_coords,
            });
        };

        const ocultarAutocompletePoi = () => {
            poiResultados = [];
            poiIndiceActivo = -1;
            if (poiAutocomplete) {
                poiAutocomplete.innerHTML = '';
                poiAutocomplete.classList.add('hidden');
            }
        };

        const limpiarResultadosPoi = () => {
            ocultarAutocompletePoi();
            if (estadoPoi) {
                estadoPoi.textContent = '';
                estadoPoi.classList.add('hidden');
            }
        };

        const pintarResultadosPoi = (items) => {
            if (!poiAutocomplete) return;

            poiResultados = items || [];
            poiIndiceActivo = poiResultados.length ? 0 : -1;

            if (!poiResultados.length) {
                poiAutocomplete.innerHTML = '<div class="p-3 text-xs uppercase text-blue-700">NO SE ENCONTRARON RESULTADOS.</div>';
                poiAutocomplete.classList.remove('hidden');
                return;
            }

            poiAutocomplete.innerHTML = poiResultados.map((item, index) => `
                <button type="button" data-poi-index="${index}" class="poi-option text-left w-full border-b border-blue-100 p-3 hover:bg-blue-50 transition ${index === poiIndiceActivo ? 'bg-blue-50' : 'bg-white'}">
                    <p class="text-xs font-bold text-blue-800 uppercase line-clamp-2">${item.nombre}</p>
                    <p class="text-[11px] text-gray-500 uppercase mt-1">${item.categoria || 'POI'} · ${item.tipo || 'UBICACIÓN'}</p>
                </button>
            `).join('');

            poiAutocomplete.classList.remove('hidden');

            poiAutocomplete.querySelectorAll('[data-poi-index]').forEach((button) => {
                button.addEventListener('click', () => {
                    seleccionarPoi(poiResultados[Number(button.dataset.poiIndex)]);
                });
            });
        };

        const actualizarResaltadoPoi = () => {
            if (!poiAutocomplete) return;

            poiAutocomplete.querySelectorAll('[data-poi-index]').forEach((button) => {
                const index = Number(button.dataset.poiIndex);
                button.classList.toggle('bg-blue-50', index === poiIndiceActivo);
                button.classList.toggle('bg-white', index !== poiIndiceActivo);
            });
        };

        const asignarPoiComoPunto = async (item) => {
            const punto = { lat: item.lat, lng: item.lng };

            poiSeleccionado = item;
            if (buscadorPoi) {
                buscadorPoi.value = item.nombre.toUpperCase();
            }

            if (objetivoPoi === 'origen') {
                origenManual = punto;
                origenInput.value = item.nombre.toUpperCase();
            } else {
                destinoManual = punto;
                destinoInput.value = item.nombre.toUpperCase();
            }

            await pintarTrayecto({
                origen_coords: origenManual,
                destino_coords: destinoManual,
                trayecto_coords: [],
            });

            marcarPreviaNoGenerada();
            refrescarPasos();

            if (origenManual && destinoManual) {
                void calcularPorCoordenadas();
            }
        };

        const seleccionarPoi = async (item) => {
            if (!item) return;
            ocultarAutocompletePoi();
            mostrarEstadoPoi(`SELECCIONADO: ${item.nombre}`);
            await asignarPoiComoPunto(item);
        };

        const buscarPoi = async (termino) => {
            const value = (termino || '').trim();

            if (value.length < 3) {
                limpiarResultadosPoi();
                return;
            }

            mostrarEstadoPoi('BUSCANDO PUNTOS DE INTERÉS...');

            if (poiAbortController) {
                poiAbortController.abort();
            }

            poiAbortController = new AbortController();

            try {
                const resp = await fetch(`{{ route('rutas.puntos-interes') }}?termino=${encodeURIComponent(value)}`, {
                    headers: { 'Accept': 'application/json' },
                    signal: poiAbortController.signal,
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error('NO SE PUDIERON CARGAR LOS PUNTOS DE INTERÉS.');
                }

                pintarResultadosPoi(data.resultados || []);
                mostrarEstadoPoi(`SE ENCONTRARON ${(data.resultados || []).length} RESULTADOS.`);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                pintarResultadosPoi([]);
                mostrarEstadoPoi((error.message || 'NO SE PUDIERON CARGAR LOS PUNTOS DE INTERÉS.').toUpperCase());
            }
        };

        const marcarPreviaNoGenerada = () => {
            vistaPreviaGenerada = false;
            guardarGeoRut(null);
            refrescarPasos();
            actualizarEstadoGuardado();
        };

        const marcarPreviaGenerada = () => {
            vistaPreviaGenerada = true;
            refrescarPasos();
            actualizarEstadoGuardado();
        };

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

        const ensureMap = async () => {
            await initLeafletAssets();

            if (!map && mapContainer) {
                map = window.L.map(mapContainer, {
                    zoomControl: true,
                }).setView([-16.5, -68.15], 12);

                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OPENSTREETMAP CONTRIBUTORS',
                }).addTo(map);

                markersLayer = window.L.layerGroup().addTo(map);
                routeLayer = window.L.layerGroup().addTo(map);

                map.on('click', async (event) => {
                    if (!modoSeleccion) {
                        return;
                    }

                    const punto = {
                        lat: event.latlng.lat,
                        lng: event.latlng.lng,
                    };

                    if (modoSeleccion === 'origen') {
                        origenManual = punto;
                        mensaje.textContent = 'ORIGEN FIJADO. AHORA SELECCIONE DESTINO EN EL MAPA.';
                        modoSeleccion = 'destino';
                    } else if (modoSeleccion === 'destino') {
                        destinoManual = punto;
                        mensaje.textContent = 'DESTINO FIJADO. CALCULANDO VISTA PREVIA...';
                        modoSeleccion = null;
                    }

                    actualizarEstadoBotonesSeleccion();
                    marcarPreviaNoGenerada();
                    refrescarPasos();

                    await pintarTrayecto({
                        origen_coords: origenManual,
                        destino_coords: destinoManual,
                        trayecto_coords: [],
                    });

                    if (origenManual && destinoManual) {
                        void calcularPorCoordenadas();
                    }
                });
            }
        };

        const actualizarEstadoBotonesSeleccion = () => {
            aplicarGrupoClases(btnSeleccionarOrigen, clasesActivasSeleccion, modoSeleccion === 'origen');
            aplicarGrupoClases(btnSeleccionarDestino, clasesActivasSeleccion, modoSeleccion === 'destino');
        };

        const limpiarSeleccionManual = async () => {
            origenManual = null;
            destinoManual = null;
            modoSeleccion = null;
            actualizarEstadoBotonesSeleccion();
            marcarPreviaNoGenerada();

            await ensureMap();
            if (markersLayer) markersLayer.clearLayers();
            if (routeLayer) routeLayer.clearLayers();

            if (mensajeMapa) {
                mensajeMapa.textContent = 'SELECCIÓN MANUAL LIMPIADA. PUEDE VOLVER A ELEGIR PUNTOS.';
            }
        };

        const calcularPorCoordenadas = async () => {
            if (!origenManual || !destinoManual) {
                return;
            }

            btn.disabled = true;
            mensaje.textContent = 'CALCULANDO TRAYECTO DESDE PUNTOS MANUALES...';
            if (mensajeMapa) {
                mensajeMapa.textContent = 'OBTENIENDO TRAZO PARA PUNTOS SELECCIONADOS...';
            }

            try {
                const resp = await fetch('{{ route('rutas.estimar-trayecto') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        origen_lat: origenManual.lat,
                        origen_lng: origenManual.lng,
                        destino_lat: destinoManual.lat,
                        destino_lng: destinoManual.lng,
                    }),
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error(data.mensaje || 'NO SE PUDO CALCULAR EL TRAYECTO DESDE EL MAPA.');
                }

                if (distanciaInput) distanciaInput.value = data.distancia_metros;
                if (tiempoInput) tiempoInput.value = data.tiempo_minutos;
                if (origenInput && data.origen_resuelto) origenInput.value = data.origen_resuelto.toUpperCase();
                if (destinoInput && data.destino_resuelto) destinoInput.value = data.destino_resuelto.toUpperCase();

                await pintarTrayecto(data);
                origenManual = data.origen_coords ?? origenManual;
                destinoManual = data.destino_coords ?? destinoManual;
                guardarGeoRut(data);
                marcarPreviaGenerada();

                mensaje.textContent = `DISTANCIA ESTIMADA: ${data.distancia_metros} M | TIEMPO ESTIMADO: ${data.tiempo_minutos} MIN`;
                if (mensajeMapa) {
                    mensajeMapa.textContent = 'TRAYECTO MANUAL CALCULADO Y MOSTRADO EN EL MAPA.';
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'TRAYECTO CALCULADO DESDE PUNTOS DEL MAPA.',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
            } catch (error) {
                marcarPreviaNoGenerada();
                if (mensajeMapa) {
                    mensajeMapa.textContent = 'NO FUE POSIBLE CALCULAR EL TRAYECTO DESDE PUNTOS MANUALES.';
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: (error.message || 'NO SE PUDO CALCULAR EL TRAYECTO DESDE EL MAPA.').toUpperCase(),
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            } finally {
                btn.disabled = false;
            }
        };

        const pintarTrayecto = async (data) => {
            await ensureMap();
            if (!map || !markersLayer || !routeLayer) return;

            markersLayer.clearLayers();
            routeLayer.clearLayers();

            const origen = data.origen_coords;
            const destino = data.destino_coords;
            const trayecto = Array.isArray(data.trayecto_coords) ? data.trayecto_coords : [];

            if (Number.isFinite(origen?.lat) && Number.isFinite(origen?.lng)) {
                window.L.marker([origen.lat, origen.lng]).addTo(markersLayer)
                    .bindPopup('ORIGEN');
            }

            if (Number.isFinite(destino?.lat) && Number.isFinite(destino?.lng)) {
                window.L.marker([destino.lat, destino.lng]).addTo(markersLayer)
                    .bindPopup('DESTINO');
            }

            let bounds = null;

            if (trayecto.length > 1) {
                const puntos = trayecto
                    .filter(p => Number.isFinite(p.lat) && Number.isFinite(p.lng))
                    .map(p => [p.lat, p.lng]);

                if (puntos.length > 1) {
                    const polyline = window.L.polyline(puntos, {
                        color: '#2d6a27',
                        weight: 5,
                        opacity: 0.85,
                    }).addTo(routeLayer);

                    bounds = polyline.getBounds();
                }
            }

            if (bounds) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }

            setTimeout(() => map.invalidateSize(), 120);
        };

        btn.addEventListener('click', async () => {
            const origen = (origenInput?.value || '').trim();
            const destino = (destinoInput?.value || '').trim();

            if (!origen || !destino) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'DEBE INGRESAR ORIGEN Y DESTINO PARA CALCULAR.',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
                return;
            }

            btn.disabled = true;
            mensaje.textContent = 'CALCULANDO TRAYECTO...';
            if (mensajeMapa) {
                mensajeMapa.textContent = 'OBTENIENDO DATOS DEL TRAZO EN MAPA...';
            }

            try {
                const resp = await fetch('{{ route('rutas.estimar-trayecto') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ origen, destino }),
                });

                const data = await resp.json();

                if (!resp.ok || !data.ok) {
                    throw new Error(data.mensaje || 'NO SE PUDO CALCULAR EL TRAYECTO.');
                }

                if (distanciaInput) distanciaInput.value = data.distancia_metros;
                if (tiempoInput) tiempoInput.value = data.tiempo_minutos;

                await pintarTrayecto(data);
                origenManual = data.origen_coords ?? origenManual;
                destinoManual = data.destino_coords ?? destinoManual;
                guardarGeoRut(data);
                marcarPreviaGenerada();

                mensaje.textContent = `DISTANCIA ESTIMADA: ${data.distancia_metros} M | TIEMPO ESTIMADO: ${data.tiempo_minutos} MIN`;
                if (mensajeMapa) {
                    mensajeMapa.textContent = 'TRAYECTO ESTIMADO MOSTRADO EN EL MAPA.';
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'DISTANCIA Y TIEMPO CALCULADOS CORRECTAMENTE.',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
            } catch (error) {
                mensaje.textContent = '';
                marcarPreviaNoGenerada();
                if (mensajeMapa) {
                    mensajeMapa.textContent = 'NO FUE POSIBLE DIBUJAR EL TRAYECTO EN EL MAPA.';
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: (error.message || 'NO SE PUDO CALCULAR EL TRAYECTO.').toUpperCase(),
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            } finally {
                btn.disabled = false;
            }
        });

        btnSeleccionarOrigen?.addEventListener('click', async () => {
            await ensureMap();
            modoSeleccion = 'origen';
            actualizarEstadoBotonesSeleccion();
            mensaje.textContent = 'PASO 1 ACTIVO: HAGA CLIC EN EL MAPA PARA MARCAR ORIGEN.';
            if (mensajeMapa) {
                mensajeMapa.textContent = 'SELECCIONE EL PUNTO DE PARTIDA EN EL MAPA.';
            }
        });

        btnSeleccionarDestino?.addEventListener('click', async () => {
            await ensureMap();
            modoSeleccion = 'destino';
            actualizarEstadoBotonesSeleccion();
            mensaje.textContent = 'PASO 2 ACTIVO: HAGA CLIC EN EL MAPA PARA MARCAR DESTINO.';
            if (mensajeMapa) {
                mensajeMapa.textContent = 'SELECCIONE EL PUNTO DE LLEGADA EN EL MAPA.';
            }
        });

        btnIniciarAsistente?.addEventListener('click', async () => {
            await ensureMap();
            modoSeleccion = 'origen';
            actualizarEstadoBotonesSeleccion();
            mensaje.textContent = 'ASISTENTE INICIADO: PASO 1, MARQUE EL ORIGEN EN EL MAPA.';
            if (mensajeMapa) {
                mensajeMapa.textContent = 'LUEGO DE MARCAR ORIGEN, SE ACTIVARÁ EL PASO DESTINO.';
            }
        });

        btnLimpiarPuntos?.addEventListener('click', async () => {
            await limpiarSeleccionManual();
            mensaje.textContent = '';
        });

        buscadorPoi?.addEventListener('input', () => {
            clearTimeout(poiTimer);
            const value = buscadorPoi.value;
            poiTimer = setTimeout(() => buscarPoi(value), 220);
        });

        buscadorPoi?.addEventListener('focus', () => {
            if (buscadorPoi.value.trim().length >= 3) {
                buscarPoi(buscadorPoi.value);
            }
        });

        buscadorPoi?.addEventListener('keydown', async (event) => {
            if (!poiResultados.length) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                poiIndiceActivo = (poiIndiceActivo + 1) % poiResultados.length;
                actualizarResaltadoPoi();
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                poiIndiceActivo = (poiIndiceActivo - 1 + poiResultados.length) % poiResultados.length;
                actualizarResaltadoPoi();
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                const item = poiResultados[poiIndiceActivo] ?? poiResultados[0];
                if (item) {
                    await seleccionarPoi(item);
                }
            }

            if (event.key === 'Escape') {
                ocultarAutocompletePoi();
            }
        });

        buscadorPoi?.addEventListener('blur', () => {
            setTimeout(() => ocultarAutocompletePoi(), 180);
        });

        usarPoiComoOrigen?.addEventListener('click', async () => {
            objetivoPoi = 'origen';
            if (!poiSeleccionado) {
                mostrarEstadoPoi('ESCRIBA Y ELIJA UN RESULTADO PARA USARLO COMO ORIGEN.');
                return;
            }

            await asignarPoiComoPunto(poiSeleccionado);
        });

        usarPoiComoDestino?.addEventListener('click', async () => {
            objetivoPoi = 'destino';
            if (!poiSeleccionado) {
                mostrarEstadoPoi('ESCRIBA Y ELIJA UN RESULTADO PARA USARLO COMO DESTINO.');
                return;
            }

            await asignarPoiComoPunto(poiSeleccionado);
        });

        limpiarPoi?.addEventListener('click', () => {
            poiSeleccionado = null;
            objetivoPoi = 'origen';
            if (poiAbortController) {
                poiAbortController.abort();
            }
            if (buscadorPoi) buscadorPoi.value = '';
            limpiarResultadosPoi();
            mostrarEstadoPoi('BÚSQUEDA DE PUNTO DE INTERÉS LIMPIADA.');
        });

        origenInput?.addEventListener('input', marcarPreviaNoGenerada);
        destinoInput?.addEventListener('input', marcarPreviaNoGenerada);

        ensureMap().catch(() => {
            if (mensajeMapa) {
                mensajeMapa.textContent = 'NO SE PUDO INICIALIZAR EL MAPA EN ESTE MOMENTO.';
            }
        });

        refrescarPasos();
        actualizarEstadoGuardado();
    });
    </script>
    @endpush
@endonce
