<section class="bg-white rounded-lg shadow p-4 mb-4 uppercase" aria-label="Mapa de una ruta seleccionada">
    <h3 class="font-bold mb-2">COMPARAR CAMINOS ACO SOBRE CALLES</h3>
    <p>VERDE GRUESA: CAMINO PRINCIPAL RECOMENDADO. LÍNEAS DELGADAS: ALTERNATIVAS DEL MISMO ORIGEN AL MISMO DESTINO.</p>
    @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<a href="{{ route('navegacion.index') }}?demostracion=1" class="underline">IR A NAVEGACIÓN ACCESIBLE</a>
@endif
    <select id="ruta-catalogo" class="border p-3 w-full" aria-label="Ruta para visualizar">
        <option value="">ELIGE UNA RUTA</option>
        @foreach($rutas as $r)<option value="{{ route('rutas.asistente',$r) }}" data-demo-osm="{{ $r->ide_rut==='DEMO-RUT-LP-OSM'?'1':'0' }}">{{ $r->nom_rut }}</option>@endforeach
    </select>
    @if(strtoupper(Auth::user()->role?->nom_rol ?? '') === 'ADMINISTRADOR_NO_VIDENTE')
<button type="button" id="asistente-mapa" data-asistente-ruta="" disabled style="background:#166534;color:white;padding:14px;border-radius:8px;margin:12px 0">INICIAR ASISTENTE DE ESTA RUTA</button>
@endif
    <div id="mapa-catalogo-rutas" style="height:360px" aria-label="Mapa de la ruta recomendada"></div>
    <p id="estado-mapa-catalogo" role="status"></p>
    <p>MAYOR GROSOR = MAYOR REFUERZO DE FEROMONA OBTENIDO POR ACO, EN EL TRAZO COLOREADO. CONTORNO DORADO = RECOMENDADA. COMPARA TAMBIÉN RIESGO Y COSTO EN LAS TARJETAS.</p>
    <div id="caminos-catalogo-lista" aria-label="Comparación de caminos completos"></div>
</section>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/mapa-aco.js') }}"></script>
<script>
(() => {
    const estado=document.getElementById('estado-mapa-catalogo');
    if(!window.L){estado.textContent='MAPA NO DISPONIBLE. UTILIZA LA LISTA ACCESIBLE.';return;}
    const map=L.map('mapa-catalogo-rutas').setView([-16.5,-68.133],14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors'}).addTo(map);
    let solicitud=null;
    const selector=document.getElementById('ruta-catalogo'),asistente=document.getElementById('asistente-mapa');
    selector.onchange=async e=>{
        if(asistente)asistente.disabled=true;
        if(solicitud)solicitud.abort();solicitud=new AbortController();
        if(!e.target.value)return;
        estado.textContent='CONSULTANDO LA RECOMENDACIÓN ACO…';
        try {
            const response=await fetch(e.target.value,{headers:{Accept:'application/json'},signal:solicitud.signal});
            const data=await response.json();if(!response.ok||!data.success)throw Error(data.mensaje_error||'Ruta no disponible.');
            window.renderizarCaminosAco(map,data,{alternativas:true,panel:'caminos-catalogo-lista'});
            if(asistente){asistente.dataset.asistenteRuta=e.target.value;asistente.disabled=false;}
            estado.textContent=`${data.demostracion?'DATOS SIMULADOS. ':''}${data.destino}. ${data.distancia_total} M. ${data.cantidad_alternativas} CAMINOS.`;
        }catch(error){if(error.name!=='AbortError')estado.textContent=error.message;}
    };
    const inicial=[...selector.options].find(o=>o.dataset.demoOsm==='1') || selector.options[1];
    if(inicial){selector.value=inicial.value;selector.dispatchEvent(new Event('change'));}
})();
</script>
@endpush
