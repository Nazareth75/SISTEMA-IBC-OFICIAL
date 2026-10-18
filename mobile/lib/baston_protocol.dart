class BastonMensaje {
  const BastonMensaje(this.tipo, this.valores);
  final String tipo;
  final List<double> valores;
}

/// Una notificación completa o líneas LF. El legado sin LF no garantiza framing.
class BastonProtocol {
  static BastonMensaje parse(String mensaje) {
    final partes = mensaje.trim().split(':');
    if (partes.length != 2) throw const FormatException('Mensaje BLE inválido');
    final tipo = partes[0] == 'DISTANCIAS' ? 'DIST' : partes[0];
    final texto = partes[1].trim();
    if (tipo == 'SOS' &&
        ['Sin GPS', 'Ubicación no disponible'].contains(texto)) {
      return const BastonMensaje('SOS', []);
    }
    final valores = texto.split(',').map(double.parse).toList();
    if (valores.any((n) => !n.isFinite)) {
      throw const FormatException('Dato no finito');
    }
    if (tipo == 'DIST' && valores.length == 3) {
      return BastonMensaje(tipo, valores);
    }
    if (tipo == 'BPM' && valores.length == 1) {
      return BastonMensaje(tipo, valores);
    }
    if ((tipo == 'GPS' || tipo == 'SOS') &&
        valores.length == 2 &&
        valores[0].abs() <= 90 &&
        valores[1].abs() <= 180) {
      return BastonMensaje(tipo, valores);
    }
    throw const FormatException('Formato o coordenadas BLE inválidos');
  }
}
