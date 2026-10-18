import 'package:flutter_test/flutter_test.dart';
import 'package:ibc_movilidad/baston_protocol.dart';

void main() {
  test(
    'Admite ambas versiones de distancias en el orden frente izquierda derecha',
    () {
      expect(BastonProtocol.parse('DIST:10,20,30').valores, [10, 20, 30]);
      expect(BastonProtocol.parse('DISTANCIAS:10,20,30').tipo, 'DIST');
    },
  );
  test('SOS sin GPS no fabrica coordenadas', () {
    expect(BastonProtocol.parse('SOS:Sin GPS').valores, isEmpty);
    expect(
      BastonProtocol.parse('SOS:Ubicación no disponible').valores,
      isEmpty,
    );
    expect(() => BastonProtocol.parse('SOS:NaN,NaN'), throwsFormatException);
    expect(() => BastonProtocol.parse('GPS:91,0'), throwsFormatException);
  });
}
