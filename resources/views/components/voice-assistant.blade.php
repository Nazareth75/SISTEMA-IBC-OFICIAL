@if(auth()->check() && auth()->user()->tieneRol('ADMINISTRADOR_NO_VIDENTE'))
    <!-- Voice Assistant UI Styles -->
    <style>
        .voice-ripple {
            animation: ripple 2s infinite ease-in-out;
        }
        @keyframes ripple {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(0.8); opacity: 0.5; }
        }
        .focus-ring:focus {
            outline: 4px solid #ff8928 !important;
            outline-offset: 4px !important;
        }
    </style>

    <!-- Floating Microphone Button (Visible on all pages for this role) -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-center gap-2">
        <button id="floating-voice-btn" class="w-16 h-16 bg-[#ff8928] text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all focus-ring cursor-pointer" title="Activar Modo Voz (Tecla Espacio)">
            <span class="material-symbols-outlined text-[32px]" id="floating-voice-icon">mic</span>
        </button>
    </div>

    <!-- Voice Assistant JavaScript Engine -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let isListening = false;
            let pendingAction = null;

            // Variables para el flujo guiado de Beneficios
            let guidedStep = 0; // 0: inactivo, 1: nom_bso, 2: tip_bso, 3: des_bso, 4: listo para guardar
            let guidedTempName = '';
            let guidedTempType = '';
            let guidedTempDesc = '';

            // Variables para el flujo guiado de Asignaciones
            let guidedTempAffiliateId = '';
            let guidedTempAffiliateText = '';
            let guidedTempBenefitId = '';
            let guidedTempBenefitText = '';
            let guidedTempStartDate = '';
            let guidedTempEndDate = '';
            let guidedTempStatus = '';
            let guidedTempObs = '';

            const path = window.location.pathname;
            const isBeneficiosIndex = path.match(/\/beneficios\/?$/) || (path.includes('/beneficios') && !path.includes('/create') && !path.match(/\/beneficios\/\d+/));
            const isBeneficiosCreate = path.includes('/beneficios/create');
            const isAsignacionIndex = path.match(/\/asignacion-beneficios\/?$/) || (path.includes('/asignacion-beneficios') && !path.includes('/create') && !path.match(/\/asignacion-beneficios\/\d+/));
            const isAsignacionCreate = path.includes('/asignacion-beneficios/create');

            // Instancia global accesible para interactuar con botones del dashboard
            window.voiceAssistantInstance = {
                toggleRecognition: toggleSpeechRecognition,
                readScreen: readScreen,
                speakHelp: speakHelp,
                confirmAction: executePendingAction,
                cancelAction: cancelPendingAction,
                triggerLogoutRequest: triggerLogoutRequest
            };

            // Asegurar que las voces de SpeechSynthesis estén cargadas
            if ('speechSynthesis' in window) {
                window.speechSynthesis.getVoices();
                if (window.speechSynthesis.onvoiceschanged !== undefined) {
                    window.speechSynthesis.onvoiceschanged = () => {
                        window.speechSynthesis.getVoices();
                    };
                }
            }

            // Inicializar Speech Recognition
            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-ES';
                recognition.continuous = false;
                recognition.interimResults = false;

                recognition.onstart = function() {
                    isListening = true;
                    updateUI();
                };

                recognition.onend = function() {
                    isListening = false;
                    updateUI();
                };

                recognition.onerror = function(event) {
                    console.error("Speech recognition error", event);
                    isListening = false;
                    updateUI();
                };

                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript.toLowerCase().trim();
                    handleCommand(transcript);
                };
            }

            // Atajo de teclado: Tecla Espacio para Micrófono, Enter para Confirmar
            document.addEventListener('keydown', function(event) {
                const activeEl = document.activeElement;
                const isFormInput = activeEl && (
                    activeEl.tagName === 'INPUT' || 
                    activeEl.tagName === 'TEXTAREA' || 
                    activeEl.tagName === 'SELECT' || 
                    activeEl.isContentEditable
                );

                if (event.key === ' ' && !isFormInput) {
                    event.preventDefault();
                    toggleSpeechRecognition();
                } else if (event.key === 'Enter') {
                    if ((isBeneficiosCreate || isAsignacionCreate) && guidedStep > 0) {
                        event.preventDefault();
                        handleGuidedEnter();
                        return;
                    }
                    if (pendingAction) {
                        event.preventDefault();
                        executePendingAction();
                    }
                }
            });

            // Manejador del botón flotante
            document.getElementById('floating-voice-btn').addEventListener('click', function(e) {
                e.preventDefault();
                toggleSpeechRecognition();
            });

            // Dar bienvenida inicial por voz según la página o leer mensajes flash
            const successMeta = document.querySelector('meta[name="flash-success"]');
            const errorMeta = document.querySelector('meta[name="flash-error"]');
            
            if (successMeta) {
                setTimeout(() => {
                    speak("Operación exitosa. " + successMeta.getAttribute('content'));
                }, 1000);
            } else if (errorMeta) {
                setTimeout(() => {
                    speak("Atención. " + errorMeta.getAttribute('content'));
                }, 1000);
            } else {
                if (path.endsWith('/dashboard') || path.includes('/dashboard')) {
                    setTimeout(() => {
                        speak("Bienvenido al Panel Administrativo Accesible del Instituto Boliviano de la Ceguera. Presione la tecla Espacio para activar el asistente de voz.");
                    }, 1000);
                } else if (isBeneficiosIndex) {
                    setTimeout(() => {
                        speak("Catálogo de beneficios. Puede buscar por nombre diciendo buscar seguido del nombre, pedir la lista diciendo pedir la lista, o registrar un nuevo beneficio diciendo registrar beneficio. Presione la tecla Espacio para hablar.");
                    }, 1000);
                } else if (isBeneficiosCreate) {
                    guidedStep = 1;
                    setTimeout(() => {
                        speak("Formulario de nuevo beneficio. Primer dato a llenar es nombre de beneficio. Por ejemplo, asistencia médica. Por favor, presione la tecla Espacio para activar el micrófono y dictar el nombre, y Enter para confirmar.");
                    }, 1000);
                } else if (isAsignacionIndex) {
                    setTimeout(() => {
                        speak("Listado de asignaciones de beneficios. Puede pedir la lista diciendo pedir la lista, o registrar una nueva asignación diciendo registrar asignación. Presione la tecla Espacio para hablar.");
                    }, 1000);
                } else if (isAsignacionCreate) {
                    guidedStep = 1;
                    setTimeout(() => {
                        speak("Formulario de asignación de beneficio social. Se le guiará paso a paso. Primer dato a llenar: buscar afiliado activo. Por favor, presione la tecla Espacio para activar el micrófono y dicte el nombre o cédula del afiliado a buscar.");
                    }, 1000);
                }
            }

            function toggleSpeechRecognition() {
                if (!recognition) {
                    speak("Tu navegador no soporta el reconocimiento de voz nativo.");
                    return;
                }

                if (isListening) {
                    recognition.stop();
                } else {
                    try {
                        recognition.start();
                    } catch (e) {
                        console.error(e);
                    }
                }
            }

            function startRecognitionQuietly() {
                if (recognition && !isListening) {
                    try {
                        recognition.start();
                    } catch (e) {
                        console.error(e);
                    }
                }
            }

            function speak(text, callback) {
                if (!('speechSynthesis' in window)) return;
                
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'es-ES';
                
                const voices = window.speechSynthesis.getVoices();
                const spanishVoice = voices.find(v => v.lang.startsWith('es'));
                if (spanishVoice) {
                    utterance.voice = spanishVoice;
                }

                // Sincronizar con elementos del dashboard si existen
                const labelReader = document.getElementById('lbl-screen-reader-text');
                if (labelReader) {
                    labelReader.textContent = text;
                }
                const labelFeedback = document.getElementById('lbl-voice-feedback');
                if (labelFeedback) {
                    labelFeedback.textContent = "Asistente: \"" + text + "\"";
                }

                if (callback) {
                    utterance.onend = callback;
                }

                window.speechSynthesis.speak(utterance);
            }

            function handleCommand(command) {
                console.log("Comando recibido:", command);
                
                const lblLastCommand = document.getElementById('lbl-last-command');
                if (lblLastCommand) {
                    lblLastCommand.textContent = `"${command}"`;
                }

                // --- Comandos Específicos de Beneficios ---
                if (isBeneficiosCreate) {
                    if (command === 'volver al catálogo' || command === 'volver al catalogo' || command === 'cancelar') {
                        guidedStep = 0;
                        navigateTo('beneficios', "{{ route('beneficios.index') }}");
                        return;
                    }
                    if (command === 'guardar') {
                        guidedStep = 4;
                        speak("Para guardar el beneficio actual, presione Enter.");
                        return;
                    }
                    if (command === 'corregir') {
                        if (guidedStep === 1) {
                            guidedTempName = '';
                            const inputNom = document.getElementById('nom_bso');
                            if (inputNom) inputNom.value = '';
                            speak("Entendido, por favor dicte de nuevo el nombre del beneficio.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 2) {
                            guidedTempType = '';
                            const selectTip = document.getElementById('tip_bso');
                            if (selectTip) selectTip.value = '';
                            speak("Entendido, por favor dicte de nuevo el tipo de beneficio.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 3) {
                            guidedTempDesc = '';
                            const textDes = document.getElementById('des_bso');
                            if (textDes) textDes.value = '';
                            speak("Entendido, por favor dicte de nuevo la descripción.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        }
                        return;
                    }

                    // Si está en el flujo guiado, capturar las dictaciones de campos
                    if (guidedStep > 0) {
                        if (guidedStep === 1) {
                            guidedTempName = command;
                            const inputNom = document.getElementById('nom_bso');
                            if (inputNom) {
                                inputNom.value = command;
                                inputNom.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            speak("Nombre dictado: " + command + ". Para confirmar presione Enter. Si desea cambiarlo, presione Espacio y dicte de nuevo.");
                        } else if (guidedStep === 2) {
                            let matchedType = '';
                            let matchedLabel = '';
                            const typesMap = {
                                'económico': 'ECONÓMICO',
                                'economico': 'ECONÓMICO',
                                'material': 'MATERIAL',
                                'servicio': 'SERVICIO',
                                'educativo': 'EDUCATIVO',
                                'otro': 'OTRO'
                            };

                            for (const key in typesMap) {
                                if (command.includes(key)) {
                                    matchedType = typesMap[key];
                                    matchedLabel = key.charAt(0).toUpperCase() + key.slice(1);
                                    break;
                                }
                            }

                            if (matchedType) {
                                guidedTempType = matchedType;
                                const selectTip = document.getElementById('tip_bso');
                                if (selectTip) {
                                    selectTip.value = matchedType;
                                    selectTip.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                                speak("Tipo seleccionado: " + matchedLabel + ". Para confirmar presione Enter. Si desea cambiarlo, presione Espacio y dicte de nuevo.");
                            } else {
                                speak("Opción no válida. Por favor, diga una de las siguientes opciones: Económico, Material, Servicio, Educativo, u Otro. Presione Espacio para volver a dictar.", function() {
                                    setTimeout(() => startRecognitionQuietly(), 100);
                                });
                            }
                        } else if (guidedStep === 3) {
                            guidedTempDesc = command;
                            const textDes = document.getElementById('des_bso');
                            if (textDes) {
                                textDes.value = command;
                                textDes.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            speak("Descripción dictada: " + command + ". Para confirmar presione Enter. Si desea cambiarlo, presione Espacio y dicte de nuevo.");
                        }
                        return;
                    }
                }

                // --- Comandos Específicos de Asignaciones ---
                if (isAsignacionCreate) {
                    if (command === 'volver al listado' || command === 'volver al catálogo' || command === 'cancelar') {
                        guidedStep = 0;
                        navigateTo('asignaciones', "{{ route('asignacion-beneficios.index') }}");
                        return;
                    }
                    if (command === 'guardar') {
                        guidedStep = 7;
                        speak("Para guardar la asignación actual, presione Enter.");
                        return;
                    }
                    if (command === 'corregir') {
                        if (guidedStep === 1) {
                            guidedTempAffiliateId = '';
                            guidedTempAffiliateText = '';
                            speak("Entendido, por favor dicte de nuevo el nombre o cédula del afiliado a buscar.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 2) {
                            guidedTempBenefitId = '';
                            guidedTempBenefitText = '';
                            speak("Entendido, por favor dicte de nuevo el beneficio social.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 3) {
                            guidedTempStartDate = '';
                            speak("Entendido, por favor dicte de nuevo la fecha de asignación.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 4) {
                            guidedTempEndDate = '';
                            speak("Entendido, por favor dicte de nuevo la fecha de fin.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 5) {
                            guidedTempStatus = '';
                            speak("Entendido, por favor dicte de nuevo el estado.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        } else if (guidedStep === 6) {
                            guidedTempObs = '';
                            const textObs = document.getElementById('obs_abe');
                            if (textObs) textObs.value = '';
                            speak("Entendido, por favor dicte de nuevo las observaciones.", function() {
                                setTimeout(() => startRecognitionQuietly(), 100);
                            });
                        }
                        return;
                    }

                    // Captura de datos guiados
                    if (guidedStep > 0) {
                        if (guidedStep === 1) {
                            speak("Buscando afiliado " + command + ".", function() {
                                fetch(`/api/afiliados-buscar?q=${encodeURIComponent(command)}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.length === 0) {
                                            speak("No encontré ningún afiliado activo para " + command + ". Por favor, presione Espacio e intente con otro nombre o cédula.");
                                        } else {
                                            const match = data[0];
                                            guidedTempAffiliateId = match.id;
                                            guidedTempAffiliateText = match.text;
                                            
                                            const selectAff = $('#cod_afiliaciones_abe');
                                            if (selectAff.length > 0) {
                                                const newOption = new Option(match.text, match.id, true, true);
                                                selectAff.append(newOption).trigger('change');
                                            }
                                            speak("Encontré a: " + match.text + ". Para confirmar presione Enter. Si desea buscar otro, presione Espacio.");
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        speak("Ocurrió un error al buscar al afiliado.");
                                    });
                            });
                        } else if (guidedStep === 2) {
                            const benefitSelect = document.getElementById('cod_beneficios_sociales_abe');
                            if (benefitSelect) {
                                const options = Array.from(benefitSelect.options).filter(o => o.value !== "");
                                let matchedOption = null;
                                for (const opt of options) {
                                    if (opt.text.toLowerCase().includes(command.toLowerCase()) || command.toLowerCase().includes(opt.text.toLowerCase())) {
                                        matchedOption = opt;
                                        break;
                                    }
                                }
                                if (matchedOption) {
                                    guidedTempBenefitId = matchedOption.value;
                                    guidedTempBenefitText = matchedOption.text;
                                    benefitSelect.value = matchedOption.value;
                                    benefitSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                    speak("Beneficio seleccionado: " + matchedOption.text + ". Para confirmar presione Enter. Si desea cambiarlo, presione Espacio.");
                                } else {
                                    speak("No encontré ningún beneficio que coincida con " + command + ". Por favor, presione la tecla Espacio para intentar nuevamente.");
                                }
                            }
                        } else if (guidedStep === 3) {
                            const parsedDate = parseDictatedDate(command);
                            if (parsedDate) {
                                guidedTempStartDate = parsedDate;
                                const inputStart = document.getElementById('fas_abe');
                                if (inputStart) {
                                    inputStart.value = parsedDate;
                                    inputStart.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                speak("Fecha de asignación dictada: " + speakDate(parsedDate) + ". Para confirmar presione Enter. Si desea cambiarla, presione Espacio.");
                            } else {
                                speak("No entendí la fecha. Por favor, diga el día, el mes, y el año. Presione Espacio para volver a dictar.");
                            }
                        } else if (guidedStep === 4) {
                            const parsedDate = parseDictatedDate(command);
                            if (parsedDate) {
                                guidedTempEndDate = parsedDate;
                                const inputEnd = document.getElementById('ffi_abe');
                                if (inputEnd) {
                                    inputEnd.value = parsedDate;
                                    inputEnd.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                speak("Fecha de fin dictada: " + speakDate(parsedDate) + ". Para confirmar presione Enter. Si desea cambiarla, presione Espacio.");
                            } else {
                                speak("No entendí la fecha de fin. Por favor, diga el día, el mes, y el año. Presione Espacio para volver a dictar.");
                            }
                        } else if (guidedStep === 5) {
                            let matchedStatus = '';
                            let matchedLabel = '';
                            const statusMap = {
                                'activo': 'ACTIVO',
                                'suspendido': 'SUSPENDIDO',
                                'finalizado': 'FINALIZADO'
                            };
                            for (const key in statusMap) {
                                if (command.includes(key)) {
                                    matchedStatus = statusMap[key];
                                    matchedLabel = key.charAt(0).toUpperCase() + key.slice(1);
                                    break;
                                }
                            }
                            if (matchedStatus) {
                                guidedTempStatus = matchedStatus;
                                const selectEst = document.getElementById('est_abe');
                                if (selectEst) {
                                    selectEst.value = matchedStatus;
                                    selectEst.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                                speak("Estado seleccionado: " + matchedLabel + ". Para confirmar presione Enter. Si desea cambiarlo, presione Espacio.");
                            } else {
                                speak("Estado no válido. Diga Activo, Suspendido, o Finalizado. Presione Espacio para volver a intentar.");
                            }
                        } else if (guidedStep === 6) {
                            guidedTempObs = command;
                            const textObs = document.getElementById('obs_abe');
                            if (textObs) {
                                textObs.value = command;
                                textObs.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            speak("Observación dictada: " + command + ". Para confirmar presione Enter. Si desea cambiarla, presione Espacio.");
                        }
                        return;
                    }
                }

                if (isBeneficiosIndex) {
                    if (command === 'registrar beneficio' || command === 'crear beneficio') {
                        navigateTo('registrar beneficio', "{{ route('beneficios.create') }}");
                        return;
                    }
                    if (command === 'pedir la lista' || command.includes('pedir lista')) {
                        const rows = document.querySelectorAll('table tbody tr');
                        if (rows.length === 0) {
                            speak("La lista de beneficios está vacía.");
                        } else {
                            let textToSpeak = "Los beneficios en esta página son: ";
                            rows.forEach((row, idx) => {
                                const cells = row.querySelectorAll('td');
                                if (cells.length >= 3) {
                                    const nombre = cells[0].textContent.trim();
                                    const tipo = cells[1].textContent.trim();
                                    const desc = cells[2].textContent.trim();
                                    textToSpeak += `Beneficio ${idx + 1}: ${nombre}, tipo ${tipo}. ${desc && desc !== '-' ? 'Descripción: ' + desc : 'Sin descripción'}. `;
                                }
                            });
                            speak(textToSpeak);
                        }
                        return;
                    }
                    if (command.startsWith('buscar ') || command.startsWith('buscar por nombre ') || command.startsWith('buscar beneficio ')) {
                        let query = command.replace('buscar por nombre ', '').replace('buscar beneficio ', '').replace('buscar ', '').trim();
                        const searchInput = document.getElementById('buscar');
                        if (searchInput) {
                            searchInput.value = query;
                            speak("Buscando beneficio " + query, function() {
                                searchInput.closest('form').submit();
                            });
                        } else {
                            speak("No encontré el campo de búsqueda de beneficios.");
                        }
                        return;
                    }
                }

                if (isAsignacionIndex) {
                    if (command === 'registrar asignación' || command === 'registrar asignacion' || command === 'asignar beneficio' || command === 'crear asignación' || command === 'crear asignacion') {
                        navigateTo('registrar asignación', "{{ route('asignacion-beneficios.create') }}");
                        return;
                    }
                    if (command === 'pedir la lista' || command.includes('pedir lista')) {
                        const rows = document.querySelectorAll('table tbody tr');
                        if (rows.length === 0) {
                            speak("La lista de asignaciones está vacía.");
                        } else {
                            let textToSpeak = "Las asignaciones en esta página son: ";
                            rows.forEach((row, idx) => {
                                const cells = row.querySelectorAll('td');
                                if (cells.length >= 6) {
                                    const afiliado = cells[0].querySelector('span').textContent.trim();
                                    const beneficio = cells[1].textContent.trim();
                                    const fecha = cells[3].textContent.trim();
                                    const estado = cells[5].textContent.trim();
                                    textToSpeak += `Asignación ${idx + 1}: ${afiliado}, beneficio ${beneficio}, asignado el ${fecha}, estado ${estado}. `;
                                }
                            });
                            speak(textToSpeak);
                        }
                        return;
                    }
                    if (command.startsWith('buscar ') || command.startsWith('buscar por nombre ') || command.startsWith('buscar beneficio ')) {
                        let query = command.replace('buscar por nombre ', '').replace('buscar beneficio ', '').replace('buscar ', '').trim();
                        const searchInput = document.getElementById('buscar');
                        if (searchInput) {
                            searchInput.value = query;
                            speak("Buscando " + query, function() {
                                searchInput.closest('form').submit();
                            });
                        } else {
                            speak("No encontré el campo de búsqueda.");
                        }
                        return;
                    }
                }
                // --- Fin de Comandos Específicos de Beneficios ---

                // Si hay una confirmación crítica pendiente
                if (pendingAction) {
                    if (command.includes('confirmar') || command === 'confirmar' || command.includes('sí') || command === 'si') {
                        executePendingAction();
                    } else if (command.includes('cancelar') || command === 'cancelar' || command.includes('no')) {
                        cancelPendingAction();
                    } else {
                        speak("Confirmación requerida. Por favor diga confirmar o cancelar.");
                    }
                    return;
                }

                // 1. Buscar afiliado
                if (command.startsWith('buscar afiliado')) {
                    let query = command.replace('buscar afiliado', '').trim();
                    if (!query) {
                        speak("Por favor, diga 'buscar afiliado' seguido del nombre, apellido o cédula del afiliado.");
                    } else {
                        const lblAction = document.getElementById('lbl-executed-action');
                        if (lblAction) lblAction.textContent = "Buscando afiliado: " + query;

                        fetch(`/api/afiliados-buscar?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                let results = Array.isArray(data) ? data : (data.results || []);
                                if (results.length === 0) {
                                    speak("No encontré ningún afiliado activo para la búsqueda " + query);
                                } else {
                                    let speakText = `Encontré ${results.length} resultados. `;
                                    results.slice(0, 5).forEach((af, idx) => {
                                        speakText += `Resultado ${idx + 1}: ${af.text}. `;
                                    });
                                    speak(speakText);
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                speak("Ocurrió un error al buscar el afiliado.");
                            });
                    }
                    return;
                }

                // 2. Leer beneficios activos
                if (command.includes('leer beneficios activos') || command.includes('beneficios activos')) {
                    const lblAction = document.getElementById('lbl-executed-action');
                    if (lblAction) lblAction.textContent = "Leyendo beneficios activos";

                    fetch('/api/asistente/beneficios-activos')
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                speak("No hay beneficios activos registrados en este momento.");
                            } else {
                                let speakText = `Hay ${data.length} beneficios activos registrados. `;
                                data.forEach((ben, idx) => {
                                    speakText += `Beneficio ${idx + 1}: ${ben.nom_bso}. Descripción: ${ben.des_bso || 'Sin descripción'}. `;
                                });
                                speak(speakText);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            speak("Error al consultar los beneficios activos.");
                        });
                    return;
                }

                // 3. Registrar observación
                if (command.startsWith('registrar observación') || command.startsWith('registrar observacion')) {
                    let dictated = command.replace(/registrar observaci[óo]n/, '').trim();
                    if (!dictated) {
                        speak("Por favor diga registrar observación seguido del texto que desea ingresar.");
                    } else {
                        const obsInput = document.querySelector('textarea[name*="obs"], input[name*="obs"], textarea[id*="obs"], input[id*="obs"]');
                        if (obsInput) {
                            obsInput.value = dictated;
                            obsInput.dispatchEvent(new Event('input', { bubbles: true }));
                            speak("Observación registrada en el campo del formulario: " + dictated);
                            const lblAction = document.getElementById('lbl-executed-action');
                            if (lblAction) lblAction.textContent = "Observación insertada en formulario";
                        } else {
                            speak("No encontré ningún campo de observaciones en esta pantalla.");
                        }
                    }
                    return;
                }

                // 4. Mostrar/Leer afiliaciones vencidas
                if (command.includes('mostrar afiliaciones vencidas') || command.includes('leer afiliaciones vencidas')) {
                    const lblAction = document.getElementById('lbl-executed-action');
                    if (lblAction) lblAction.textContent = "Buscando afiliaciones vencidas";

                    fetch('/api/asistente/afiliaciones-vencidas')
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                speak("No se encontraron afiliaciones vencidas.");
                            } else {
                                let speakText = `Se encontraron ${data.length} afiliaciones vencidas. `;
                                data.forEach((a, idx) => {
                                    speakText += `Registro ${idx + 1}: ${a.nombre}, cédula ${a.ci}. Venció el ${a.fecha_vencimiento}. `;
                                });
                                speak(speakText);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            speak("Error al buscar afiliaciones vencidas.");
                        });
                    return;
                }

                // 5. Leer obstáculos críticos
                if (command.includes('leer obstáculos críticos') || command.includes('leer obstaculos criticos')) {
                    const lblAction = document.getElementById('lbl-executed-action');
                    if (lblAction) lblAction.textContent = "Consultando obstáculos críticos";

                    fetch('/api/asistente/obstaculos-criticos')
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                speak("No hay obstáculos críticos reportados o activos en este momento.");
                            } else {
                                let speakText = `Hay ${data.length} obstáculos críticos. `;
                                data.forEach((obs, idx) => {
                                    speakText += `Obstáculo ${idx + 1}: ${obs.tipo_obstaculo}. Dirección o referencia: ${obs.direccion_referencia || 'No indicada'}. Descripción: ${obs.descripcion || 'Sin descripción'}. `;
                                });
                                speak(speakText);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            speak("Error al leer los obstáculos críticos.");
                        });
                    return;
                }

                // 6. Cambiar estado del trámite a resuelto
                if (command.includes('cambiar estado del trámite a resuelto') || command.includes('cambiar estado del tramite a resuelto')) {
                    const match = window.location.pathname.match(/\/tramites-instituciones\/(\d+)/);
                    if (!match) {
                        speak("Este comando solo puede ser utilizado dentro de la pantalla de detalle de un trámite.");
                        return;
                    }
                    const tramiteId = match[1];
                    
                    pendingAction = {
                        type: 'resolve_tramite',
                        id: tramiteId
                    };

                    const warningCard = document.getElementById('security-warning-card');
                    if (warningCard) {
                        warningCard.classList.remove('hidden');
                        const warningDesc = warningCard.querySelector('.text-gray-800') || warningCard.querySelector('p');
                        if (warningDesc) warningDesc.textContent = "Has solicitado resolver el trámite actual.";
                    }

                    speak("¿Está seguro de que desea cambiar el estado de este trámite a resuelto? Diga confirmar o cancelar.", function() {
                        setTimeout(() => startRecognitionQuietly(), 100);
                    });

                    const lblAction = document.getElementById('lbl-executed-action');
                    if (lblAction) lblAction.textContent = "Solicitando confirmación para resolver trámite";
                    return;
                }

                // 7. Volver al menú principal
                if (command.includes('volver al menú principal') || command.includes('volver al menu principal')) {
                    navigateTo('menú principal', "{{ route('dashboard') }}");
                    return;
                }

                // Comandos estándar de navegación y lectura
                if (command.includes('ayuda') || command === 'ayuda') {
                    speakHelp();
                } else if (command.includes('leer pantalla') || command.includes('leer') || command === 'pantalla') {
                    readScreen();
                } else if (command.includes('ir a inicio') || command === 'inicio' || command === 'ir a dashboard') {
                    navigateTo('inicio', "{{ route('dashboard') }}");
                } else if (command.includes('ir a personas no videntes') || command.includes('personas no videntes') || command.includes('afiliados')) {
                    navigateTo('personas no videntes', "{{ route('personas-no-videntes.index') }}");
                } else if (command.includes('ir a afiliaciones') || command.includes('afiliaciones') || command.includes('afiliación')) {
                    navigateTo('afiliaciones', "{{ route('afiliaciones.index') }}");
                } else if (command.includes('ir a beneficios') || command.includes('beneficios') || command.includes('beneficio')) {
                    navigateTo('beneficios', "{{ route('beneficios.index') }}");
                } else if (command.includes('ir a asignaciones') || command.includes('ir a asignación') || command.includes('asignaciones') || command.includes('asignacion')) {
                    navigateTo('asignación de beneficios', "{{ route('asignacion-beneficios.index') }}");
                } else if (command.includes('ir a capacitaciones') || command.includes('capacitaciones') || command.includes('capacitación')) {
                    navigateTo('capacitaciones', "{{ route('capacitaciones.index') }}");
                } else if (command.includes('ir a rutas seguras') || command.includes('rutas seguras') || command.includes('rutas')) {
                    speak("El módulo de rutas seguras no está disponible para tu rol.");
                } else if (command.includes('ir a obstáculos') || command.includes('obstáculos') || command.includes('obstaculos') || command.includes('obstáculo') || command.includes('obstaculo')) {
                    if (command.includes('urbanos')) {
                        navigateTo('obstáculos urbanos', "{{ route('reportes-obstaculos.index') }}");
                    } else {
                        speak("El módulo de obstáculos no está disponible para tu rol. Si deseas ver los reportes ciudadanos, por favor di: ir a obstáculos urbanos.");
                    }
                } else if (command.includes('cerrar sesión') || command.includes('cerrar sesion') || command === 'salir') {
                    triggerLogoutRequest();
                } else {
                    speak("No reconocí el comando '" + command + "'. Diga ayuda para escuchar la lista de comandos.");
                }
            }

            function parseDictatedDate(text) {
                text = text.toLowerCase().trim();
                if (text.includes('hoy') || text.includes('actual')) {
                    const now = new Date();
                    return now.toISOString().split('T')[0];
                }
                
                const months = {
                    'enero': '01', 'febrero': '02', 'marzo': '03', 'abril': '04',
                    'mayo': '05', 'junio': '06', 'julio': '07', 'agosto': '08',
                    'septiembre': '09', 'octubre': '10', 'noviembre': '11', 'diciembre': '12',
                    'setiembre': '09'
                };
                
                const textMap = {
                    'primero': '1', 'uno': '1', 'dos': '2', 'tres': '3', 'cuatro': '4', 'cinco': '5',
                    'seis': '6', 'siete': '7', 'ocho': '8', 'nueve': '9', 'diez': '10',
                    'once': '11', 'doce': '12', 'trece': '13', 'catorce': '14', 'quince': '15',
                    'dieciséis': '16', 'dieciseis': '16', 'diecisiete': '17', 'dieciocho': '18',
                    'diecinueve': '19', 'veinte': '20', 'veintiuno': '21', 'veintidós': '22',
                    'veintidos': '22', 'veintitrés': '23', 'veintitres': '23', 'veinticuatro': '24',
                    'veinticinco': '25', 'veintiséis': '26', 'veintiseis': '26', 'veintisiete': '27',
                    'veintiocho': '28', 'veintinueve': '29', 'treinta': '30', 'treinta y uno': '31'
                };
                
                let normalized = text;
                for (const word in textMap) {
                    normalized = normalized.replace(new RegExp('\\b' + word + '\\b', 'g'), textMap[word]);
                }

                const regex = /(\d{1,2})\s*(?:de|del|\/|-)\s*([a-z0-9]+)\s*(?:de|del|\/|-)?\s*(\d{4})/;
                const match = normalized.match(regex);
                if (match) {
                    let day = match[1].padStart(2, '0');
                    let monthStr = match[2];
                    let year = match[3];
                    let month = months[monthStr] || (months[monthStr.replace(/[áéíóú]/g, 'a')] || monthStr.padStart(2, '0'));
                    
                    const monthNum = parseInt(month, 10);
                    if (monthNum >= 1 && monthNum <= 12) {
                        return `${year}-${month.padStart(2, '0')}-${day}`;
                    }
                }
                
                const regexISO = /(\d{4})[-/](\d{2})[-/](\d{2})/;
                const matchISO = normalized.match(regexISO);
                if (matchISO) {
                    return `${matchISO[1]}-${matchISO[2]}-${matchISO[3]}`;
                }
                
                return null;
            }

            function speakDate(dateStr) {
                if (!dateStr) return "vacío";
                const parts = dateStr.split('-');
                if (parts.length !== 3) return dateStr;
                const monthsNames = [
                    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
                ];
                const day = parseInt(parts[2], 10);
                const month = monthsNames[parseInt(parts[1], 10) - 1];
                const year = parts[0];
                return `${day} de ${month} de ${year}`;
            }

            function handleGuidedEnter() {
                if (isListening) {
                    recognition.stop();
                    return;
                }

                if (isBeneficiosCreate) {
                    if (guidedStep === 1) {
                        if (guidedTempName) {
                            const inputNom = document.getElementById('nom_bso');
                            if (inputNom) {
                                inputNom.value = guidedTempName;
                                inputNom.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            guidedStep = 2;
                            speak("Nombre confirmado. Segundo dato a llenar: tipo de beneficio. Las opciones son: Económico, Material, Servicio, Educativo, u Otro. Por favor, presione la tecla Espacio para activar el micrófono y decir una opción.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar el nombre.");
                        }
                    } else if (guidedStep === 2) {
                        if (guidedTempType) {
                            const selectTip = document.getElementById('tip_bso');
                            if (selectTip) {
                                selectTip.value = guidedTempType;
                                selectTip.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            guidedStep = 3;
                            speak("Tipo confirmado. Siguiente campo: descripción. Por favor, presione la tecla Espacio para activar el micrófono y dictar la descripción.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar el tipo de beneficio.");
                        }
                    } else if (guidedStep === 3) {
                        if (guidedTempDesc) {
                            const textDes = document.getElementById('des_bso');
                            if (textDes) {
                                textDes.value = guidedTempDesc;
                                textDes.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            guidedStep = 4;
                            speak("Descripción confirmada. Para guardar presione Enter.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar la descripción.");
                        }
                    } else if (guidedStep === 4) {
                        speak("Guardando beneficio.", function() {
                            const form = document.getElementById('beneficioForm');
                            if (form) {
                                form.submit();
                            }
                        });
                    }
                }

                if (isAsignacionCreate) {
                    if (guidedStep === 1) {
                        if (guidedTempAffiliateId) {
                            guidedStep = 2;
                            const benefitSelect = document.getElementById('cod_beneficios_sociales_abe');
                            let optionsStr = "";
                            if (benefitSelect) {
                                const options = Array.from(benefitSelect.options).filter(o => o.value !== "");
                                optionsStr = options.map(o => o.text.trim().split('(')[0]).join(', ');
                            }
                            speak("Afiliado confirmado. Segundo dato a llenar: beneficio social. Las opciones disponibles son: " + optionsStr + ". Por favor, presione Espacio para activar el micrófono y dictar el nombre del beneficio.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y buscar al afiliado.");
                        }
                    } else if (guidedStep === 2) {
                        if (guidedTempBenefitId) {
                            guidedStep = 3;
                            const todayStr = new Date().toISOString().split('T')[0];
                            guidedTempStartDate = todayStr;
                            speak("Beneficio confirmado. Tercer dato a llenar: fecha de asignación. El valor por defecto es hoy, " + speakDate(todayStr) + ". Para confirmar este valor presione Enter. Si desea cambiarlo, presione Espacio para activar el micrófono y dictar la fecha.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar el beneficio.");
                        }
                    } else if (guidedStep === 3) {
                        if (guidedTempStartDate) {
                            guidedStep = 4;
                            speak("Fecha de asignación confirmada. Cuarto dato: fecha de fin. Este campo es opcional. Presione Enter para dejarlo vacío y avanzar. O presione la tecla Espacio para activar el micrófono y dictar la fecha de fin.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar la fecha de asignación.");
                        }
                    } else if (guidedStep === 4) {
                        guidedStep = 5;
                        guidedTempStatus = "ACTIVO";
                        speak("Fecha de fin confirmada. Quinto dato: estado de la asignación. El valor por defecto es Activo. Para confirmar presione Enter. O presione la tecla Espacio para dictar: Activo, Suspendido, o Finalizado.");
                    } else if (guidedStep === 5) {
                        if (guidedTempStatus) {
                            guidedStep = 6;
                            speak("Estado confirmado. Sexto dato: observaciones. Este campo es opcional. Presione Enter para dejarlo vacío y avanzar. O presione la tecla Espacio para activar el micrófono y dictar las observaciones.");
                        } else {
                            speak("Por favor, presione la tecla Espacio para activar el micrófono y dictar el estado.");
                        }
                    } else if (guidedStep === 6) {
                        guidedStep = 7;
                        speak("Observaciones confirmadas. Para guardar la asignación del beneficio, presione Enter.");
                    } else if (guidedStep === 7) {
                        speak("Guardando asignación.", function() {
                            const form = document.getElementById('asignacionForm');
                            if (form) {
                                form.submit();
                            }
                        });
                    }
                }
            }

            function navigateTo(name, url) {
                speak("Redirigiendo al módulo de " + name + ".", function() {
                    window.location.href = url;
                });
                const lblAction = document.getElementById('lbl-executed-action');
                if (lblAction) {
                    lblAction.textContent = "Redirigiendo a: " + name;
                }
            }

            function triggerLogoutRequest() {
                pendingAction = 'logout';
                const warningCard = document.getElementById('security-warning-card');
                if (warningCard) {
                    warningCard.classList.remove('hidden');
                }

                speak("¿Está seguro de que desea cerrar sesión? Diga confirmar para salir o cancelar para continuar.", function() {
                    setTimeout(() => startRecognitionQuietly(), 100);
                });

                const lblAction = document.getElementById('lbl-executed-action');
                if (lblAction) {
                    lblAction.textContent = "Solicitando confirmación de salida";
                }
            }

            function executePendingAction() {
                if (!pendingAction) return;

                if (pendingAction === 'logout') {
                    speak("Cerrando sesión. Hasta luego.", function() {
                        const logoutForm = document.getElementById('voice-logout-form');
                        if (logoutForm) {
                            logoutForm.submit();
                        } else {
                            const genericForm = document.querySelector('form[action*="logout"]');
                            if (genericForm) genericForm.submit();
                        }
                    });
                } else if (pendingAction.type === 'resolve_tramite') {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    speak("Actualizando trámite a resuelto.", function() {
                        fetch(`/tramites-instituciones/${pendingAction.id}/cambiar-estado`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                nuevo_estado: 'RESUELTO',
                                observaciones: 'Resuelto mediante comando de voz.'
                            })
                        })
                        .then(res => {
                            window.location.reload();
                        })
                        .catch(err => {
                            console.error(err);
                            speak("Error al intentar actualizar el trámite.");
                        });
                    });
                }
                pendingAction = null;
                hideWarningCard();
            }

            function cancelPendingAction() {
                speak("Acción cancelada.");
                pendingAction = null;
                hideWarningCard();
            }

            function hideWarningCard() {
                const warningCard = document.getElementById('security-warning-card');
                if (warningCard) {
                    warningCard.classList.add('hidden');
                }
            }

            function speakHelp() {
                const helpText = "Los comandos disponibles son: " +
                    "Ayuda. " +
                    "Leer pantalla. " +
                    "Volver al menú principal. " +
                    "Ir a inicio. " +
                    "Ir a personas no videntes. " +
                    "Ir a afiliaciones. " +
                    "Ir a beneficios. " +
                    "Ir a asignaciones. " +
                    "Ir a capacitaciones. " +
                    "Ir a obstáculos urbanos. " +
                    "Buscar afiliado seguido del nombre o documento. " +
                    "Leer beneficios activos. " +
                    "Registrar observación seguido del comentario. " +
                    "Mostrar afiliaciones vencidas. " +
                    "Leer obstáculos críticos. " +
                    "Cambiar estado del trámite a resuelto. " +
                    "Cerrar sesión. " +
                    "Y cancelar.";
                speak(helpText);
                const lblAction = document.getElementById('lbl-executed-action');
                if (lblAction) {
                    lblAction.textContent = "Reproduciendo ayuda de comandos";
                }
            }

            function readScreen() {
                let textToSpeak = "";
                const mainArea = document.querySelector('main');
                
                if (!mainArea) {
                    speak("No se pudo detectar el área de contenido principal.");
                    return;
                }
                
                // Leer título principal dentro de main
                const titleEl = mainArea.querySelector('h1') || mainArea.querySelector('h2') || mainArea.querySelector('.text-3xl') || mainArea.querySelector('.text-2xl') || mainArea.querySelector('.font-bold');
                if (titleEl) {
                    textToSpeak += "Sección actual: " + titleEl.textContent.trim().replace(/\s+/g, ' ') + ". ";
                }
                
                // Intentar leer las descripciones o textos introductorios del main (primeros 2 párrafos descriptivos)
                const paragraphs = mainArea.querySelectorAll('p');
                let paragraphCount = 0;
                paragraphs.forEach(p => {
                    const text = p.textContent.trim().replace(/\s+/g, ' ');
                    if (text.length > 10 && paragraphCount < 2 && !p.closest('table') && !p.closest('.fixed')) {
                        textToSpeak += text + ". ";
                        paragraphCount++;
                    }
                });

                // Identificar botones y enlaces principales dentro de main
                const interactiveElements = mainArea.querySelectorAll('a, button');
                let foundButtons = [];

                interactiveElements.forEach(el => {
                    const text = el.textContent.trim().replace(/\s+/g, ' ') || el.getAttribute('aria-label');
                    if (text && el.offsetWidth > 0 && el.offsetHeight > 0 && foundButtons.length < 8) {
                        if (text.length > 1 && text.length < 55 && !foundButtons.includes(text)) {
                            foundButtons.push(text);
                        }
                    }
                });

                if (foundButtons.length > 0) {
                    textToSpeak += "Botones y acciones disponibles: " + foundButtons.join(', ') + ". ";
                } else {
                    textToSpeak += "No hay botones de acción visibles en este módulo. ";
                }

                // Intentar leer los datos de alguna tabla o listado principal si existen en el main
                const tableRows = mainArea.querySelectorAll('table tbody tr');
                if (tableRows.length > 0) {
                    textToSpeak += `Se muestran ${tableRows.length} registros en la tabla. `;
                    // Leer el primer registro como muestra
                    const firstRowCells = tableRows[0].querySelectorAll('td');
                    let rowText = [];
                    firstRowCells.forEach(cell => {
                        const cellText = cell.textContent.trim().replace(/\s+/g, ' ');
                        if (cellText && cellText.length > 0 && cellText.length < 100) {
                            rowText.push(cellText);
                        }
                    });
                    if (rowText.length > 0) {
                        textToSpeak += "Por ejemplo, el primer registro contiene: " + rowText.slice(0, 4).join(', ') + ". ";
                    }
                }

                speak(textToSpeak);
                const lblAction = document.getElementById('lbl-executed-action');
                if (lblAction) {
                    lblAction.textContent = "Lectura de contenido principal finalizada";
                }
            }

            function updateUI() {
                const btnFloating = document.getElementById('floating-voice-btn');
                const iconFloating = document.getElementById('floating-voice-icon');

                if (isListening) {
                    if (btnFloating) {
                        btnFloating.classList.add('voice-ripple');
                        btnFloating.classList.remove('bg-[#ff8928]');
                        btnFloating.classList.add('bg-red-600');
                    }
                    if (iconFloating) iconFloating.textContent = "mic_active";
                } else {
                    if (btnFloating) {
                        btnFloating.classList.remove('voice-ripple');
                        btnFloating.classList.remove('bg-red-600');
                        btnFloating.classList.add('bg-[#ff8928]');
                    }
                    if (iconFloating) iconFloating.textContent = "mic";
                }

                // Si en la página actual existen los elementos del dashboard, actualizarlos también
                if (typeof updateUI === 'function' || document.getElementById('voice-status-text')) {
                    // Buscar los elementos e invocarlos dinámicamente
                    const statusText = document.getElementById('voice-status-text');
                    const micStatus = document.getElementById('mic-status-text');
                    const interactionTitle = document.getElementById('interaction-title');
                    const interactionSubtitle = document.getElementById('interaction-subtitle');
                    const btnToggleText = document.getElementById('toggle-voice-btn-text');
                    const listeningIndicator = document.getElementById('listening-indicator');
                    
                    if (isListening) {
                        if (statusText) statusText.textContent = "Escuchando...";
                        if (micStatus) micStatus.textContent = "Grabando audio...";
                        if (interactionTitle) interactionTitle.textContent = "Escuchando...";
                        if (interactionSubtitle) interactionSubtitle.textContent = "Hable ahora. El sistema procesará su comando al terminar.";
                        if (btnToggleText) btnToggleText.textContent = "DESACTIVAR";
                        if (listeningIndicator) {
                            listeningIndicator.className = "w-4 h-4 rounded-full bg-[#ff8928] voice-ripple";
                        }
                    } else {
                        if (statusText) statusText.textContent = "Inactivo (Tecla Espacio)";
                        if (micStatus) micStatus.textContent = "Listo / Esperando";
                        if (interactionTitle) interactionTitle.textContent = "Asistente en Espera";
                        if (interactionSubtitle) interactionSubtitle.textContent = "Presiona la tecla Espacio o el botón de abajo para hablar.";
                        if (btnToggleText) btnToggleText.textContent = "ACTIVAR";
                        if (listeningIndicator) {
                            listeningIndicator.className = "w-4 h-4 rounded-full bg-[#ff8928] opacity-50";
                        }
                    }
                }
            }
            
            // Añadir clase focus-ring a todos los botones/enlaces para accesibilidad visual rápida
            const elements = document.querySelectorAll('button, a, input, select, textarea');
            elements.forEach(el => {
                el.classList.add('focus-ring');
            });
        });
    </script>
@endif
