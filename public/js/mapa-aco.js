/* Solo geometría de caminos del grafo real; no calcula rutas en el navegador. */
window.renderizarCaminosAco = function(map, data, opciones = {}) {
    if (!map || !window.L || !data) return;
    (map._capasCaminosAco || []).forEach(c => map.removeLayer(c));
    map._capasCaminosAco = [];
    if (map._selectorAco) map.removeControl(map._selectorAco);
    const caminos = data.caminos_candidatos || [];
    const colores = ['#15803d','#2563eb','#b45309','#7c3aed','#be123c'];
    const overlays = {};
    const bounds = L.latLngBounds([]);
    const panel = document.getElementById(opciones.panel || 'caminos-aco-lista');
    if (panel) panel.replaceChildren();
    const texto = (tag, valor) => { const el = document.createElement(tag); el.textContent = valor; return el; };
    const resumen = camino => {
        const div = document.createElement('div');
        div.append(texto('strong',camino.nombre+(camino.mas_corta?' - MAS CORTA':'')),texto('p',camino.tramos_nombres.join(' → ')),
            texto('p',`${camino.distancia.toFixed(1)} m · Riesgo agregado: ${camino.riesgo == null ? 'SIN EVALUAR' : camino.riesgo.toFixed(3)} · Costo: ${camino.costo.toFixed(4)}`),
            texto('p',`Refuerzo relativo: ${(camino.refuerzo_relativo*100).toFixed(1)} % · Media de feromona ponderada por distancia: ${camino.feromona_media_ponderada.toPrecision(4)}`));
        return div;
    };
    let grupoRecomendada = null;
    caminos.forEach((camino,i) => {
        const grupo = L.featureGroup();
        if (camino.recomendada) grupoRecomendada = grupo;
        const color = opciones.celular ? (camino.recomendada ? '#166534' : '#1e88e5') : (camino.recomendada ? '#15803d' : colores[1 + (i % (colores.length-1))]);
        const grosor = camino.recomendada ? 10 : Math.max(3, Math.round(3 + 5 * (camino.refuerzo_relativo || 0.4)));
        camino.geometria.forEach((geo,indiceTramo) => {
            if (!Array.isArray(geo) || geo.length<2) return;
            const puntos = geo.map(p => [p.lat,p.lng]);
            // El halo blanco solo identifica la recomendación destacándola; la línea verde gruesa representa el camino seguro.
            if(camino.recomendada && !opciones.celular) L.polyline(puntos,{color:'#ffffff',weight:grosor+4,opacity:0.85,interactive:false}).addTo(grupo);
            const fichaTramo = resumen(camino);
            fichaTramo.append(texto('strong', `TRAMO ${camino.tramos_ids[indiceTramo]}: ${camino.tramos_nombres[indiceTramo]}`));
            L.polyline(puntos,{color,weight:grosor,opacity:camino.recomendada?1:0.62,
                dashArray:opciones.celular?null:(camino.recomendada?null:'10 5')}).bindPopup(fichaTramo,{maxWidth:420}).addTo(grupo);
        });
        const nombreCapa = camino.recomendada
            ? '★ RUTA RECOMENDADA (MENOR RIESGO · LÍNEA GRUESA)'
            : `${camino.nombre} (LÍNEA DELGADA)`;
        overlays[nombreCapa] = grupo;
        map._capasCaminosAco.push(grupo);
        if (camino.recomendada || opciones.alternativas) grupo.addTo(map);
        if ((camino.recomendada || opciones.alternativas) && grupo.getLayers().length) bounds.extend(grupo.getBounds());
        if(panel) {
            const articulo = texto('article',''); articulo.style.cssText='padding:12px;border:1px solid #cbd5e1;border-left:6px solid '+color+';margin:8px 0;border-radius:8px';
            const boton = texto('button',camino.nombre+' · '+camino.distancia.toFixed(1)+' M');
            boton.type='button';boton.style.cssText='font-weight:bold;text-align:left;padding:10px';
            boton.onclick=()=>{grupo.addTo(map);map.fitBounds(grupo.getBounds(),{padding:[25,25]});};
            articulo.append(boton,resumen(camino));panel.append(articulo);
        }
    });
    const recomendada = caminos.find(c => c.recomendada);
    if(grupoRecomendada) grupoRecomendada.bringToFront();
    if(recomendada && L.circleMarker && (opciones.mostrarNodos || opciones.celular)) {
        const grupoNodos = L.featureGroup();
        recomendada.geometria.forEach((geo,i) => {
            if(!geo.length)return;
            const p=geo[0];
            L.circleMarker([p.lat,p.lng],{radius:4,color:'#475569',fillColor:'#fff',fillOpacity:0.9,weight:1.5})
                .bindTooltip(opciones.celular ? `T${recomendada.tramos_ids[i]}` : `Tramo ${recomendada.tramos_ids[i]}: ${recomendada.tramos_nombres[i]}`,{permanent:!!opciones.celular,direction:'top'})
                .addTo(grupoNodos);
        });
        map._capasCaminosAco.push(grupoNodos);
        if(opciones.celular) grupoNodos.addTo(map);
        else overlays['CONEXIONES DE TRAMOS'] = grupoNodos;
    }
    if(recomendada?.geometria.length) {
        const inicio=recomendada.geometria[0][0],ultimo=recomendada.geometria.at(-1),fin=ultimo.at(-1);
        for(const [p,nombre] of [[inicio,'ORIGEN'],[fin,'DESTINO']]) {
            const marcador=L.marker([p.lat,p.lng]).addTo(map).bindPopup(texto('strong',nombre));
            map._capasCaminosAco.push(marcador);
        }
    }
    if(opciones.tecnico) {
        for(const [nombre,layer] of Object.entries(map._referenciasTecnicas || {})) overlays[nombre]=layer;
    }
    map._selectorAco=L.control.layers({},overlays,{collapsed:false}).addTo(map);
    if(bounds.isValid()) map.fitBounds(bounds,{padding:[35,35]});
    map._referenciasTecnicas?.['ETIQUETAS DE TRAMOS']?.bringToFront?.();
};
