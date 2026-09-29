<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

    <div class="min-h-screen bg-[#f9f9fc] py-8">
        <div class="max-w-7xl mx-auto px-6 space-y-8">

            {{-- Encabezado --}}
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-[#004d27]">DASHBOARD ADMINISTRATIVO</h1>
                        <p class="text-gray-600 mt-2">Análisis integral del Sistema IBC en tiempo real</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Actualizado: <span id="fecha-actualizacion"></span></p>
                    </div>
                </div>
            </section>

            {{-- KPIs Principales (Cards) --}}
            <section>
                <h2 class="text-xl font-bold text-[#004d27] mb-4">INDICADORES PRINCIPALES</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4" id="kpis-container">
                    <!-- Se llenan dinámicamente -->
                </div>
            </section>

            {{-- Gráficos principales (2 columnas) --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Distribución por Estado (Pastel) --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">DISTRIBUCIÓN POR ESTADO</h3>
                    <div id="chart-estado"></div>
                </div>

                {{-- Género (Donut) --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">GÉNERO DE PERSONAS</h3>
                    <div id="chart-genero"></div>
                </div>
            </section>

            {{-- Grado de Discapacidad --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">GRADO DE DISCAPACIDAD</h3>
                    <div id="chart-discapacidad"></div>
                </div>

                {{-- Afiliaciones por Tipo (Barras) --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">AFILIACIONES POR TIPO</h3>
                    <div id="chart-afiliaciones-tipo"></div>
                </div>
            </section>

            {{-- Registros por Mes (Línea) --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-[#004d27] mb-4">REGISTROS POR MES (ÚLTIMOS 6 MESES)</h3>
                <div id="chart-registros-mes"></div>
            </section>

            {{-- Distribución por Ciudad y Beneficios --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">DISTRIBUCIÓN POR CIUDAD</h3>
                    <div id="chart-ciudades"></div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">TOP 5 BENEFICIOS ASIGNADOS</h3>
                    <div id="chart-beneficios"></div>
                </div>
            </section>

            {{-- Trámites por Estado --}}
            <section class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-[#004d27] mb-4">ESTADO DE TRÁMITES</h3>
                <div id="chart-tramites"></div>
            </section>

            {{-- Tablas de Resumen --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Últimas Personas --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">ÚLTIMAS PERSONAS REGISTRADAS</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-600">CÓDIGO</th>
                                    <th class="px-4 py-2 text-left text-gray-600">NOMBRE</th>
                                    <th class="px-4 py-2 text-left text-gray-600">ESTADO</th>
                                    <th class="px-4 py-2 text-left text-gray-600">FECHA</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-personas">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Últimas Afiliaciones --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-[#004d27] mb-4">ÚLTIMAS AFILIACIONES</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-600">PERSONA</th>
                                    <th class="px-4 py-2 text-left text-gray-600">ESTADO</th>
                                    <th class="px-4 py-2 text-left text-gray-600">FECHA</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-afiliaciones">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <script>
        // Función auxiliar para obtener color según estado
        function getColorEstado(estado) {
            const colores = {
                'ACTIVO': '#10b981',
                'INACTIVO': '#6b7280',
                'PENDIENTE': '#f59e0b',
                'SUSPENDIDO': '#ef4444',
                'EGRESADO': '#3b82f6',
                'ACTIVA': '#10b981',
                'INACTIVA': '#6b7280',
                'VENCIDA': '#f97316',
                'RESUELTO': '#10b981',
                'EN PROCESO': '#3b82f6',
                'RECHAZADO': '#ef4444',
                'ABANDONADO': '#6b7280',
            };
            return colores[estado] || '#6b7280';
        }

        // Función para obtener badge de estado
        function getBadgeEstado(estado) {
            const colores = {
                'ACTIVO': 'bg-green-100 text-green-800',
                'INACTIVO': 'bg-gray-100 text-gray-800',
                'PENDIENTE': 'bg-yellow-100 text-yellow-800',
                'SUSPENDIDO': 'bg-red-100 text-red-800',
                'EGRESADO': 'bg-blue-100 text-blue-800',
                'ACTIVA': 'bg-green-100 text-green-800',
                'INACTIVA': 'bg-gray-100 text-gray-800',
                'VENCIDA': 'bg-orange-100 text-orange-800',
            };
            return colores[estado] || 'bg-gray-100 text-gray-800';
        }

        // Cargar datos desde la API
        fetch('{{ route("dashboard.estadisticas") }}')
            .then(response => response.json())
            .then(data => {
                console.log('Datos del dashboard:', data);

                // Actualizar fecha
                document.getElementById('fecha-actualizacion').textContent = 
                    new Date().toLocaleDateString('es-BO', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                // ========== KPIs ==========
                renderKPIs(data.kpis);

                // ========== Gráficos ==========

                // Distribución por Estado
                if (data.distribucionPorEstado.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-estado"), {
                        series: data.distribucionPorEstado.series,
                        chart: { type: "pie" },
                        labels: data.distribucionPorEstado.labels,
                        colors: data.distribucionPorEstado.colors,
                        responsive: [{ breakpoint: 480, options: { chart: { width: 300 }, legend: { position: "bottom" } } }],
                    }).render();
                }

                // Género
                if (data.generoPersonas.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-genero"), {
                        series: data.generoPersonas.series,
                        chart: { type: "donut" },
                        labels: data.generoPersonas.labels,
                        colors: ["#2d6a27", "#d946ef"],
                        responsive: [{ breakpoint: 480, options: { chart: { width: 300 } } }],
                    }).render();
                }

                // Grado de Discapacidad
                if (data.gradoDiscapacidad.labels && data.gradoDiscapacidad.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-discapacidad"), {
                        series: [{
                            name: "Cantidad",
                            data: data.gradoDiscapacidad.series,
                        }],
                        chart: { type: "bar", height: 300 },
                        plotOptions: { bar: { horizontal: true } },
                        xaxis: { categories: data.gradoDiscapacidad.labels },
                        colors: ["#f0b429"],
                    }).render();
                }

                // Afiliaciones por Tipo
                if (data.afiliacionesPorTipo.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-afiliaciones-tipo"), {
                        series: [{
                            name: "Cantidad",
                            data: data.afiliacionesPorTipo.series,
                        }],
                        chart: { type: "bar", height: 350 },
                        plotOptions: { bar: { columnWidth: "55%", distributed: true } },
                        dataLabels: { enabled: true },
                        xaxis: { categories: data.afiliacionesPorTipo.labels },
                        colors: ["#2d6a27"],
                    }).render();
                }

                // Registros por Mes
                if (data.registrosPorMes.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-registros-mes"), {
                        series: [{
                            name: "Registros",
                            data: data.registrosPorMes.series,
                        }],
                        chart: { type: "line", height: 350, sparkline: { enabled: false } },
                        stroke: { curve: "smooth", width: 3 },
                        xaxis: { categories: data.registrosPorMes.labels },
                        colors: ["#2d6a27"],
                        fill: { type: "gradient", gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
                        dataLabels: { enabled: true, formatter: val => val.toFixed(0) },
                    }).render();
                }

                // Distribución por Ciudad
                if (data.distribucionPorCiudad.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-ciudades"), {
                        series: [{
                            name: "Personas",
                            data: data.distribucionPorCiudad.series,
                        }],
                        chart: { type: "bar", height: 350 },
                        xaxis: { categories: data.distribucionPorCiudad.labels },
                        colors: ["#f0b429"],
                        dataLabels: { enabled: true },
                    }).render();
                }

                // Top Beneficios
                if (data.beneficiosMasAsignados.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-beneficios"), {
                        series: [{
                            name: "Asignaciones",
                            data: data.beneficiosMasAsignados.series,
                        }],
                        chart: { type: "bar", height: 350 },
                        plotOptions: { bar: { columnWidth: "55%" } },
                        xaxis: { categories: data.beneficiosMasAsignados.labels },
                        colors: ["#2d6a27"],
                        dataLabels: { enabled: true },
                    }).render();
                }

                // Trámites por Estado
                if (data.tramitesPorEstado.labels.length > 0) {
                    new ApexCharts(document.querySelector("#chart-tramites"), {
                        series: data.tramitesPorEstado.series,
                        chart: { type: "pie" },
                        labels: data.tramitesPorEstado.labels,
                        colors: ["#2d6a27", "#3b82f6", "#10b981", "#ef4444"],
                    }).render();
                }

                // ========== Tablas ==========

                // Tabla de Personas
                const personasHTML = data.ultimasPersonas.map(p => `
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-bold">${p.codigo}</td>
                        <td class="px-4 py-3">${p.nombre}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold ${getBadgeEstado(p.estado)}">
                                ${p.estado}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">${p.fecha}</td>
                    </tr>
                `).join('');
                document.getElementById('tabla-personas').innerHTML = personasHTML || 
                    '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">📋 SIN REGISTROS</td></tr>';

                // Tabla de Afiliaciones
                const afiliacionesHTML = data.ultimasAfiliaciones.map(a => `
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3">${a.persona}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold ${getBadgeEstado(a.estado)}">
                                ${a.estado}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">${a.fecha}</td>
                    </tr>
                `).join('');
                document.getElementById('tabla-afiliaciones').innerHTML = afiliacionesHTML || 
                    '<tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">📋 SIN REGISTROS</td></tr>';
            })
            .catch(error => console.error('Error cargando datos:', error));

        // Renderizar KPIs
        function renderKPIs(kpis) {
            const kpiData = [
                { label: 'PERSONAS REGISTRADAS', value: kpis.totalPersonas, icon: 'person', color: '#2d6a27' },
                { label: 'ACTIVAS', value: kpis.personasActivas, icon: 'check_circle', color: '#10b981' },
                { label: 'AFILIACIONES', value: kpis.afiliacionesActivas, icon: 'assignment_ind', color: '#f0b429' },
                { label: 'BENEFICIOS', value: kpis.beneficiosAsignados, icon: 'card_giftcard', color: '#3b82f6' },
                { label: 'TRÁMITES PENDIENTES', value: kpis.tramitesPendientes, icon: 'pending_actions', color: '#ef4444' },
            ];

            const container = document.getElementById('kpis-container');
            container.innerHTML = kpiData.map(kpi => `
                <div class="bg-white rounded-lg shadow p-6 border-l-4" style="border-color: ${kpi.color}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-xs font-semibold">${kpi.label}</p>
                            <p class="text-3xl font-bold mt-2" style="color: ${kpi.color}">${kpi.value}</p>
                        </div>
                        <span class="material-symbols-outlined text-5xl opacity-10" style="color: ${kpi.color}">
                            ${kpi.icon}
                        </span>
                    </div>
                </div>
            `).join('');
        }
    </script>
</x-app-layout>
