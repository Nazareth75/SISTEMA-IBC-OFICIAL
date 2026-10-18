class ConfiguracionMovil {
  static const entorno = String.fromEnvironment(
    'APP_ENV',
    defaultValue: 'DEVELOPMENT_LOCAL',
  );
  static const baseUrl = String.fromEnvironment(
    'API_BASE',
    defaultValue: 'http://10.0.2.2:8000',
  );
  static bool urlValida(String value) {
    final uri = Uri.tryParse(value);
    if (uri == null ||
        uri.host.isEmpty ||
        uri.userInfo.isNotEmpty ||
        uri.hasQuery ||
        uri.hasFragment) {
      return false;
    }
    if (uri.path.isNotEmpty && uri.path != '/') return false;
    return uri.scheme == 'https' ||
        (entorno == 'DEVELOPMENT_LOCAL' && uri.scheme == 'http');
  }
}
