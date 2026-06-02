<aside class="fixed left-0 top-0 bottom-0 z-40 w-72 bg-white border-r border-gray-100 shadow-xl hidden lg:flex flex-col">

    {{-- Logo / nombre --}}
    <div class="p-6 border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="flex flex-col gap-2">
            <div class="w-16 h-16 rounded-2xl bg-[#006837] flex items-center justify-center text-white shadow-lg overflow-hidden">
                <span class="font-bold text-xl">IBC</span>
            </div>

            <div>
                <h2 class="text-base font-bold text-[#004d27] leading-tight">
                    Instituto Boliviano de la Ceguera
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Portal de Gestión
                </p>
            </div>
        </a>
    </div>

    {{-- Menú --}}
    <nav class="flex-1 py-4 space-y-1 overflow-y-auto">

        <a href="{{ route('dashboard') }}"
           class="ibc-sidebar-link {{ request()->routeIs('dashboard') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Inicio</span>
        </a>

        @if(auth()->user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR'))
        <a href="{{ route('personas-no-videntes.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('personas-no-videntes.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">person_search</span>
            <span>Personas No Videntes</span>
        </a>

        <a href="{{ route('afiliaciones.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('afiliaciones.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">assignment_ind</span>
            <span>Afiliaciones</span>
        </a>

        <a href="{{ route('beneficios.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('beneficios.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">volunteer_activism</span>
            <span>Beneficios</span>
        </a>

        <a href="{{ route('asignacion-beneficios.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('asignacion-beneficios.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">payments</span>
            <span>Asignaciones</span>
        </a>

        <a href="{{ route('capacitaciones.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('capacitaciones.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">school</span>
            <span>Capacitaciones</span>
        </a>

        <a href="{{ route('tipos-capacitacion.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('tipos-capacitacion.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">category</span>
            <span>Tipos de Capacitación</span>
        </a>

        <a href="{{ route('instituciones-externas.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('instituciones-externas.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">apartment</span>
            <span>Instituciones Externas</span>
        </a>

        <a href="{{ route('tramites-instituciones.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('tramites-instituciones.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">description</span>
            <span>Trámites</span>
        </a>

        <a href="{{ route('accesos-salud.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('accesos-salud.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">health_and_safety</span>
            <span>Accesos a Salud</span>
        </a>

        <a href="{{ route('niveles-seguridad.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('niveles-seguridad.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">shield</span>
            <span>Niveles de Seguridad</span>
        </a>
        @endif

        @if(auth()->user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'))
        <div class="px-5 pt-4 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">
            Módulo 7
        </div>

        <a href="{{ route('rutas.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('rutas.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">map</span>
            <span>Rutas Seguras</span>
        </a>

        <a href="{{ route('obstaculos.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('obstaculos.*') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">warning</span>
            <span>Obstáculos</span>
        </a>

        @endif

        @if(auth()->user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO', 'VOLUNTARIO'))
        <div class="px-5 pt-4 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">
            Observatorio
        </div>

        @if(auth()->user()->tieneRol('SUPERADMINISTRADOR', 'ADMINISTRADOR', 'TECNICO'))
        <a href="{{ route('reportes-obstaculos.index') }}"
           class="ibc-sidebar-link {{ request()->routeIs('reportes-obstaculos.index', 'reportes-obstaculos.show') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">visibility</span>
            <span>Obstáculos Urbanos</span>
        </a>
        @endif

        <a href="{{ route('reportes-obstaculos.mapa-monitoreo') }}"
           class="ibc-sidebar-link {{ request()->routeIs('reportes-obstaculos.mapa-monitoreo') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">map</span>
            <span>Mapa de Riesgo</span>
        </a>

        <a href="{{ route('reportes-obstaculos.create') }}"
           class="ibc-sidebar-link {{ request()->routeIs('reportes-obstaculos.create') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">report</span>
            <span>Reportar Obstáculo</span>
        </a>

        <a href="{{ route('reportes-obstaculos.mis-reportes') }}"
           class="ibc-sidebar-link {{ request()->routeIs('reportes-obstaculos.mis-reportes') ? 'ibc-sidebar-active' : '' }}">
            <span class="material-symbols-outlined">folder_shared</span>
            <span>Mis Reportes</span>
        </a>
        @endif
    </nav>

    {{-- Cerrar sesión --}}
    <div class="p-4 border-t border-gray-100">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-xl">
                <span class="material-symbols-outlined">logout</span>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

<header class="fixed top-0 left-0 lg:left-72 right-0 z-30 h-16 bg-white border-b border-gray-100 shadow-sm flex items-center justify-between px-6">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-[#004d27]">menu</span>
        <span class="font-bold text-[#004d27]">Dashboard</span>
    </div>

    <div class="flex items-center gap-3">
        <span class="hidden sm:block text-sm text-gray-600">Gestión Institucional</span>
        <div class="w-10 h-10 rounded-full bg-[#e8f7ef] border-2 border-[#006837] flex items-center justify-center text-[#006837] font-bold">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</header>

<style>
    .ibc-sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 8px;
        padding: 12px 16px;
        border-radius: 14px;
        color: #4b5563;
        font-size: 14px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .ibc-sidebar-link:hover {
        background: #f3f7f4;
        color: #006837;
    }

    .ibc-sidebar-active {
        background: #e8f7ef;
        color: #004d27;
        border-left: 4px solid #006837;
    }

    .ibc-sidebar-link .material-symbols-outlined {
        font-size: 22px;
    }
</style>
