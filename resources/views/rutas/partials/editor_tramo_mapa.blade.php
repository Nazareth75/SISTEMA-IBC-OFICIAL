<section class="my-4 border rounded p-3 uppercase" aria-label="Definir tramo desde el mapa">
    <h4 class="font-bold">DEFINIR TRAMO SOBRE EL TRAZADO REGISTRADO</h4>
    <p class="text-sm">ELIGE UNA GEOMETRÍA Y MARCA INICIO Y FIN EN ESQUINAS, CRUCES O CONEXIONES. SE CONSERVAN LOS PUNTOS INTERMEDIOS DEL TRAZADO.</p>
    <label>GEOMETRÍA BASE <select id="base-geometria-tramo" title="TRAZADO SOBRE EL QUE SE DELIMITARÁ EL TRAMO"></select></label>
    <button type="button" id="limpiar-geometria-tramo" class="border rounded p-2">REINICIAR SELECCIÓN</button>
    <div id="editor-geometria-tramo" style="height:320px" class="mt-3"></div>
    <p id="estado-editor-tramo" role="status">SELECCIONA UN TRAZADO Y MARCA EL INICIO.</p>
</section>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const estado=document.getElementById('estado-editor-tramo');
    if(!window.L) {estado.textContent='MAPA NO DISPONIBLE.';return;}
    const bases={{ Illuminate\Support\Js::from(array_merge(
        !empty($ruta->geo_rut['trayecto_coords'] ?? $ruta->geo_rut['coordenadas'] ?? []) ? [['nombre'=>'TRAZADO BASE REGISTRADO (VALIDAR RED PEATONAL)','geo'=>$ruta->geo_rut['trayecto_coords'] ?? $ruta->geo_rut['coordenadas']]] : [],
        $ruta->tramos->map(fn($t)=>['nombre'=>$t->nom_ctr,'geo'=>$t->geo_ctr])->values()->all()
    )) }}.filter(b=>Array.isArray(b.geo) && b.geo.length>=2);
    const selector=document.getElementById('base-geometria-tramo'),form=document.getElementById('form-nuevo-tramo');
    const campo=form.querySelector('[name="geo_ctr"]'),longitud=form.querySelector('[name="lon_ctr"]');
    const mapa=L.map('editor-geometria-tramo').setView([-16.5,-68.133],14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap contributors',maxZoom:19}).addTo(mapa);
    const capas=L.layerGroup().addTo(mapa); let inicio=null,seleccion=null;
    bases.forEach((b,i)=>{const o=document.createElement('option');o.value=i;o.textContent=b.nombre;selector.append(o);});
    function dibujar(){
        capas.clearLayers();inicio=null;seleccion=null;campo.value='';
        const base=bases[Number(selector.value)];
        if(!base){estado.textContent='PRIMERO REGISTRA UNA GEOMETRÍA VIAL PARA LA RUTA.';return;}
        const linea=L.polyline(base.geo.map(p=>[p.lat,p.lng]),{color:'#64748b',weight:6}).addTo(capas);
        mapa.fitBounds(linea.getBounds(),{padding:[20,20]});
        estado.textContent='MARCA EL INICIO SOBRE LA LÍNEA GRIS.';
    }
    selector.addEventListener('change',dibujar);document.getElementById('limpiar-geometria-tramo').addEventListener('click',dibujar);
    mapa.on('click',e=>{
        const base=bases[Number(selector.value)];if(!base)return;
        let menor=Infinity,indice=0;
        base.geo.forEach((p,i)=>{const d=e.latlng.distanceTo([p.lat,p.lng]);if(d<menor){menor=d;indice=i;}});
        if(menor>25){estado.textContent='MARCA UN PUNTO SOBRE EL TRAZADO VIAL REGISTRADO.';return;}
        if(inicio===null){inicio=indice;L.marker([base.geo[indice].lat,base.geo[indice].lng]).addTo(capas);estado.textContent='AHORA MARCA EL FIN EN UNA ESQUINA O CONEXIÓN.';return;}
        if(indice===inicio){estado.textContent='INICIO Y FIN DEBEN SER DISTINTOS.';return;}
        const puntos=base.geo.slice(Math.min(inicio,indice),Math.max(inicio,indice)+1);if(indice<inicio)puntos.reverse();
        if(seleccion)capas.removeLayer(seleccion);
        seleccion=L.polyline(puntos.map(p=>[p.lat,p.lng]),{color:'#15803d',weight:8}).addTo(capas);
        campo.value=JSON.stringify(puntos);
        let metros=0;for(let i=1;i<puntos.length;i++)metros+=L.latLng(puntos[i-1].lat,puntos[i-1].lng).distanceTo([puntos[i].lat,puntos[i].lng]);
        longitud.value=metros.toFixed(2);estado.textContent='TRAMO SELECCIONADO: '+Math.round(metros)+' M. REVISA NOMBRE Y TIPO ANTES DE GUARDAR.';
    });
    dibujar();
});
</script>
@endpush
