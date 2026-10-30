import 'package:flutter_test/flutter_test.dart';
import 'package:latlong2/latlong.dart';
import 'package:ibc_movilidad/actualizacion_ruta.dart';

void main() {
  test('Resolver cambia condiciones aunque conserve los tramos ganadores', () {
    final a = <String, dynamic>{
      'tramos_seleccionados_ids': [1, 2],
      'riesgo_total': 0.5,
      'resumen_audio': 'Obra en camino',
    };
    expect(recomendacionCambio(a, Map.from(a)), false);
    expect(
      recomendacionCambio(a, {
        ...a,
        'riesgo_total': 0.0,
        'resumen_audio': 'No constan obstáculos activos',
      }),
      true,
    );
    expect(
      recomendacionCambio(a, {
        ...a,
        'tramos_seleccionados_ids': [3, 4],
      }),
      true,
    );
  });
  test('Voz normaliza acentos y exige confirmación afirmativa explícita', () {
    expect(
      coincideDestino('  Hospital la mérced ', {
        'dst_rut': 'Hospital La Merced',
      }),
      true,
    );
    expect(coincideDestino('', {'dst_rut': 'Hospital La Merced'}), false);
    expect(respuestaAfirmativa('Sí'), true);
    expect(respuestaAfirmativa('no'), false);
    expect(respuestaAfirmativa(null), false);
  });
  final ruta = <String, dynamic>{
    'geometria_recomendada': [
      [
        {'lat': 0.0, 'lng': 0.0},
        {'lat': 0.0, 'lng': 0.001},
        {'lat': 0.0, 'lng': 0.002},
      ],
    ],
    'instrucciones': [
      {'distancia_m': 0},
      {'distancia_m': 111},
      {'distancia_m': 222},
    ],
  };
  test('Reanudación no vuelve a indicar un giro que ya quedó detrás', () {
    expect(
      siguienteInstruccionActualizada(
        ruta,
        const LatLng(0, 0.0015),
        5,
        DateTime.now(),
      ),
      2,
    );
  });
  test(
    'Reanudación exige ubicación reciente precisa y sobre la nueva ruta',
    () {
      expect(
        siguienteInstruccionActualizada(
          ruta,
          const LatLng(1, 1),
          5,
          DateTime.now(),
        ),
        null,
      );
      expect(
        siguienteInstruccionActualizada(
          ruta,
          const LatLng(0, 0.0015),
          90,
          DateTime.now(),
        ),
        null,
      );
      expect(
        siguienteInstruccionActualizada(
          ruta,
          const LatLng(0, 0.0015),
          5,
          DateTime.now().subtract(const Duration(minutes: 1)),
        ),
        null,
      );
    },
  );
}
