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
    expect(find.text('DICTAR CARNET DE IDENTIDAD'), findsOneWidget);
    expect(find.text('Nombre'), findsNothing);
    expect(find.text('Servidor Laravel'), findsNothing);
    expect(find.text('Datos de demostración'), findsNothing);
    expect(find.text('REINTENTAR SESIÓN GUARDADA'), findsNothing);
    expect(
      hablados.any((s) => s.contains('Dicte su carnet de identidad')),
      true,
    );
    await tester.pumpWidget(const SizedBox());
  });
  testWidgets('Dicta carnet, confirma y entra sin nombre', (tester) async {
    final respuestas = <String?>[
      'uno dos tres cuatro cinco seis siete',
      'sí',
      null,
    ];
    var logins = 0;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(SesionSegura.canal, (call) async {
          if (call.method == 'voice') return respuestas.removeAt(0);
          if (call.method == 'save') guardada = call.arguments as String;
          return null;
        });
    await tester.pumpWidget(
      MaterialApp(
        home: Movilidad(
          crearApi: (base) => MovilidadApi(
            base,
            client: MockClient((req) async {
              if (req.url.path.endsWith('/login')) {
                logins++;
                expect(jsonDecode(req.body), {'ci': '1234567'});
                return http.Response(
                  jsonEncode({'token': 'nuevo', 'nombre': 'ISRAEL'}),
                  200,
                );
              }
              expect(req.headers['Authorization'], 'Bearer nuevo');
              return http.Response(
                jsonEncode({'data': [], 'last_page': 1}),
                200,
              );
            }),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(logins, 1);
    expect(find.text('Hola, ISRAEL'), findsOneWidget);
    expect(jsonDecode(guardada!)['token'], 'nuevo');
    expect(hablados.any((s) => s.contains('Puedes decir:')), true);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('No confirma carnet: no se transmite ni inicia sesión', (
    tester,
  ) async {
    final respuestas = <String?>['1234567', 'no'];
    var peticiones = 0;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(
          SesionSegura.canal,
          (call) async =>
              call.method == 'voice' ? respuestas.removeAt(0) : null,
        );
    await tester.pumpWidget(
      MaterialApp(
        home: Movilidad(
          crearApi: (base) => MovilidadApi(
            base,
            client: MockClient((req) async {
              peticiones++;
              return http.Response('{}', 500);
            }),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(peticiones, 0);
    expect(find.text('DICTAR CARNET DE IDENTIDAD'), findsOneWidget);
    expect(hablados.any((s) => s.contains('Acceso cancelado')), true);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets(
    'Quiere ir al hospital: consulta ruta del servidor y confirma antes de navegar',
    (tester) async {
      guardada = jsonEncode({
        'servidor': ConfiguracionMovil.baseUrl,
        'token': 'guardado',
        'demo': false,
      });
      final respuestas = <String?>[
        'quiero ir al Hospital La Merced',
        'sí',
        'no',
      ];
      final paths = <String>[];
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(
            SesionSegura.canal,
            (call) async => call.method == 'read'
                ? guardada
                : call.method == 'voice'
                ? respuestas.removeAt(0)
                : null,
          );
      await tester.pumpWidget(
        MaterialApp(
          home: Movilidad(
            crearApi: (base) => MovilidadApi(
              base,
              client: MockClient((req) async {
                paths.add(req.url.path);
                if (req.url.path.endsWith('/sesion'))
                  return http.Response(jsonEncode({'nombre': 'ISRAEL'}), 200);
                if (req.url.path.endsWith('/rutas/9'))
                  return http.Response(
                    jsonEncode({
                      'ruta_id': 9,
                      'resumen_audio': 'Ruta recibida del sistema original.',
                      'geometria_recomendada': [],
                      'instrucciones': [],
                      'tramos_seleccionados_ids': [1, 2],
                    }),
                    200,
                  );
                return http.Response(
                  jsonEncode({
                    'data': [
                      {
                        'cod_rutas': 9,
                        'dst_rut': 'Hospital La Merced',
                        'ori_rut': 'IBC',
                        'nom_rut': 'Ruta registrada',
                      },
                    ],
                    'last_page': 1,
                  }),
                  200,
                );
              }),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(paths, contains('/api/movilidad/rutas/9'));
      expect(hablados, contains('Ruta recibida del sistema original.'));
      expect(
        hablados.any((s) => s.contains('¿Quieres iniciar navegación?')),
        true,
      );
      expect(find.text('INICIAR NAVEGACIÓN'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
    },
  );
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
    expect(hablados.any((s) => s.contains('Puedes decir:')), true);
    await tester.pumpWidget(const SizedBox());
  });
}
