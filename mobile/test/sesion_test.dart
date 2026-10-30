import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:ibc_movilidad/api.dart';
import 'package:ibc_movilidad/sesion_segura.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  test(
    'Identifica una vez y las siguientes peticiones usan solo Bearer',
    () async {
      var llamadas = 0;
      final api = MovilidadApi(
        'http://ibc.test/',
        client: MockClient((req) async {
          llamadas++;
          if (llamadas == 1) {
            expect(jsonDecode(req.body), {'ci': 'TEST-CI', 'nombre': 'MARÍA'});
            return http.Response(
              jsonEncode({'token': 'credencial', 'nombre': 'MARÍA'}),
              200,
            );
          }
          expect(req.headers['Authorization'], 'Bearer credencial');
          expect(req.body, isEmpty);
          expect(req.url.path, '/api/movilidad/sesion');
          return http.Response(jsonEncode({'nombre': 'MARÍA'}), 200);
        }),
      );
      expect(await api.login('TEST-CI', 'MARÍA'), 'MARÍA');
      await api.request('sesion');
      expect(llamadas, 2);
    },
  );
  test(
    '401 requiere identificación; fallo de red conserva la credencial',
    () async {
      final api = MovilidadApi(
        'http://ibc.test',
        client: MockClient((_) async => http.Response('{}', 401)),
      )..token = 'vigente';
      await expectLater(
        api.request('sesion'),
        throwsA(isA<IdentificacionRequerida>()),
      );
      final sinRed = MovilidadApi(
        'http://ibc.test',
        client: MockClient((_) async => throw http.ClientException('sin red')),
      )..token = 'recordada';
      await expectLater(sinRed.request('sesion'), throwsA(isA<SinConexion>()));
      expect(sinRed.token, 'recordada');
    },
  );
  test('Una ruta que ya no puede recomendarse detiene su uso', () async {
    final api = MovilidadApi(
      'https://ibc.test',
      client: MockClient((_) async => http.Response('{"success":false}', 422)),
    );
    await expectLater(api.ruta(1), throwsA(isA<RecomendacionNoDisponible>()));
  });
  test(
    'Sesión usa el canal seguro y no guarda identificación personal',
    () async {
      String? persistido;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(SesionSegura.canal, (call) async {
            if (call.method == 'save') persistido = call.arguments as String;
            if (call.method == 'clear') persistido = null;
            return call.method == 'read' ? persistido : null;
          });
      final sesion = SesionSegura();
      await sesion.guardar('http://ibc.test', 'credencial', false);
      expect(
        jsonDecode(persistido!).keys,
        unorderedEquals(['servidor', 'token', 'demo']),
      );
      expect((await sesion.leer())!['token'], 'credencial');
      await sesion.borrar();
      expect(await sesion.leer(), isNull);
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(SesionSegura.canal, null);
    },
  );
}
