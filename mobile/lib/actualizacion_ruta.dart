import 'dart:convert';
import 'dart:math' as math;
import 'package:latlong2/latlong.dart';

String normalizarDestino(String texto) {
  var s = texto.toLowerCase().trim();
  const a = 'áéíóúüñ', b = 'aeiouun';
  for (var i = 0; i < a.length; i++) {
    s = s.replaceAll(a[i], b[i]);
  }
  return s
      .replaceAll(RegExp(r'[^a-z0-9 ]'), '')
      .replaceAll(RegExp(r'\s+'), ' ');
}

bool coincideDestino(String texto, dynamic ruta) {
  final q = normalizarDestino(texto);
  return q.isNotEmpty &&
      (normalizarDestino('${ruta['dst_rut']}').contains(q) ||
          normalizarDestino('${ruta['nom_rut']}').contains(q));
}

bool respuestaAfirmativa(String? r) => [
  'si',
  'si correcto',
  'correcto',
  'confirmar',
].contains(normalizarDestino(r ?? ''));
bool recomendacionCambio(Map<String, dynamic> a, Map<String, dynamic> b) => [
  'tramos_seleccionados_ids',
  'geometria_recomendada',
  'instrucciones',
  'riesgo_total',
  'costo_total',
  'resumen_audio',
].any((k) => jsonEncode(a[k]) != jsonEncode(b[k]));

/// Enlazar la posición al recorrido del servidor; no calcula una ruta local.
int? siguienteInstruccionActualizada(
  Map<String, dynamic> ruta,
  LatLng posicion,
  double precision,
  DateTime fecha,
) {
  if (precision > 25 || DateTime.now().difference(fecha).inSeconds.abs() > 20) {
    return null;
  }
  final puntos = (ruta['geometria_recomendada'] as List)
      .expand((s) => s as List)
      .map(
        (p) =>
            LatLng((p['lat'] as num).toDouble(), (p['lng'] as num).toDouble()),
      )
      .toList();
  final instrucciones = ruta['instrucciones'] as List;
  var menor = double.infinity, avance = 0.0, acumulada = 0.0;
  final distancia = const Distance();
  final escala = 111320 * math.cos(posicion.latitude * math.pi / 180);
  for (var i = 1; i < puntos.length; i++) {
    final a = puntos[i - 1], b = puntos[i];
    final ax = (a.longitude - posicion.longitude) * escala,
        ay = (a.latitude - posicion.latitude) * 111320;
    final dx = (b.longitude - a.longitude) * escala,
        dy = (b.latitude - a.latitude) * 111320;
    final l = dx * dx + dy * dy;
    final t = l == 0 ? 0.0 : (-(ax * dx + ay * dy) / l).clamp(0.0, 1.0);
    final d = math.sqrt(math.pow(ax + t * dx, 2) + math.pow(ay + t * dy, 2));
    final largo = distancia.as(LengthUnit.Meter, a, b);
    if (d < menor) {
      menor = d;
      avance = acumulada + t * largo;
    }
    acumulada += largo;
  }
  if (menor > 30) return null;
  for (var i = 1; i < instrucciones.length; i++) {
    if ((instrucciones[i]['distancia_m'] as num) >= avance) return i;
  }
  return null;
}
