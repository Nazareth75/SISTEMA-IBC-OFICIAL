<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Bienvenida -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">{{ __('¡Bienvenido!') }}</h3>
                    <p>{{ __('Selecciona una opción a continuación para acceder a los módulos del sistema') }}</p>
                </div>
            </div>

            <!-- Grid de Botones de Navegación -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Botón: Personas No Videntes -->
                <a href="{{ route('personas-no-videntes.index') }}" 
                   class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-blue-500 hover:border-blue-700"
                   aria-label="Acceder a gestión de Personas No Videntes">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center mb-3">
                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"></path>
                            </svg>
                            <h4 class="text-lg font-semibold group-hover:text-blue-600 transition-colors">
                                {{ __('Personas No Videntes') }}
                            </h4>
                        </div>
                        <p class="text-gray-600 text-sm">{{ __('Gestiona la información de personas no videntes') }}</p>
                        <div class="mt-4">
                            <span class="inline-flex items-center text-blue-500 group-hover:text-blue-700">
                                {{ __('Acceder') }}
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Botón: Afiliaciones -->
                <a href="{{ route('afiliaciones.index') }}" 
                   class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-green-500 hover:border-green-700"
                   aria-label="Acceder a gestión de Afiliaciones">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center mb-3">
                            <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-lg font-semibold group-hover:text-green-600 transition-colors">
                                {{ __('Afiliaciones') }}
                            </h4>
                        </div>
                        <p class="text-gray-600 text-sm">{{ __('Gestiona afiliaciones y membresías') }}</p>
                        <div class="mt-4">
                            <span class="inline-flex items-center text-green-500 group-hover:text-green-700">
                                {{ __('Acceder') }}
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Botón: Mi Perfil -->
                <a href="{{ route('profile.edit') }}" 
                   class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-purple-500 hover:border-purple-700"
                   aria-label="Acceder a mi perfil">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center mb-3">
                            <svg class="w-8 h-8 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-lg font-semibold group-hover:text-purple-600 transition-colors">
                                {{ __('Mi Perfil') }}
                            </h4>
                        </div>
                        <p class="text-gray-600 text-sm">{{ __('Edita tu información personal y configuración') }}</p>
                        <div class="mt-4">
                            <span class="inline-flex items-center text-purple-500 group-hover:text-purple-700">
                                {{ __('Acceder') }}
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>

                @if(auth()->user()?->esAdministrador())
                    <!-- Botón: Beneficios Sociales -->
                    <a href="{{ route('beneficios.index') }}" 
                       class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-orange-500 hover:border-orange-700"
                       aria-label="Acceder a gestión de Beneficios Sociales">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 text-orange-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="text-lg font-semibold group-hover:text-orange-600 transition-colors">
                                    {{ __('Beneficios Sociales') }}
                                </h4>
                            </div>
                            <p class="text-gray-600 text-sm">{{ __('Catálogo y gestión de beneficios') }}</p>
                            <div class="mt-4">
                                <span class="inline-flex items-center text-orange-500 group-hover:text-orange-700">
                                    {{ __('Acceder') }}
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Botón: Asignaciones de Beneficios -->
                    <a href="{{ route('asignacion-beneficios.index') }}" 
                       class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-red-500 hover:border-red-700"
                       aria-label="Acceder a gestión de Asignaciones de Beneficios">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <h4 class="text-lg font-semibold group-hover:text-red-600 transition-colors">
                                    {{ __('Asignaciones de Beneficios') }}
                                </h4>
                            </div>
                            <p class="text-gray-600 text-sm">{{ __('Asigna y gestiona beneficios a afiliados') }}</p>
                            <div class="mt-4">
                                <span class="inline-flex items-center text-red-500 group-hover:text-red-700">
                                    {{ __('Acceder') }}
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Botón: Capacitaciones --}}
                    <a href="{{ route('capacitaciones.index') }}" 
                    class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-indigo-500 hover:border-indigo-700"
                    aria-label="Acceder a gestión de Capacitaciones">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                <h4 class="text-lg font-semibold group-hover:text-indigo-600 transition-colors">
                                    {{ __('Capacitaciones') }}
                                </h4>
                            </div>

                            <p class="text-gray-600 text-sm">
                                {{ __('Gestiona cursos, responsables e inscripciones de personas no videntes') }}
                            </p>

                            <div class="mt-4">
                                <span class="inline-flex items-center text-indigo-500 group-hover:text-indigo-700">
                                    {{ __('Acceder') }}
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Botón: Tipos de Capacitación --}}
                    <a href="{{ route('tipos-capacitacion.index') }}" 
                    class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-teal-500 hover:border-teal-700"
                    aria-label="Acceder a catálogo de Tipos de Capacitación">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 text-teal-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 11H5m14 0a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                <h4 class="text-lg font-semibold group-hover:text-teal-600 transition-colors">
                                    {{ __('Tipos de Capacitación') }}
                                </h4>
                            </div>

                            <p class="text-gray-600 text-sm">
                                {{ __('Administra el catálogo de tipos de capacitación disponibles') }}
                            </p>

                            <div class="mt-4">
                                <span class="inline-flex items-center text-teal-500 group-hover:text-teal-700">
                                    {{ __('Acceder') }}
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endif

                {{-- ── MÓDULO 7: RUTAS SEGURAS (Admin + Técnico) ─────────────── --}}
                @if(auth()->user()?->puedeCrearRuta())

                    {{-- Separador de sección --}}
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 mt-2">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 border-t border-gray-200"></div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest px-2">
                                MÓDULO 7 — RUTAS SEGURAS
                            </span>
                            <div class="flex-1 border-t border-gray-200"></div>
                        </div>
                    </div>

                    {{-- Botón: Rutas Seguras --}}
                    <a href="{{ route('rutas.index') }}"
                        class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-green-600 hover:border-green-800"
                        aria-label="Acceder a gestión de Rutas Seguras">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-route w-8 h-8 mr-3 text-2xl" style="color:#2d6a27"></i>
                                <h4 class="text-lg font-semibold group-hover:text-green-700 transition-colors uppercase">
                                    RUTAS SEGURAS
                                </h4>
                            </div>
                            <p class="text-gray-600 text-sm uppercase">
                                GESTIONA Y APRUEBA RUTAS URBANAS SEGURAS PARA AFILIADOS
                            </p>
                            <div class="mt-4">
                                <span class="inline-flex items-center font-semibold text-sm" style="color:#2d6a27">
                                    ACCEDER
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Botón: Gestión de Obstáculos --}}
                    <a href="{{ route('obstaculos.index') }}"
                        class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-yellow-500 hover:border-yellow-700"
                        aria-label="Acceder a gestión de Obstáculos">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-exclamation-triangle w-8 h-8 mr-3 text-2xl" style="color:#f0b429"></i>
                                <h4 class="text-lg font-semibold group-hover:text-yellow-600 transition-colors uppercase">
                                    OBSTÁCULOS
                                </h4>
                            </div>
                            <p class="text-gray-600 text-sm uppercase">
                                VALIDAR Y DAR SEGUIMIENTO A OBSTÁCULOS REPORTADOS EN RUTAS
                            </p>
                            <div class="mt-4">
                                <span class="inline-flex items-center text-yellow-600 group-hover:text-yellow-700 font-semibold text-sm">
                                    ACCEDER
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                @endif

                {{-- Niveles de Seguridad: visible para Admin y Técnico --}}
                @if(auth()->user()?->puedeGestionarNivelesSeguridad() || auth()->user()?->esTecnico())
                    <a href="{{ route('niveles-seguridad.index') }}"
                        class="group bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-300 border-l-4 border-emerald-500 hover:border-emerald-700"
                        aria-label="Acceder al catálogo de Niveles de Seguridad">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-shield-alt w-8 h-8 mr-3 text-2xl text-emerald-600"></i>
                                <h4 class="text-lg font-semibold group-hover:text-emerald-700 transition-colors uppercase">
                                    NIVELES DE SEGURIDAD
                                </h4>
                            </div>
                            <p class="text-gray-600 text-sm uppercase">
                                CATÁLOGO DE NIVELES PARA EVALUACIÓN DE TRAMOS Y RUTAS
                            </p>
                            <div class="mt-4">
                                <span class="inline-flex items-center text-emerald-600 group-hover:text-emerald-700 font-semibold text-sm">
                                    ACCEDER
                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
