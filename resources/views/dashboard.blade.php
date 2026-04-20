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
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
