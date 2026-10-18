(() => {
    const selector=document.getElementById('destino-accesible'),estado=document.getElementById('estado-navegacion');
    const opciones=[...selector.options].map(o=>({value:o.value,text:o.text}));
    let datos=null,mapa=null,ubicacion=null,vigilancia=null,paso=1,ultima='Elige un destino.',solicitud=null;
    function decir(texto){ultima=texto;estado.textContent=texto;if('speechSynthesis'in window){speechSynthesis.cancel();const voz=new SpeechSynthesisUtterance(texto);voz.lang='es-ES';speechSynthesis.speak(voz);}}
    function detener(){if(vigilancia!==null)navigator.geolocation.clearWatch(vigilancia);vigilancia=null;if('speechSynthesis'in window)speechSynthesis.cancel();}
    selector.onchange=()=>{detener();datos=null;document.getElementById('resultado-accesible').hidden=true;estado.textContent='PULSA BUSCAR RUTA SEGURA.';};
    document.getElementById('buscar-destino').oninput=e=>{
        const filtro=e.target.value.toLocaleLowerCase();selector.replaceChildren();
        opciones.filter(o=>!o.value||o.text.toLocaleLowerCase().includes(filtro)).forEach(o=>selector.add(new Option(o.text,o.value)));
        selector.onchange();
    };
    document.getElementById('buscar-ruta-accesible').onclick=async()=>{
        detener();datos=null;document.getElementById('resultado-accesible').hidden=true;
        if(solicitud)solicitud.abort();solicitud=new AbortController();
        if(!selector.value){decir('Selecciona un destino.');return;}
        try {
            const r=await fetch(selector.value,{headers:{Accept:'application/json'},signal:solicitud.signal});
            const d=await r.json();if(!r.ok||!d.success)throw Error(d.mensaje_error||'No se pudo obtener una ruta.');
            datos=d;document.getElementById('resultado-accesible').hidden=false;
            document.getElementById('resumen-accesible').textContent=`Destino: ${d.destino}. Desde: ${d.origen}. Distancia: ${d.distancia_total} metros. Riesgo estimado: ${d.nivel_riesgo_estimado}.`;
            if(window.L){mapa??=L.map('mapa-navegacion');if(!mapa._tiles){L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors'}).addTo(mapa);mapa._tiles=true;}
                window.renderizarCaminosAco(mapa,d);mapa.invalidateSize();}
            decir(d.resumen_audio);
        }catch(e){if(e.name!=='AbortError')decir('Error: '+e.message);}
    };
    document.getElementById('iniciar-navegacion').onclick=()=>{
        if(!datos)return;detener();paso=1;
        if(!navigator.geolocation){decir('Ubicación no disponible.');return;}
        decir('Ruta segura iniciada. Guía aproximada; esperando ubicación.');
        vigilancia=navigator.geolocation.watchPosition(pos=>{
            if(pos.coords.accuracy>25){estado.textContent='UBICACIÓN IMPRECISA. GUÍA PAUSADA.';return;}
            const actual=L.latLng(pos.coords.latitude,pos.coords.longitude);
            if(mapa){if(ubicacion)ubicacion.setLatLng(actual);else ubicacion=L.circleMarker(actual,{color:'#1d4ed8',radius:8}).addTo(mapa).bindTooltip('MI UBICACIÓN');}
            const proximo=datos.instrucciones[paso];if(!proximo)return;
            const metros=actual.distanceTo([proximo.punto.lat,proximo.punto.lng]);
            if(metros<20){decir(paso===datos.instrucciones.length-1?'Has llegado al punto de destino según el GPS. Comprueba el acceso.':proximo.texto);paso++;if(paso===datos.instrucciones.length){navigator.geolocation.clearWatch(vigilancia);vigilancia=null;}}
            else estado.textContent=`PRÓXIMA INDICACIÓN A ${Math.round(metros)} METROS.`;
        },e=>decir('No se pudo obtener ubicación: '+e.message),{enableHighAccuracy:true,maximumAge:5000,timeout:15000});
    };
    document.getElementById('repetir-navegacion').onclick=()=>decir(ultima);
    document.getElementById('detener-navegacion').onclick=()=>{detener();estado.textContent='NAVEGACIÓN DETENIDA.';};
    window.addEventListener('pagehide',detener);
})();
