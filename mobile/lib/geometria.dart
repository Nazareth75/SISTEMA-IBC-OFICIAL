import 'dart:math' as math;
import 'package:latlong2/latlong.dart';

double distanciaAlTrazado(LatLng posicion, List<List<LatLng>> trazos) {
  var menor = double.infinity;
  final x = 111320 * math.cos(posicion.latitude * math.pi / 180);
  for (final segmento in trazos) {
    for (var i = 1; i < segmento.length; i++) {
      final a = segmento[i - 1], b = segmento[i];
      final ax = (a.longitude - posicion.longitude) * x,
          ay = (a.latitude - posicion.latitude) * 111320;
      final bx = (b.longitude - posicion.longitude) * x,
          by = (b.latitude - posicion.latitude) * 111320;
      final dx = bx - ax, dy = by - ay, l = dx * dx + dy * dy;
      final t = l > 0 ? (-(ax * dx + ay * dy) / l).clamp(0, 1) : 0;
      menor = math.min(
        menor,
        math.sqrt(math.pow(ax + t * dx, 2) + math.pow(ay + t * dy, 2)),
      );
    }
  }
  return menor;
}
