import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:ibc_movilidad/sesion_segura.dart';
import 'package:ibc_movilidad/almacen_movilidad.dart';
import 'package:ibc_movilidad/configuracion.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  String? disco;
  setUp(() {
    disco = null;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(SesionSegura.canal, (call) async {
          if (call.method == 'saveLocal') disco = call.arguments as String;
          if (call.method == 'clearLocal') disco = null;
          return call.method == 'readLocal' ? disco : null;
        });
  });
  tearDown(
    () => TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(SesionSegura.canal, null),
  );
  test('Ruta y cola sobreviven una nueva instancia y conservan UUID', () async {
    final almacen = AlmacenMovilidad();
    await almacen.abrir('sesion-1');
    final ruta = {
      'ruta_id': 12,
      'destino': 'Destino',
      'geometria_recomendada': [
        [
          {'lat': -16.5, 'lng': -68.1},
        ],
      ],
      'instrucciones': [
        {'texto': 'Gira'},
      ],
      'fecha_calculo': '2026-10-18T10:00:00Z',
      'distancia_total': 120,
    };
    await almacen.guardarRuta(ruta);
    final evento = {
      'evento_uuid': 'uuid-1',
      'tipo': 'SOS',
      'estado': 'PENDIENTE',
    };
    await almacen.agregar(evento);
    await almacen.agregar(evento);
    final restaurado = AlmacenMovilidad();
    await restaurado.abrir('sesion-1');
    expect(restaurado.ruta, ruta);
    expect(restaurado.pendientes, [evento]);
    await restaurado.confirmar('uuid-1');
    final tercero = AlmacenMovilidad();
    await tercero.abrir('sesion-1');
    expect(tercero.pendientes, isEmpty);
  });
  test('Escrituras consecutivas y aislamiento entre personas', () async {
    final almacen = AlmacenMovilidad();
    await almacen.abrir('persona-A');
    await Future.wait([
      almacen.agregar({'evento_uuid': 'a'}),
      almacen.agregar({'evento_uuid': 'b'}),
    ]);
    expect((jsonDecode(disco!)['pendientes'] as List).length, 2);
    final otra = AlmacenMovilidad();
    await otra.abrir('persona-B');
    expect(otra.pendientes, isEmpty);
    expect(otra.ruta, isNull);
    await otra.borrar();
    expect(disco, isNull);
  });
  test(
    'BASE_URL acepta HTTPS y rechaza credenciales, query y esquemas ajenos',
    () {
      expect(ConfiguracionMovil.urlValida('https://demo.example.org'), isTrue);
      for (final url in [
        'https://user:secret@example.org',
        'file:///etc/passwd',
        'https://example.org?token=x',
        'https://example.org/admin',
      ]) {
        expect(ConfiguracionMovil.urlValida(url), isFalse);
      }
    },
  );
}
