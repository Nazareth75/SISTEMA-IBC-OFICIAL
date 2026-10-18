import 'package:latlong2/latlong.dart';

// Geometry and pheromone reinforcement come exclusively from Laravel ACO.
class CaminoMapa {
  CaminoMapa(Map<String, dynamic> datos)
    : nombre = datos['nombre'] as String,
      recomendada = datos['recomendada'] == true,
      refuerzo = (datos['refuerzo_relativo'] as num).toDouble().clamp(0, 1),
      tramos = List<int>.from(datos['tramos_ids']),
      nombresTramos = List<String>.from(datos['tramos_nombres']),
      geometria = (datos['geometria'] as List)
          .map(
            (s) => (s as List)
                .map(
                  (p) => LatLng(
                    (p['lat'] as num).toDouble(),
                    (p['lng'] as num).toDouble(),
                  ),
                )
                .toList(),
          )
          .toList();
  final String nombre;
  final bool recomendada;
  final double refuerzo;
  final List<int> tramos;
  final List<String> nombresTramos;
  final List<List<LatLng>> geometria;
  double get grosor => 3 + 11 * refuerzo;
}
