import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:ibc_movilidad/main.dart';
import 'package:ibc_movilidad/api.dart';
import 'package:ibc_movilidad/configuracion.dart';
import 'package:ibc_movilidad/sesion_segura.dart';

void main() {
  const tts = MethodChannel('flutter_tts');
  String? guardada;
  final hablados = <String>[];
  setUp(() {
    guardada = null;
    hablados.clear();
    final messenger =
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
    messenger.setMockMethodCallHandler(tts, (call) async {
      if (call.method == 'speak') {
        hablados.add(call.arguments as String);
      }
      return 1;
    });
    messenger.setMockMethodCallHandler(
      SesionSegura.canal,
      (call) async => call.method == 'read' ? guardada : null,
    );
  });
  tearDown(() {
    final m = TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
    m.setMockMethodCallHandler(tts, null);
    m.setMockMethodCallHandler(SesionSegura.canal, null);
  });
  testWidgets('Primera apertura habla y solo ofrece vinculación accesible', (
    tester,
  ) async {
    await tester.pumpWidget(const MaterialApp(home: Movilidad()));
    await tester.pumpAndSettle();
    expect(find.text('Carnet de identidad'), findsOneWidget);
    expect(find.text('Nombre'), findsOneWidget);
    expect(find.text('Servidor Laravel'), findsNothing);
    expect(find.text('Datos de demostración'), findsNothing);
    expect(find.text('REINTENTAR SESIÓN GUARDADA'), findsNothing);
    expect(
      hablados.any((s) => s.contains('Vamos a configurar tu acceso')),
      true,
    );
    await tester.pumpWidget(const SizedBox());
  });
  testWidgets('Segunda apertura valida token y entra sin pedir CI ni nombre', (
    tester,
  ) async {
    guardada = jsonEncode({
      'servidor': ConfiguracionMovil.baseUrl,
      'token': 'guardado',
      'demo': false,
    });
    final paths = <String>[];
    await tester.pumpWidget(
      MaterialApp(
        home: Movilidad(
          crearApi: (base) => MovilidadApi(
            base,
            client: MockClient((req) async {
              paths.add(req.url.path);
              expect(req.headers['Authorization'], 'Bearer guardado');
              return http.Response(
                jsonEncode(
                  req.url.path.endsWith('/sesion')
                      ? {'nombre': 'ISRAEL'}
                      : {'data': [], 'last_page': 1},
                ),
                200,
              );
            }),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.text('Hola, ISRAEL'), findsOneWidget);
    expect(find.text('Carnet de identidad'), findsNothing);
    expect(paths.any((p) => p.endsWith('/login')), false);
    expect(hablados.any((s) => s.contains('Presiona volumen arriba')), true);
    await tester.pumpWidget(const SizedBox());
  });
}
