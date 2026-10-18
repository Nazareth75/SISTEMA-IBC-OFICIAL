import 'package:flutter_test/flutter_test.dart';
import 'package:ibc_movilidad/caminos_mapa.dart';

void main() {
  test('width follows backend reinforcement and keeps segment geometry', () {
    CaminoMapa camino(double refuerzo) => CaminoMapa({
      'nombre': 'Camino',
      'recomendada': true,
      'refuerzo_relativo': refuerzo,
      'tramos_ids': [12],
      'tramos_nombres': ['Acera'],
      'geometria': [
        [
          {'lat': -16.5, 'lng': -68.13},
          {'lat': -16.501, 'lng': -68.132},
        ],
      ],
    });
    expect(camino(0).grosor, 3);
    expect(camino(0.5).grosor, 8.5);
    expect(camino(1).grosor, 14);
    expect(camino(0.5).tramos, [12]);
    expect(camino(0.5).geometria.single.last.longitude, -68.132);
  });
}
