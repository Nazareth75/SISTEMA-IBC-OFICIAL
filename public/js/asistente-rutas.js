/* Resumen previo al recorrido. Solo habla tras una acción explícita. */
(() => {
    let solicitud = null;
    const estado = document.getElementById('estado-asistente-rutas');
    function mostrar(texto) { if (estado) estado.textContent = texto; }
    function detener() {
        if (solicitud) solicitud.abort();
        solicitud = null;
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    }
    function hablar(texto) {
        mostrar(texto);
        if (!('speechSynthesis' in window)) {
            mostrar('VOZ NO DISPONIBLE EN ESTE NAVEGADOR. ' + texto);
            return;
        }
        window.speechSynthesis.cancel();
        const voz = new SpeechSynthesisUtterance(texto);
        voz.lang = 'es-BO';
        voz.rate = 1.05;
        voz.onerror = () => mostrar('NO SE PUDO REPRODUCIR LA VOZ. ' + texto);
        window.speechSynthesis.speak(voz);
    }
    document.querySelectorAll('[data-asistente-ruta]').forEach(boton => {
        boton.addEventListener('click', async () => {
            detener();
            const peticion = new AbortController();
            solicitud = peticion;
            boton.disabled = true;
            mostrar('CONSULTANDO RECOMENDACIÓN Y OBSTÁCULOS…');
            try {
                const respuesta = await fetch(boton.dataset.asistenteRuta, {
                    headers: {Accept: 'application/json'}, signal: peticion.signal,
                });
                const datos = await respuesta.json();
                if (!respuesta.ok || !datos.success) throw new Error(datos.mensaje_error || 'No se pudo consultar la ruta.');
                if (solicitud === peticion) hablar(datos.resumen_audio);
            } catch (error) {
                if (error.name !== 'AbortError') mostrar('ERROR: ' + error.message);
            } finally {
                boton.disabled = false;
                if (solicitud === peticion) solicitud = null;
            }
        });
    });
    document.querySelector('[data-detener-asistente]')?.addEventListener('click', () => {
        detener(); mostrar('ASISTENTE DETENIDO.');
    });
    document.querySelector('[data-leer-rutas]')?.addEventListener('click', () => {
        detener();
        const rutas = [...document.querySelectorAll('[data-nombre-ruta]')].map(b => b.dataset.nombreRuta);
        hablar(rutas.length ? 'Rutas de esta página: ' + rutas.join('. ') +
            '. Selecciona Iniciar asistente junto a una ruta para escuchar su resumen.' : 'No hay rutas en esta página.');
    });
    window.addEventListener('pagehide', detener);
})();
