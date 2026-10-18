import 'dart:convert';
import 'package:flutter/services.dart';

/// Android Keystore AES-GCM. Nunca persiste CI ni nombre de identificación.
class SesionSegura {
  static const canal = MethodChannel('bo.ibc/session');
  Future<void> guardar(String servidor, String token, bool demo) =>
      canal.invokeMethod(
        'save',
        jsonEncode({'servidor': servidor, 'token': token, 'demo': demo}),
      );
  Future<Map<String, dynamic>?> leer() async {
    final valor = await canal.invokeMethod<String>('read');
    return valor == null ? null : Map<String, dynamic>.from(jsonDecode(valor));
  }

  Future<void> borrar() => canal.invokeMethod('clear');
}
