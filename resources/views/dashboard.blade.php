<x-app-layout>
    <div class="min-h-screen bg-[#f9f9fc] py-8">
        <div class="max-w-7xl mx-auto px-6 space-y-8">

            {{-- Bienvenida --}}
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#006837] to-[#0b6d3b] text-white p-10 shadow-lg">
                <div class="relative z-10 max-w-3xl">
                    <h1 class="text-4xl font-bold mb-4">
                        Bienvenido al Portal IBC
                    </h1>

                    <p class="text-green-100 text-base leading-relaxed mb-6">
                        Sistema integral de gestión para el Instituto Boliviano de la Ceguera.
                        Acceda a herramientas para la inclusión, seguimiento institucional y rutas seguras.
                    </p>

                    <a href="{{ route('rutas.index') }}"
                       class="inline-flex items-center gap-2 bg-[#ff8928] text-[#311300] px-6 py-3 rounded-2xl font-semibold shadow hover:opacity-90 transition">
                        Ver rutas seguras
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </a>
                </div>

                <div class="absolute right-10 top-8 opacity-20">
                    <span class="material-symbols-outlined text-[180px]">visibility</span>
                </div>
            </section>

            {{-- Módulos de gestión --}}
            <section>
                <div class="flex items-center gap-4 mb-6">
                    <h2 class="text-2xl font-bold text-[#004d27]">
                        Módulos de Gestión
                    </h2>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <a href="{{ route('personas-no-videntes.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">person_search</span>
                        <h3>Personas No Videntes</h3>
                    </a>

                    <a href="{{ route('afiliaciones.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">assignment_ind</span>
                        <h3>Afiliaciones</h3>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">contact_page</span>
                        <h3>Mi Perfil</h3>
                    </a>

                    <a href="{{ route('beneficios.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">volunteer_activism</span>
                        <h3>Beneficios Sociales</h3>
                    </a>

                    <a href="{{ route('asignacion-beneficios.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">payments</span>
                        <h3>Asignaciones de Beneficios</h3>
                    </a>

                    <a href="{{ route('capacitaciones.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">school</span>
                        <h3>Capacitaciones</h3>
                    </a>

                    <a href="{{ route('tipos-capacitacion.index') }}" class="ibc-card-admin">
                        <span class="material-symbols-outlined ibc-icon-admin">category</span>
                        <h3>Tipos de Capacitación</h3>
                    </a>

                    <div class="border-2 border-dashed border-gray-200 rounded-2xl bg-white p-6 flex flex-col items-center justify-center text-center text-gray-400 min-h-[150px]">
                        <span class="material-symbols-outlined text-4xl mb-3">add</span>
                        <h3 class="font-semibold">Nuevo Módulo</h3>
                    </div>
                </div>
            </section>

            {{-- Módulo 7 --}}
            <section class="bg-[#fff7ef] border-2 border-[#ff8928]/30 rounded-3xl p-8 shadow-inner">
                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-[#ff8928] text-white p-3 rounded-2xl shadow">
                        <span class="material-symbols-outlined text-3xl">map</span>
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-[#964900]">
                            Rutas Seguras
                        </h2>
                        <p class="text-sm text-gray-600">
                            Monitoreo y gestión de accesibilidad urbana para personas con discapacidad visual.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <a href="{{ route('rutas.index') }}" class="ibc-card-route">
                        <div class="flex justify-between items-start mb-4">
                            <span class="material-symbols-outlined text-[#964900] text-4xl">route</span>
                            <span class="material-symbols-outlined text-gray-300">arrow_forward</span>
                        </div>
                        <h3>Rutas Seguras</h3>
                        <p>Visualice y gestione trayectos verificados para una navegación con menor riesgo.</p>
                    </a>

                    <a href="{{ route('obstaculos.index') }}" class="ibc-card-route">
                        <div class="flex justify-between items-start mb-4">
                            <span class="material-symbols-outlined text-[#964900] text-4xl">warning</span>
                            <span class="material-symbols-outlined text-gray-300">arrow_forward</span>
                        </div>
                        <h3>Obstáculos</h3>
                        <p>Reporte y seguimiento de barreras arquitectónicas y obstáculos temporales.</p>
                    </a>

                    <a href="{{ route('niveles-seguridad.index') }}" class="ibc-card-route">
                        <div class="flex justify-between items-start mb-4">
                            <span class="material-symbols-outlined text-[#964900] text-4xl">shield</span>
                            <span class="material-symbols-outlined text-gray-300">arrow_forward</span>
                        </div>
                        <h3>Niveles de Seguridad</h3>
                        <p>Configuración de parámetros de riesgo y evaluación de tramos urbanos.</p>
                    </a>

                </div>
            </section>

            {{-- Footer visual --}}
            <footer class="text-center py-8 text-gray-500">
                <div class="flex items-center justify-center gap-2 text-[#004d27] font-bold tracking-widest">
                    <span class="material-symbols-outlined">visibility</span>
                    IBC BOLIVIA
                </div>

                <p class="text-xs mt-4 uppercase tracking-wider">
                    © 2026 Instituto Boliviano de la Ceguera. Accesibilidad e inclusión.
                </p>
            </footer>
        </div>
    </div>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .ibc-card-admin {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            min-height: 150px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 14px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: all .2s ease;
        }

        .ibc-card-admin:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0,0,0,.08);
        }

        .ibc-card-admin h3 {
            color: #1f2937;
            font-weight: 700;
            font-size: 15px;
        }

        .ibc-icon-admin {
            background: #e8f7ef;
            color: #006837;
            width: 60px;
            height: 60px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .ibc-card-route {
            background: white;
            border: 1px solid #ffd7b0;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: all .2s ease;
        }

        .ibc-card-route:hover {
            border-color: #ff8928;
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(150, 73, 0, .12);
        }

        .ibc-card-route h3 {
            color: #1f2937;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .ibc-card-route p {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</x-app-layout>
