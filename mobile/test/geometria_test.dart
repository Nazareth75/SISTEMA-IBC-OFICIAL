import 'package:flutter_test/flutter_test.dart';
import 'package:latlong2/latlong.dart';
import 'package:ibc_movilidad/geometria.dart';

void main() {
  test('Mide distancia al segmento completo, no solo a sus extremos', () {
    final tramo = [
      [const LatLng(-16.5, -68.13), const LatLng(-16.5, -68.12)],
    ];
    expect(
      distanciaAlTrazado(const LatLng(-16.5, -68.125), tramo),
      lessThan(0.01),
    );
    expect(
      distanciaAlTrazado(const LatLng(-16.501, -68.125), tramo),
      greaterThan(100),
    );
  });
}
