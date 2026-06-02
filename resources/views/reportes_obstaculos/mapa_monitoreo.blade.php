<x-app-layout>
    <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] relative overflow-hidden bg-gray-50">
        
        {{-- Área Central del Mapa --}}
        <div class="flex-1 h-full relative" id="map-container-wrapper">
            <div id="mapa-monitoreo-canvas" class="w-full h-full z-10"></div>
            
            {{-- Instrucción Flotante para Reporte por Mapa --}}
            <div id="map-instruction" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 bg-black/80 text-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider hidden shadow-lg flex items-center gap-2">
                <span class="animate-ping w-2 h-2 rounded-full bg-red-500"></span>
                <span>HAGA CLIC EN EL MAPA PARA UBICAR EL OBSTÁCULO</span>
            </div>

            {{-- Leyenda de Riesgo Flotante --}}
            <div class="absolute bottom-8 left-8 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl shadow-lg border border-gray-200 w-64 select-none">
                <h3 class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider">LEYENDA DE RIESGO</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-green-500 inline-block"></span>
                        <span class="text-xs font-bold text-gray-700 uppercase">BAJO (VÍA SEGURA)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-yellow-500 inline-block"></span>
                        <span class="text-xs font-bold text-gray-700 uppercase">MODERADO (PRECAUCIÓN)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-orange-500 inline-block"></span>
                        <span class="text-xs font-bold text-gray-700 uppercase">ALTO (OBSTRUCCIÓN)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600"></span>
                        </span>
                        <span class="text-xs font-bold text-gray-700 uppercase">CRÍTICO (EMERGENCIA / SOS)</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 mt-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-0.5 border-t-2 border-dashed border-[#004d27] inline-block"></span>
                            <span class="text-[10px] font-bold text-gray-500 uppercase">TRAYECTORIA GPS (BASTÓN INTELIGENTE)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel de Filtros Derecho --}}
        <aside class="w-full lg:w-80 bg-white border-t lg:border-t-0 lg:border-l border-gray-200 h-1/3 lg:h-full flex flex-col p-6 z-20 shadow-2xl overflow-y-auto">
            <div class="flex items-center justify-between mb-6 shrink-0">
                <h2 class="font-bold text-lg text-gray-800 uppercase flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#006837]">filter_alt</span>
                    FILTROS DE MAPA
                </h2>
                <button type="button" onclick="resetFilters()" class="text-xs font-bold text-[#006837] hover:underline uppercase">
                    LIMPIAR
                </button>
            </div>

            <div class="flex-grow space-y-6">
                {{-- Nivel de Riesgo Checkboxes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-3 tracking-wider">NIVEL DE RIESGO</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors text-xs font-semibold uppercase">
                            <input type="checkbox" id="risk-bajo" checked class="rounded text-[#006837] focus:ring-[#006837] h-4 w-4" onchange="applyFilters()"/>
                            <span>BAJO</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors text-xs font-semibold uppercase">
                            <input type="checkbox" id="risk-medio" checked class="rounded text-[#006837] focus:ring-[#006837] h-4 w-4" onchange="applyFilters()"/>
                            <span>MODERADO</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors text-xs font-semibold uppercase">
                            <input type="checkbox" id="risk-alto" checked class="rounded text-[#006837] focus:ring-[#006837] h-4 w-4" onchange="applyFilters()"/>
                            <span>ALTO</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors text-xs font-semibold uppercase">
                            <input type="checkbox" id="risk-critico" checked class="rounded text-[#006837] focus:ring-[#006837] h-4 w-4" onchange="applyFilters()"/>
                            <span>SOS</span>
                        </label>
                    </div>
                </div>

                {{-- Tipo de Detección Dropdown --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-3 tracking-wider">TIPO DE DETECCIÓN</label>
                    <select id="detection-type" onchange="applyFilters()" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-xs font-bold uppercase focus:ring-[#006837] focus:border-[#006837]">
                        <option value="ALL">TODOS LOS MÉTODOS</option>
                        <option value="SENSOR">SENSOR ULTRASÓNICO</option>
                        <option value="GPS">GPS COORDENADAS</option>
                        <option value="MANUAL">REPORTE MANUAL (APP)</option>
                        <option value="IA">IA RECONOCIMIENTO</option>
                    </select>
                </div>

                {{-- Estado de Validación Botones --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-3 tracking-wider">ESTADO DE VALIDACIÓN</label>
                    <div class="flex flex-wrap gap-2" id="validation-buttons">
                        <button type="button" onclick="setValidationFilter('ALL', this)" class="validation-filter-btn px-4 py-2 bg-[#006837] text-white rounded-full text-xs font-bold uppercase shadow-sm">
                            TODOS
                        </button>
                        <button type="button" onclick="setValidationFilter('VERIFIED', this)" class="validation-filter-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-xs font-bold uppercase hover:bg-gray-200">
                            VERIFICADOS
                        </button>
                        <button type="button" onclick="setValidationFilter('PENDING', this)" class="validation-filter-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-xs font-bold uppercase hover:bg-gray-200">
                            PENDIENTES
                        </button>
                    </div>
                </div>

                {{-- Datos Activos Stats --}}
                <div class="bg-[#e8f7ef] p-4 rounded-xl border border-[#cbece0]">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-[#006837] text-sm animate-pulse">sensors</span>
                        <span class="text-xs font-bold text-[#004d27] uppercase tracking-wider">DATOS EN TIEMPO REAL</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-3xl font-extrabold text-[#006837] leading-none">{{ $alertasHoy }}</p>
                            <p class="text-[10px] font-bold text-gray-500 uppercase mt-1">ALERTAS HOY</p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-extrabold text-red-600 leading-none">{{ $zonasCriticas }}</p>
                            <p class="text-[10px] font-bold text-gray-500 uppercase mt-1">ZONAS CRÍTICAS</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 shrink-0 flex flex-col gap-2">
                <button type="button" onclick="toggleSmartCaneRoute()" id="btn-toggle-route" class="w-full py-3 bg-[#e8f7ef] text-[#006837] border-2 border-[#006837] rounded-lg font-bold text-xs uppercase shadow-sm hover:bg-[#cbece0] transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">timeline</span>
                    <span>TRAYECTORIA GPS (BASTÓN)</span>
                </button>
                <button type="button" onclick="startLocationReport()" class="w-full py-3.5 bg-red-600 text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-red-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add_alert</span>
                    <span>GENERAR ALERTA RIESGO</span>
                </button>
            </div>
        </aside>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 0;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .custom-popup .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }
        .custom-popup .leaflet-popup-close-button {
            top: 8px;
            right: 8px;
            color: #9ca3af !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];
        let smartCaneRoute;
        let routeVisible = true;
        let activeValidationFilter = 'ALL';
        
        // Cargar datos desde Laravel
        const rawReportes = @json($reportes);

        // Geometrías para la trayectoria GPS simulada (La Paz, Bolivia)
        const trajectoryCoords = [
            [-16.5028, -68.1345],
            [-16.5022, -68.1350],
            [-16.5015, -68.1358],
            [-16.5008, -68.1368],
            [-16.5000, -68.1375],
            [-16.4990, -68.1382],
            [-16.4982, -68.1390],
            [-16.4975, -68.1398]
        ];

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
        });

        // Inicializar Mapa
        function initMap() {
            // Centrado en el paseo del Prado, La Paz, Bolivia
            map = window.L.map('mapa-monitoreo-canvas', { 
                zoomControl: false 
            }).setView([-16.5000, -68.1375], 15);

                // Agregar control de zoom en la esquina superior derecha
                window.L.control.zoom({
                    position: 'topright'
                }).addTo(map);

                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Dibujar trayectoria del bastón inteligente
                smartCaneRoute = window.L.polyline(trajectoryCoords, {
                    color: '#004d27',
                    weight: 5,
                    opacity: 0.8,
                    dashArray: '10, 10'
                }).addTo(map);

                // Añadir puntos de inicio y fin del bastón
                window.L.circleMarker(trajectoryCoords[0], {
                    radius: 6,
                    fillColor: '#964900',
                    color: '#ffffff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map).bindPopup('INICIO DE TRAYECTO DE BASTÓN');

                window.L.circleMarker(trajectoryCoords[trajectoryCoords.length - 1], {
                    radius: 8,
                    fillColor: '#004d27',
                    color: '#ffffff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map).bindPopup('UBICACIÓN EN TIEMPO REAL DEL AFILIADO');

                // Renderizar los marcadores de la base de datos
                renderMarkers();
                
                // Configurar el evento de clic en el mapa para reportar obstáculo
                map.on('click', onMapClick);

                // Forzar recalculado de tamaño para evitar fondo gris parcial (Leaflet bug)
                setTimeout(() => {
                    map.invalidateSize();
                }, 250);

                window.addEventListener('resize', () => {
                    map.invalidateSize();
                });
        }

        // Determinar tipo de detección según la descripción
        function getDetectionType(reporte) {
            const desc = (reporte.descripcion || '').toLowerCase();
            const tipo = (reporte.tipo_obstaculo || '').toLowerCase();
            if (desc.includes('sensor') || desc.includes('ultrasónico') || tipo.includes('sensor') || tipo.includes('bastón')) {
                return 'SENSOR';
            }
            if (desc.includes('gps') || desc.includes('coordenada') || desc.includes('satélite')) {
                return 'GPS';
            }
            if (desc.includes('ia') || desc.includes('reconocimiento') || desc.includes('cámara') || desc.includes('inteligencia')) {
                return 'IA';
            }
            return 'MANUAL';
        }

        // Crear Marcadores
        function renderMarkers() {
            // Limpiar anteriores si existieran
            markers.forEach(m => m.markerInstance.remove());
            markers = [];

            rawReportes.forEach(reporte => {
                if (!reporte.latitud || !reporte.longitud) return;

                const lat = parseFloat(reporte.latitud);
                const lng = parseFloat(reporte.longitud);

                // Color e icono
                let color = '#10b981'; // BAJO
                let icon = 'location_on';
                let animationClass = '';

                if (reporte.nivel_riesgo === 'MEDIO') {
                    color = '#eab308'; // MODERADO
                } else if (reporte.nivel_riesgo === 'ALTO') {
                    color = '#f97316'; // ALTO
                } else if (reporte.nivel_riesgo === 'CRITICO') {
                    color = '#ef4444'; // CRÍTICO
                    icon = 'emergency';
                    if (reporte.estado === 'REPORTADO') {
                        animationClass = 'animate-pulse';
                    }
                }

                // Generar HTML personalizado del icono
                const iconHtml = `
                    <div class="relative flex items-center justify-center ${animationClass}">
                        ${reporte.nivel_riesgo === 'CRITICO' && reporte.estado === 'REPORTADO' ? 
                            '<div class="absolute w-8 h-8 bg-red-500 rounded-full opacity-40 animate-ping"></div>' : ''}
                        <span class="material-symbols-outlined text-[32px] drop-shadow-md select-none" style="color: ${color}; font-variation-settings: 'FILL' 1;">
                            ${icon}
                        </span>
                    </div>
                `;

                const customIcon = window.L.divIcon({
                    html: iconHtml,
                    className: 'custom-div-icon',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                // Estado badge
                const badgeColor = {
                    'REPORTADO': 'bg-yellow-100 text-yellow-800',
                    'ACTIVO': 'bg-blue-100 text-blue-800',
                    'DESCARTADO': 'bg-gray-200 text-gray-700',
                    'RESUELTO': 'bg-green-100 text-green-800'
                }[reporte.estado] || 'bg-gray-100 text-gray-800';

                // Imagen HTML
                let imageHtml = '';
                if (reporte.foto) {
                    imageHtml = `
                        <div class="w-full h-32 overflow-hidden bg-gray-100 border-b border-gray-100">
                            <img src="${'/storage/' + reporte.foto}" class="w-full h-full object-cover" alt="OBSTÁCULO">
                        </div>
                    `;
                }

                const popupContent = `
                    <div class="custom-popup rounded-lg overflow-hidden flex flex-col">
                        ${imageHtml}
                        <div class="p-4 uppercase text-xs">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-[#006837] font-mono text-[13px]">${reporte.codigo}</span>
                                <span class="px-2 py-0.5 rounded font-bold ${badgeColor}">${reporte.estado}</span>
                            </div>
                            <h4 class="font-extrabold text-gray-800 text-[13px] mb-1">${reporte.tipo_obstaculo}</h4>
                            <p class="text-gray-600 mb-2 font-medium leading-relaxed">${reporte.descripcion}</p>
                            
                            <div class="border-t border-gray-100 pt-2 space-y-1 text-[10px] text-gray-500">
                                <div><strong class="text-gray-700">UBICACIÓN:</strong> ${reporte.direccion_referencia || 'COORDENADAS GPS'}</div>
                                <div><strong class="text-gray-700">RIESGO:</strong> <span class="font-bold" style="color: ${color}">${reporte.nivel_riesgo}</span></div>
                                <div><strong class="text-gray-700">REPORTADO POR:</strong> ${reporte.reportado_por ? (reporte.reportado_por === {{ auth()->id() }} ? 'MÍ' : 'SISTEMA') : 'ANÓNIMO'}</div>
                                <div><strong class="text-gray-700">FECHA:</strong> ${new Date(reporte.created_at).toLocaleDateString()} ${new Date(reporte.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                            </div>
                        </div>
                    </div>
                `;

                const marker = window.L.marker([lat, lng], { icon: customIcon })
                    .bindPopup(popupContent, { maxWidth: 280, className: 'custom-popup-container' });

                marker.addTo(map);

                markers.push({
                    reporte: reporte,
                    markerInstance: marker,
                    detectionType: getDetectionType(reporte)
                });
            });
        }

        // Aplicar Filtros Dinámicos
        function applyFilters() {
            const riskBajo = document.getElementById('risk-bajo').checked;
            const riskMedio = document.getElementById('risk-medio').checked;
            const riskAlto = document.getElementById('risk-alto').checked;
            const riskCritico = document.getElementById('risk-critico').checked;
            const detectionSelect = document.getElementById('detection-type').value;

            markers.forEach(item => {
                const rep = item.reporte;
                let show = true;

                // 1. Filtrar por riesgo
                if (rep.nivel_riesgo === 'BAJO' && !riskBajo) show = false;
                if (rep.nivel_riesgo === 'MEDIO' && !riskMedio) show = false;
                if (rep.nivel_riesgo === 'ALTO' && !riskAlto) show = false;
                if (rep.nivel_riesgo === 'CRITICO' && !riskCritico) show = false;

                // 2. Filtrar por detección
                if (detectionSelect !== 'ALL' && item.detectionType !== detectionSelect) show = false;

                // 3. Filtrar por estado de validación
                if (activeValidationFilter === 'VERIFIED' && !['ACTIVO', 'RESUELTO'].includes(rep.estado)) show = false;
                if (activeValidationFilter === 'PENDING' && rep.estado !== 'REPORTADO') show = false;

                // Aplicar visibilidad
                if (show) {
                    if (!map.hasLayer(item.markerInstance)) {
                        item.markerInstance.addTo(map);
                    }
                } else {
                    if (map.hasLayer(item.markerInstance)) {
                        item.markerInstance.remove();
                    }
                }
            });
        }

        // Filtro de Estado de Validación (Botones)
        function setValidationFilter(filter, buttonElement) {
            activeValidationFilter = filter;

            // Cambiar estilos de botones activos
            document.querySelectorAll('.validation-filter-btn').forEach(btn => {
                btn.classList.remove('bg-[#006837]', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-600');
            });

            buttonElement.classList.remove('bg-gray-100', 'text-gray-600');
            buttonElement.classList.add('bg-[#006837]', 'text-white');

            applyFilters();
        }

        // Reiniciar Filtros
        function resetFilters() {
            document.getElementById('risk-bajo').checked = true;
            document.getElementById('risk-medio').checked = true;
            document.getElementById('risk-alto').checked = true;
            document.getElementById('risk-critico').checked = true;
            document.getElementById('detection-type').value = 'ALL';

            const todosBtn = document.querySelector('.validation-filter-btn');
            setValidationFilter('ALL', todosBtn);
        }

        // Toggle Trayectoria
        function toggleSmartCaneRoute() {
            routeVisible = !routeVisible;
            const btn = document.getElementById('btn-toggle-route');

            if (routeVisible) {
                smartCaneRoute.addTo(map);
                btn.classList.remove('bg-gray-100', 'text-gray-500', 'border-gray-300');
                btn.classList.add('bg-[#e8f7ef]', 'text-[#006837]', 'border-[#006837]');
            } else {
                smartCaneRoute.remove();
                btn.classList.remove('bg-[#e8f7ef]', 'text-[#006837]', 'border-[#006837]');
                btn.classList.add('bg-gray-100', 'text-gray-500', 'border-gray-300');
            }
        }

        // Gestión de Reporte con SweetAlert2
        let reportingActive = false;

        function startLocationReport() {
            reportingActive = true;
            document.getElementById('map-instruction').classList.remove('hidden');
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'HAGA CLIC EN EL MAPA PARA INDICAR LA UBICACIÓN DEL RIESGO',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true
            });
        }

        function onMapClick(e) {
            if (!reportingActive) return;

            reportingActive = false;
            document.getElementById('map-instruction').classList.add('hidden');

            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            // Abrir SweetAlert2 con formulario de registro
            Swal.fire({
                title: 'REPORTAR OBSTÁCULO DE RIESGO',
                html: `
                    <form id="form-reportar-mapa" action="{{ route('reportes-obstaculos.store') }}" method="POST" enctype="multipart/form-data" class="text-left space-y-3 p-1">
                        @csrf
                        <input type="hidden" name="latitud" value="${lat}">
                        <input type="hidden" name="longitud" value="${lng}">
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">TIPO DE OBSTÁCULO *</label>
                            <select name="tipo_obstaculo" required class="w-full border rounded px-3 py-2 text-xs font-bold uppercase focus:ring-[#006837]">
                                <option value="OBRA EN LA VÍA">OBRA EN LA VÍA</option>
                                <option value="POSTE CAÍDO / DAÑADO">POSTE CAÍDO / DAÑADO</option>
                                <option value="VEHÍCULO MAL ESTACIONADO">VEHÍCULO MAL ESTACIONADO</option>
                                <option value="BACHES / AGUJEROS">BACHES / AGUJEROS</option>
                                <option value="COMERCIO INFORMAL">COMERCIO INFORMAL</option>
                                <option value="ALERTA SOS (EMERGENCIA)">ALERTA SOS (EMERGENCIA)</option>
                                <option value="OTRO">OTRO OBSTÁCULO</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">NIVEL DE RIESGO *</label>
                            <select name="nivel_riesgo" required class="w-full border rounded px-3 py-2 text-xs font-bold uppercase focus:ring-[#006837]">
                                <option value="BAJO">BAJO (PRECAUCIÓN MENOR)</option>
                                <option value="MEDIO">MEDIO (PRECAUCIÓN REGULAR)</option>
                                <option value="ALTO">ALTO (VÍA OBSTRUIDA)</option>
                                <option value="CRITICO">CRÍTICO (PELIGRO EXTREMO / SOS)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">DIRECCIÓN / REFERENCIA *</label>
                            <input type="text" name="direccion_referencia" required placeholder="E.G. AV. 16 DE JULIO, ESQ. CALLE BUENO" class="w-full border rounded px-3 py-2 text-xs font-bold uppercase focus:ring-[#006837]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">DESCRIPCIÓN DEL DETALLE *</label>
                            <textarea name="descripcion" required rows="2" placeholder="INDIQUE DETALLES ADICIONALES DEL OBSTÁCULO" class="w-full border rounded px-3 py-2 text-xs font-bold uppercase focus:ring-[#006837]"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">FOTO EVIDENCIA (OPCIONAL)</label>
                            <input type="file" name="foto" accept="image/*" class="w-full border rounded px-2 py-1.5 text-xs focus:ring-[#006837]">
                        </div>

                        <div class="bg-gray-50 p-2.5 rounded border border-gray-200 text-[10px] text-gray-500 font-bold uppercase">
                            COORDENADAS: ${lat}, ${lng}
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonColor: '#006837',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'ENVIAR REPORTE',
                cancelButtonText: 'CANCELAR',
                reverseButtons: true,
                preConfirm: () => {
                    const form = document.getElementById('form-reportar-mapa');
                    
                    // Validar campos requeridos
                    const tipo = form.querySelector('[name="tipo_obstaculo"]').value;
                    const desc = form.querySelector('[name="descripcion"]').value;
                    const dir = form.querySelector('[name="direccion_referencia"]').value;

                    if (!tipo || !desc || !dir) {
                        Swal.showValidationMessage('DEBE COMPLETAR TODOS LOS CAMPOS OBLIGATORIOS (*)');
                        return false;
                    }

                    return form;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    // Enviar formulario
                    result.value.submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
