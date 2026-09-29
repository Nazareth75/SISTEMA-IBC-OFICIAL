<x-app-layout>
    <div class="min-h-screen bg-[#f9f9fc] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Header Section -->
            <section class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-[#006837]">PANEL ADMINISTRATIVO ACCESIBLE</p>
                <h1 id="page-title" class="text-3xl font-bold text-[#004d27] mt-2">Bienvenido, {{ Auth::user()->nombre_completo }}</h1>
                <p class="text-gray-600 mt-2">Modo de administración optimizado por lectura auditiva y comandos de voz para el Instituto Boliviano de la Ceguera.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="bg-white p-4 rounded-xl flex items-center gap-4 border border-gray-200 shadow-sm">
                        <div class="p-3 bg-[#e8f7ef] text-[#006837] rounded-lg">
                            <span class="material-symbols-outlined text-[32px] block">shield_person</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Rol Asignado</p>
                            <p class="font-bold text-[#004d27]">Admin No Vidente</p>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl flex items-center gap-4 border border-gray-200 shadow-sm">
                        <div class="p-3 bg-[#e8f7ef] text-[#006837] rounded-lg">
                            <span class="material-symbols-outlined text-[32px] block">record_voice_over</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Asistente de Voz</p>
                            <p id="voice-status-text" class="font-bold text-[#004d27]">Inactivo (Tecla Espacio)</p>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl flex items-center gap-4 border border-gray-200 shadow-sm">
                        <div class="p-3 bg-[#e8f7ef] text-[#006837] rounded-lg">
                            <span class="material-symbols-outlined text-[32px] block">mic</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Micrófono</p>
                            <p id="mic-status-text" class="font-bold text-[#004d27]">Listo / Esperando</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Voice Mode Interaction Card -->
            <section class="bg-[#004d27] p-8 rounded-2xl shadow-xl relative overflow-hidden text-white">
                <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none">
                    <span class="material-symbols-outlined text-[180px]">graphic_eq</span>
                </div>
                <div class="flex flex-col lg:flex-row items-center justify-between gap-6 relative z-10">
                    <div class="text-center lg:text-left space-y-3">
                        <div class="flex items-center gap-3 justify-center lg:justify-start">
                            <div id="listening-indicator" class="w-4 h-4 rounded-full bg-[#ff8928] opacity-50"></div>
                            <h2 id="interaction-title" class="text-2xl font-bold text-white uppercase tracking-wide">Asistente en Espera</h2>
                        </div>
                        <p id="interaction-subtitle" class="text-green-100 text-lg">Presiona la tecla Espacio o el botón de abajo para hablar.</p>
                        <div class="inline-flex items-center gap-3 bg-[#00331a] px-4 py-2 rounded-xl border border-green-700">
                            <kbd class="bg-white text-[#004d27] px-3 py-1 rounded-lg font-extrabold text-sm shadow-md">Espacio</kbd>
                            <span class="text-xs text-green-200">Activar micrófono</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                        <button id="btn-read-screen" class="h-16 px-8 bg-[#ff8928] text-white hover:bg-[#e0751b] rounded-xl font-bold shadow-lg flex items-center justify-center gap-3 transition-all transform active:scale-95 focus-ring">
                            <span class="material-symbols-outlined text-[24px]">spatial_audio_off</span>
                            LEER PANTALLA
                        </button>
                        <div class="grid grid-cols-2 gap-4 sm:flex">
                            <button id="btn-toggle-voice" class="h-16 px-6 bg-white text-[#004d27] hover:bg-gray-100 rounded-xl font-semibold shadow transition-all transform active:scale-95 focus-ring flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">mic</span>
                                <span id="toggle-voice-btn-text">ACTIVAR</span>
                            </button>
                            <button id="btn-help-voice" class="h-16 px-6 bg-white text-[#004d27] hover:bg-gray-100 rounded-xl font-semibold shadow transition-all transform active:scale-95 focus-ring flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">campaign</span>
                                AYUDA VOZ
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Confirmation Modal / Security Warning (Hidden by default, shown when critical action pending) -->
            <section id="security-warning-card" class="bg-[#ffdad6] border-2 border-[#ba1a1a] p-6 rounded-2xl flex flex-col lg:flex-row items-center gap-6 shadow-lg hidden">
                <div class="w-14 h-14 rounded-full bg-[#ba1a1a] flex items-center justify-center text-white shrink-0 shadow-md">
                    <span class="material-symbols-outlined text-[32px]">lock_clock</span>
                </div>
                <div class="flex-grow text-center lg:text-left">
                    <h4 class="text-xl font-bold text-[#93000a]">Confirmación de Acción Requerida</h4>
                    <p class="text-gray-800 mt-1">Has solicitado una acción crítica que requiere confirmación por voz.</p>
                    <p class="text-sm font-bold text-[#ba1a1a] mt-2">Diga <span class="underline">"CONFIRMAR"</span> para proceder o <span class="underline">"CANCELAR"</span> para declinar.</p>
                </div>
                <div class="flex gap-4 shrink-0 w-full lg:w-auto justify-center">
                    <button id="btn-confirm-action" class="bg-[#ba1a1a] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#93000a] transition-colors focus-ring">CONFIRMAR</button>
                    <button id="btn-cancel-action" class="bg-white text-[#ba1a1a] border border-[#ba1a1a] px-6 py-3 rounded-xl font-bold hover:bg-red-50 transition-colors focus-ring">CANCELAR</button>
                </div>
            </section>

            <!-- Command & System Status Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Command Panel -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#004d27] mb-4 flex items-center gap-2 border-b pb-3">
                            <span class="material-symbols-outlined">history</span>
                            Última Actividad de Voz
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase">Comando Reconocido</p>
                                <p id="lbl-last-command" class="text-lg font-semibold italic text-gray-800 mt-1">Ninguno aún</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase">Acción del Sistema</p>
                                <p id="lbl-executed-action" class="text-lg font-semibold text-[#004d27] mt-1">Esperando entrada</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-[#044d29] text-white rounded-xl flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#ff8928] text-[28px] shrink-0">chat_bubble</span>
                        <p id="lbl-voice-feedback" class="text-sm font-medium">Asistente: "Listo para navegar. Presiona la tecla Espacio para hablar."</p>
                    </div>
                </div>

                <!-- Screen Reader Transcript / Audit Log -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col">
                    <h3 class="text-lg font-bold text-[#004d27] mb-4 flex items-center gap-2 border-b pb-3">
                        <span class="material-symbols-outlined">text_snippet</span>
                        Transcripción del Narrador
                    </h3>
                    <div class="flex-grow bg-gray-50 p-4 rounded-xl border border-gray-100 font-mono text-xs text-gray-700 overflow-y-auto max-h-[160px] min-h-[120px] leading-relaxed">
                        <span class="text-[#004d27] font-bold block mb-1">[AUDIBLE]</span>
                        <span id="lbl-screen-reader-text">Bienvenido al Panel Administrativo Accesible por Voz. Para conocer los comandos de voz disponibles, presione la tecla Espacio y diga "ayuda".</span>
                    </div>
                    <p class="mt-3 text-[11px] text-gray-400 italic">Muestra textual de la narración que se está reproduciendo en tus auriculares.</p>
                </div>
            </div>

            <!-- Quick Access Grid -->
            <div>
                <h3 class="text-xl font-bold text-[#004d27] mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined">apps</span>
                    Accesos Rápidos del Sistema
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Card 1: Personas No Videntes -->
                    <a href="{{ route('personas-no-videntes.index') }}" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-[#004d27] hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-[#004d27] group-hover:scale-105 transition-transform">person_search</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#004d27]">Personas No Videntes</h4>
                            <p class="text-gray-500 text-sm mt-1">Base de datos de afiliados nacionales.</p>
                        </div>
                    </a>

                    <!-- Card 2: Afiliaciones -->
                    <a href="{{ route('afiliaciones.index') }}" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-[#004d27] hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-[#004d27] group-hover:scale-105 transition-transform">assignment_ind</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#004d27]">Afiliaciones</h4>
                            <p class="text-gray-500 text-sm mt-1">Registro y renovaciones de estado.</p>
                        </div>
                    </a>

                    <!-- Card 3: Beneficios -->
                    <a href="{{ route('beneficios.index') }}" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-[#004d27] hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-[#004d27] group-hover:scale-105 transition-transform">volunteer_activism</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#004d27]">Beneficios</h4>
                            <p class="text-gray-500 text-sm mt-1">Gestión de bonos y apoyos sociales.</p>
                        </div>
                    </a>

                    <!-- Card 4: Capacitaciones -->
                    <a href="{{ route('capacitaciones.index') }}" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-[#004d27] hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-[#004d27] group-hover:scale-105 transition-transform">school</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#004d27]">Capacitaciones</h4>
                            <p class="text-gray-500 text-sm mt-1">Cursos de Braille y autonomía.</p>
                        </div>
                    </a>

                    <!-- Card 5: Obstáculos Urbanos -->
                    <a href="{{ route('reportes-obstaculos.index') }}" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-[#004d27] hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-[#004d27] group-hover:scale-105 transition-transform">visibility</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#004d27]">Obstáculos Urbanos</h4>
                            <p class="text-gray-500 text-sm mt-1">Gestión y reporte de obstáculos en la vía pública.</p>
                        </div>
                    </a>

                    <!-- Card 7: Cerrar Sesión -->
                    <button type="button" onclick="triggerVoiceLogout()" class="group bg-white p-6 rounded-2xl border border-gray-200 hover:border-red-500 hover:shadow-lg transition-all text-left flex flex-col gap-4 focus-ring">
                        <span class="material-symbols-outlined text-[40px] text-red-500 group-hover:scale-105 transition-transform">logout</span>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-red-500">Cerrar Sesión</h4>
                            <p class="text-gray-500 text-sm mt-1">Salir del sistema administrativo.</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Guides & Compatibility -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Command guide -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-bold text-[#004d27] mb-4 border-b pb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined">list_alt</span>
                        Guía Rápida de Comandos de Voz
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-mono text-sm font-bold text-[#004d27]">"ayuda"</span>
                            <span class="text-xs text-gray-500 italic">Lista los comandos</span>
                        </li>
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-mono text-sm font-bold text-[#004d27]">"leer pantalla"</span>
                            <span class="text-xs text-gray-500 italic">Narra el contenido actual</span>
                        </li>
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-mono text-sm font-bold text-[#004d27]">"ir a [módulo]"</span>
                            <span class="text-xs text-gray-500 italic">Ej: "ir a afiliaciones", "ir a inicio"</span>
                        </li>
                        <li class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-mono text-sm font-bold text-[#004d27]">"cerrar sesión"</span>
                            <span class="text-xs text-gray-500 italic">Solicita confirmación de salida</span>
                        </li>
                    </ul>
                </div>

                <!-- Voice Status Support -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#004d27] mb-4 border-b pb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            Chequeo de Compatibilidad
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Motor de Reconocimiento de Voz</p>
                                    <p class="text-xs text-gray-500">Nativo del Navegador (Web Speech API)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Motor de Síntesis de Voz</p>
                                    <p class="text-xs text-gray-500">SpeechSynthesis en Español (es-ES)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-green-600 font-bold">check_circle</span>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Soporte de Atajos Teclado</p>
                                    <p class="text-xs text-gray-500">Tecla Espacio para hablar, Enter para confirmar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center pt-6 border-t mt-4">
                        <span class="font-semibold text-xs text-gray-400 uppercase tracking-widest">Instituto Boliviano de la Ceguera</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Hidden Form for Logging Out -->
    <form id="voice-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    @push('scripts')
    <script>
        // Funciones auxiliares para enlazar con el componente voice-assistant
        function triggerVoiceLogout() {
            // Se comunica con el asistente de voz enviando un evento o disparando la lógica directamente
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.triggerLogoutRequest();
            } else {
                // Si por alguna razón no está el asistente, pedir confirmación por alerta
                if (confirm('¿Desea cerrar sesión?')) {
                    document.getElementById('voice-logout-form').submit();
                }
            }
        }

        document.getElementById('btn-read-screen').addEventListener('click', function() {
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.readScreen();
            }
        });

        document.getElementById('btn-toggle-voice').addEventListener('click', function() {
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.toggleRecognition();
            }
        });

        document.getElementById('btn-help-voice').addEventListener('click', function() {
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.speakHelp();
            }
        });

        document.getElementById('btn-confirm-action').addEventListener('click', function() {
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.confirmAction();
            }
        });

        document.getElementById('btn-cancel-action').addEventListener('click', function() {
            if (window.voiceAssistantInstance) {
                window.voiceAssistantInstance.cancelAction();
            }
        });
    </script>
    @endpush
</x-app-layout>
