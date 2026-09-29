<x-app-layout>
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700;900&display=swap" rel="stylesheet"/>
    <style>
        .public-sans { font-family: 'Public Sans', sans-serif; }
        .active-category {
            outline: 4px solid #ff8928 !important;
            background-color: #ff89281a !important;
            border-color: transparent !important;
        }
        .bento-card {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), outline 0.2s, background-color 0.2s;
        }
        .bento-card:active {
            transform: scale(0.95);
        }
    </style>
    <!-- Leaflet CSS for the map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    <div class="bg-[#f9f9fc] min-h-[calc(100vh-4rem)] flex flex-col items-center py-8 public-sans">
        
        <form action="{{ route('reportes-obstaculos.store') }}" method="POST" enctype="multipart/form-data" class="w-full max-w-md mx-auto px-4 space-y-8">
            @csrf

            <!-- Mostrar errores de validación -->
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4" role="alert">
                    <p class="font-bold">Error en el formulario</p>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Header Title & Mascot -->
            <section class="flex justify-between items-start">
                <div class="space-y-1">
                    <h2 class="text-[32px] leading-[40px] font-bold text-[#004d27]">Reportar Obstáculo</h2>
                    <p class="text-[16px] text-[#3f4940]">Ayúdanos a mantener las rutas accesibles para todos.</p>
                </div>
                <div class="w-20 h-20 flex-shrink-0 relative">
                    <img alt="IBC Mascot" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCL1v4iEM6R1l7AHNeSAz74MBmzmykg1kSfTYuwZryGMWrVy2RN-eikSnTe7GgozWK6zg5PskMRmd12nPj7_6-hTJPGm3X2CnIfGLspuuoNRHpr2Ot3v1KeNYaS0RmP-yh7p4OwyYx2tcfHyK8l8b1IdPvJGuaZnR6tJgA-iHVYiAF6DrvDgF7OSq1JFZerSlL6WlJxMm3562PVKBGjcXevc6Zhh3tKg2yG5Rn_IfRLX88lW_Ntw336k6SiVEDWcNm_in0ZFCYfy7s"/>
                </div>
            </section>

            <!-- Current Location Indicator / Map -->
            <section class="relative group">
                <div class="absolute -top-3 left-4 z-[1001] bg-[#006837] text-white px-3 py-1 rounded-full text-xs font-bold tracking-wider flex items-center gap-1 shadow-md cursor-pointer hover:bg-[#004d27]" id="btn-locate">
                    <span class="material-symbols-outlined text-[14px] animate-pulse" id="icon-locate">my_location</span>
                    <span id="text-locate">UBICACIÓN ACTUAL</span>
                </div>
                <div class="w-full h-48 rounded-xl overflow-hidden border-2 border-[#bec9be] shadow-lg bg-[#eeeef0] relative">
                    <div id="map" class="absolute inset-0 z-0"></div>
                    
                    <!-- Location Label Overlay -->
                    <div class="absolute bottom-3 left-3 right-3 bg-white/95 backdrop-blur-sm p-3 rounded-lg border border-[#bec9be] flex items-center gap-3 z-[1000] shadow-sm">
                        <span class="material-symbols-outlined text-[#964900]">location_on</span>
                        <input type="text" id="direccion_referencia" name="direccion_referencia" placeholder="Dirección o Referencia" class="font-bold text-[14px] text-[#1a1c1e] bg-transparent border-none p-0 focus:ring-0 w-full" required value="{{ old('direccion_referencia', 'Buscando ubicación...') }}">
                    </div>
                </div>
                <!-- Hidden inputs for coordinates -->
                <input type="hidden" id="latitud" name="latitud" value="{{ old('latitud', '-16.4958') }}">
                <input type="hidden" id="longitud" name="longitud" value="{{ old('longitud', '-68.1335') }}">
            </section>

            <!-- Obstacle Categories Grid -->
            <section class="space-y-4">
                <h3 class="text-[24px] font-semibold text-[#1a1c1e]">¿Qué obstáculo encontraste?</h3>
                <input type="hidden" name="tipo_obstaculo" id="tipo_obstaculo" value="{{ old('tipo_obstaculo') }}">
                <input type="hidden" name="nivel_riesgo" id="nivel_riesgo" value="{{ old('nivel_riesgo') }}">
                <input type="hidden" name="metodo_deteccion" id="metodo_deteccion" value="{{ old('metodo_deteccion', auth()->user()?->tieneRol('VOLUNTARIO') ? 'VOLUNTARIO' : 'MANUAL') }}">
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Bento Grid Layout -->
                    <button type="button" class="bento-card group flex flex-col items-center justify-center p-6 bg-white border-2 border-[#bec9be] rounded-xl text-center gap-3 hover:border-[#006837]" onclick="selectCategory(this, 'Ventas Ambulantes')">
                        <span class="material-symbols-outlined text-4xl text-[#004d27] group-hover:scale-110 transition-transform">storefront</span>
                        <span class="font-bold text-[14px] text-[#1a1c1e]">Ventas Ambulantes</span>
                    </button>
                    <button type="button" class="bento-card group flex flex-col items-center justify-center p-6 bg-white border-2 border-[#bec9be] rounded-xl text-center gap-3 hover:border-[#006837]" onclick="selectCategory(this, 'Bache')">
                        <span class="material-symbols-outlined text-4xl text-[#964900] group-hover:scale-110 transition-transform" style="font-variation-settings: 'FILL' 1;">warning</span>
                        <span class="font-bold text-[14px] text-[#1a1c1e]">Baches / Huecos</span>
                    </button>
                    <button type="button" class="bento-card group flex flex-col items-center justify-center p-6 bg-white border-2 border-[#bec9be] rounded-xl text-center gap-3 hover:border-[#006837]" onclick="selectCategory(this, 'Escombros')">
                        <span class="material-symbols-outlined text-4xl text-[#3f4940] group-hover:scale-110 transition-transform">construction</span>
                        <span class="font-bold text-[14px] text-[#1a1c1e]">Obras en Vía</span>
                    </button>
                    <button type="button" class="bento-card group flex flex-col items-center justify-center p-6 bg-white border-2 border-[#bec9be] rounded-xl text-center gap-3 hover:border-[#006837]" onclick="selectCategory(this, 'Semáforo Dañado')">
                        <span class="material-symbols-outlined text-4xl text-[#ba1a1a] group-hover:scale-110 transition-transform">traffic</span>
                        <span class="font-bold text-[14px] text-[#1a1c1e]">Semáforo Malogrado</span>
                    </button>
                    <button type="button" class="col-span-2 bento-card group flex items-center justify-start p-6 bg-white border-2 border-[#bec9be] rounded-xl text-left gap-6 hover:border-[#006837]" onclick="selectCategory(this, 'Acera Dañada')">
                        <div class="bg-[#eeeef0] p-3 rounded-full">
                            <span class="material-symbols-outlined text-4xl text-[#004d27] group-hover:scale-110 transition-transform">blind</span>
                        </div>
                        <span class="text-[24px] font-semibold text-[#1a1c1e]">Falta de Guía Táctil</span>
                    </button>
                </div>
            </section>

            <!-- Descripción (Oculto inicialmente o visible con estilo) -->
            <section class="space-y-4">
                <h3 class="text-[20px] font-semibold text-[#1a1c1e]">Detalles Adicionales</h3>
                <textarea id="descripcion" name="descripcion" rows="3" required class="w-full rounded-xl border-2 border-[#bec9be] p-4 text-[#1a1c1e] font-medium focus:border-[#006837] focus:ring-0 resize-none shadow-sm" placeholder="Describe el problema o usa la nota de voz...">{{ old('descripcion') }}</textarea>
            </section>

            <!-- Documentation Controls -->
            <section class="grid grid-cols-2 gap-4">
                <button type="button" onclick="document.getElementById('foto').click()" class="flex flex-col items-center gap-2 p-5 rounded-xl border-2 border-[#006837] text-[#004d27] font-semibold hover:bg-[#006837]/5 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 0;">photo_camera</span>
                    Capturar Foto
                </button>
                <input type="file" id="foto" name="foto" accept="image/*" class="hidden">
                
                <button type="button" class="flex flex-col items-center gap-2 p-5 rounded-xl border-2 border-[#ff8928] text-[#964900] font-semibold hover:bg-[#ff8928]/5 active:scale-95 transition-all relative overflow-hidden" id="voice-btn">
                    <span class="material-symbols-outlined text-3xl">mic</span>
                    Nota de Voz
                    <div class="absolute inset-0 bg-[#ff8928]/10 hidden" id="recording-pulse"></div>
                </button>
            </section>

            <!-- Submit Action -->
            <section class="pt-4 pb-12">
                <button type="submit" id="submit-btn" class="w-full h-16 bg-[#006837] text-white rounded-xl font-semibold text-xl shadow-[0_8px_24px_rgba(0,104,55,0.3)] hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                    <span>Enviar Reporte</span>
                    <span class="material-symbols-outlined">send</span>
                </button>
            </section>
        </form>

    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Form Logic
        const riskMap = {
            'Ventas Ambulantes': 'BAJO',
            'Bache': 'MEDIO',
            'Escombros': 'MEDIO',
            'Semáforo Dañado': 'ALTO',
            'Acera Dañada': 'CRITICO'
        };

        function selectCategory(el, categoryName) {
            document.querySelectorAll('.bento-card').forEach(card => {
                card.classList.remove('active-category');
            });
            el.classList.add('active-category');
            document.getElementById('tipo_obstaculo').value = categoryName;
            document.getElementById('nivel_riesgo').value = riskMap[categoryName] || 'MEDIO';
            
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        }

        const voiceBtn = document.getElementById('voice-btn');
        const pulse = document.getElementById('recording-pulse');
        const descripcionInput = document.getElementById('descripcion');
        let isRecording = false;
        let recognition;

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = true;
            recognition.lang = 'es-ES';

            recognition.onresult = function(event) {
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    }
                }
                if (finalTranscript !== '') {
                    // Append the voice text to the textarea
                    descripcionInput.value = (descripcionInput.value + ' ' + finalTranscript).trim();
                }
            };

            recognition.onend = function() {
                if (isRecording) {
                    stopRecording();
                }
            };
            
            recognition.onerror = function(event) {
                console.error('Error de reconocimiento de voz', event.error);
                stopRecording();
            };
        } else {
            // Hide the button if voice recognition is not supported in this browser
            voiceBtn.style.display = 'none';
        }

        function startRecording() {
            isRecording = true;
            pulse.classList.remove('hidden');
            voiceBtn.querySelector('.material-symbols-outlined').style.fontVariationSettings = "'FILL' 1";
            voiceBtn.querySelector('.material-symbols-outlined').classList.add('text-[#ba1a1a]');
            voiceBtn.querySelector('.material-symbols-outlined').classList.remove('text-[#964900]');
            if(recognition) recognition.start();
        }

        function stopRecording() {
            isRecording = false;
            pulse.classList.add('hidden');
            voiceBtn.querySelector('.material-symbols-outlined').style.fontVariationSettings = "'FILL' 0";
            voiceBtn.querySelector('.material-symbols-outlined').classList.remove('text-[#ba1a1a]');
            voiceBtn.querySelector('.material-symbols-outlined').classList.add('text-[#964900]');
            if(recognition) recognition.stop();
        }

        voiceBtn.addEventListener('click', () => {
            if (!isRecording) {
                startRecording();
            } else {
                stopRecording();
            }
        });

        // Validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if(!document.getElementById('tipo_obstaculo').value) {
                e.preventDefault();
                alert('Por favor selecciona el tipo de obstáculo haciendo clic en una de las opciones.');
            }
        });

        // Leaflet Map Logic & Geolocation
        document.addEventListener('DOMContentLoaded', function() {
            var defaultLat = parseFloat(document.getElementById('latitud').value) || -16.4958;
            var defaultLng = parseFloat(document.getElementById('longitud').value) || -68.1335;
            
            var map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLng], 16);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OSM'
            }).addTo(map);

            var marker = L.marker([defaultLat, defaultLng]).addTo(map);

            function updateAddress(lat, lng) {
                document.getElementById('direccion_referencia').value = "Buscando dirección...";
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data && data.address) {
                            var street = data.address.road || data.address.pedestrian || data.address.suburb || "Ubicación seleccionada";
                            var city = data.address.city || data.address.town || data.address.village || "La Paz";
                            document.getElementById('direccion_referencia').value = `${street}, ${city}`;
                        } else {
                            document.getElementById('direccion_referencia').value = "Dirección no encontrada";
                        }
                    })
                    .catch(err => {
                        document.getElementById('direccion_referencia').value = "Ubicación manual";
                    });
            }

            function setLocation(lat, lng, doGeocode = true) {
                lat = parseFloat(lat).toFixed(8);
                lng = parseFloat(lng).toFixed(8);
                
                document.getElementById('latitud').value = lat;
                document.getElementById('longitud').value = lng;
                
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 17);
                
                if(doGeocode) updateAddress(lat, lng);
            }

            // Auto-locate
            function autoLocate() {
                var btnText = document.getElementById('text-locate');
                var btnIcon = document.getElementById('icon-locate');
                btnText.innerText = "LOCALIZANDO...";
                btnIcon.classList.add('animate-spin');

                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        setLocation(position.coords.latitude, position.coords.longitude);
                        btnText.innerText = "UBICACIÓN ACTUAL";
                        btnIcon.classList.remove('animate-spin');
                    }, function(error) {
                        btnText.innerText = "USAR GPS";
                        btnIcon.classList.remove('animate-spin');
                        if(document.getElementById('direccion_referencia').value === "Buscando ubicación...") {
                            document.getElementById('direccion_referencia').value = "Por favor selecciona en el mapa";
                        }
                    }, { enableHighAccuracy: true, timeout: 5000 });
                } else {
                    btnText.innerText = "GPS NO SOPORTADO";
                }
            }

            // Click on map to set location
            map.on('click', function(e) {
                setLocation(e.latlng.lat, e.latlng.lng);
            });
            
            document.getElementById('btn-locate').addEventListener('click', autoLocate);

            // Re-apply old selections if they exist (after validation error)
            var oldCategory = document.getElementById('tipo_obstaculo').value;
            if(oldCategory) {
                document.querySelectorAll('.bento-card').forEach(card => {
                    if(card.innerText.includes(oldCategory) || card.innerHTML.includes(oldCategory)) {
                        card.classList.add('active-category');
                    }
                });
            }

            // Trigger autoLocate on load if no old category exists (first visit)
            if(!oldCategory) {
                autoLocate();
            }
        });
    </script>
    @endpush
</x-app-layout>
