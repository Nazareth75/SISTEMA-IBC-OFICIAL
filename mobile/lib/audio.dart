import 'package:flutter_tts/flutter_tts.dart';

class AudioRuta {
  final FlutterTts _tts = FlutterTts();
  int _prioridad = -1, _generacion = 0;
  Future<void> inicializar() async {
    await _tts.setLanguage('es-ES');
    await _tts.setSpeechRate(0.5);
    await _tts.awaitSpeakCompletion(true);
  }

  /// SOS (2) interrumpe peligro (1), que interrumpe navegación (0).
  Future<void> decir(String texto, {int prioridad = 0}) async {
    if (_prioridad > prioridad) return;
    final turno = ++_generacion;
    await _tts.stop();
    _prioridad = prioridad;
    try {
      await _tts.speak(texto);
    } finally {
      if (_generacion == turno) _prioridad = -1;
    }
  }

  Future<void> detener() async {
    ++_generacion;
    _prioridad = -1;
    await _tts.stop();
  }
}
