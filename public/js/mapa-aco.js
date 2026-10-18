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
        div.append(texto('strong',camino.nombre),texto('p',camino.tramos_nombres.join(' → ')),
            texto('p',`${camino.distancia.toFixed(1)} m · Riesgo agregado: ${camino.riesgo.toFixed(3)} · Costo: ${camino.costo.toFixed(4)}`),
            texto('p',`Refuerzo relativo: ${(camino.refuerzo_relativo*100).toFixed(1)} % · Media de feromona ponderada por distancia: ${camino.feromona_media_ponderada.toPrecision(4)}`));
        return div;
    };
    caminos.forEach((camino,i) => {
        const grupo = L.featureGroup();
        const color = colores[i % colores.length];
        const grosor = 3 + 11 * camino.refuerzo_relativo;
        camino.geometria.forEach(geo => {
            if (!Array.isArray(geo) || geo.length<2) return;
            const puntos = geo.map(p => [p.lat,p.lng]);
            // El halo solo identifica la recomendación; el trazo coloreado mide refuerzo.
            if(camino.recomendada) L.polyline(puntos,{color:'#facc15',weight:Math.max(20,grosor+6),opacity:0.8,interactive:false}).addTo(grupo);
            L.polyline(puntos,{color,weight:grosor,opacity:camino.recomendada?1:0.62,
                dashArray:camino.recomendada?null:'10 5'}).bindPopup(resumen(camino),{maxWidth:420}).addTo(grupo);
        });
        overlays[camino.nombre] = grupo;
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
    if(recomendada)map._capasCaminosAco[0]?.bringToFront();
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
};
