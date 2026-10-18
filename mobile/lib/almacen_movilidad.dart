import 'dart:convert';
import 'sesion_segura.dart';

/// Ruta y evidencias cifradas con Keystore; aisladas por credencial móvil.
class AlmacenMovilidad {
  Map<String, dynamic> datos = {};
  Future<void> _escrituras = Future.value();
  Future<void> abrir(String token) async {
    final raw = await SesionSegura.canal.invokeMethod<String>('readLocal');
    final anterior = raw == null
        ? <String, dynamic>{}
        : Map<String, dynamic>.from(jsonDecode(raw));
    datos = anterior['token'] == token
        ? anterior
        : {'token': token, 'pendientes': <dynamic>[]};
  }

  List<Map<String, dynamic>> get pendientes =>
      (datos['pendientes'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
  Map<String, dynamic>? get ruta =>
      datos['ruta'] == null ? null : Map<String, dynamic>.from(datos['ruta']);
  Future<void> guardar() {
    final snapshot = jsonEncode(datos);
    final operacion = _escrituras.then(
      (_) => SesionSegura.canal.invokeMethod<void>('saveLocal', snapshot),
    );
    _escrituras = operacion.catchError((Object _) {});
    return operacion;
  }

  Future<void> agregar(Map<String, dynamic> evento) async {
    final cola = pendientes;
    if (!cola.any((e) => e['evento_uuid'] == evento['evento_uuid'])) {
      cola.add(evento);
    }
    datos['pendientes'] = cola;
    await guardar();
  }

  Future<void> confirmar(String uuid) async {
    datos['pendientes'] = pendientes
        .where((e) => e['evento_uuid'] != uuid)
        .toList();
    await guardar();
  }

  Future<void> guardarRuta(Map<String, dynamic> ruta) async {
    datos['ruta'] = ruta;
    await guardar();
  }

  Future<void> borrar() async {
    await _escrituras;
    datos = {};
    await SesionSegura.canal.invokeMethod<void>('clearLocal');
  }
}
