import 'dart:convert';
import 'dart:async';
import 'package:http/http.dart' as http;

class IdentificacionRequerida implements Exception {}

class SinConexion implements Exception {}

class RecomendacionNoDisponible implements Exception {}

class MovilidadApi {
  MovilidadApi(String base, {http.Client? client})
    : base = base.replaceAll(RegExp(r'/$'), ''),
      _client = client ?? http.Client();
  final http.Client _client;
  final String base;
  String? token;
  bool demostracion = false;
  Future<dynamic> request(String path, {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('$base/api/movilidad/$path');
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
    late http.Response response;
    try {
      response =
          await (body == null
                  ? _client.get(uri, headers: headers)
                  : _client.post(uri, headers: headers, body: jsonEncode(body)))
              .timeout(const Duration(seconds: 25));
    } on http.ClientException {
      throw SinConexion();
    } on TimeoutException {
      throw SinConexion();
    }
    if (response.statusCode == 204) return null;
    if (response.statusCode == 401) throw IdentificacionRequerida();
    if (response.statusCode == 422 && path.startsWith('rutas/')) {
      throw RecomendacionNoDisponible();
    }
    final data = jsonDecode(response.body);
    if (response.statusCode >= 400) {
      throw Exception(
        data['mensaje_error'] ??
            data['message'] ??
            'Error ${response.statusCode}',
      );
    }
    return data;
  }

  Future<String> login(String ci, [String? nombre]) async {
    final data = await request('login', body: {'ci': ci, 'nombre': ?nombre});
    token = data['token'];
    return data['nombre'];
  }

  Future<Map<String, dynamic>> catalogo(int page) async =>
      Map<String, dynamic>.from(
        await request(
          'rutas?page=$page${demostracion ? '&demostracion=1' : ''}',
        ),
      );
  Future<Map<String, dynamic>> ruta(int id) async => Map<String, dynamic>.from(
    await request('rutas/$id${demostracion ? '?demostracion=1' : ''}'),
  );
}
