# IBC · Movilidad

App del afiliado: identificación de prototipo CI + nombre contra un registro existente con afiliación activa; no crea ni modifica el padrón. Token revocable de 30 días. No persiste CI ni nombre de identificación. Sesión, ruta y cola cifradas con AES-GCM y clave Android Keystore; backup deshabilitado.

## Servidor y compilación

Configuración central: `lib/configuracion.dart`, variables `APP_ENV=DEVELOPMENT_LOCAL|REMOTE_DEMO|PRODUCTION` y `API_BASE`. Solo DEVELOPMENT_LOCAL admite HTTP. El campo Servidor permite cambiar la URL antes de identificarse. Cambiar de servidor requiere cerrar sesión.

```powershell
flutter pub get
flutter analyze
flutter test
flutter build apk --debug --dart-define=APP_ENV=REMOTE_DEMO --dart-define=API_BASE=https://URL-REAL-DEL-TUNEL
```

Para LAN usar DEVELOPMENT_LOCAL y la IP actual del PC. No usar 127.0.0.1 en Android físico. Para release repetir con --release; la firma configurada aún es de prueba. No hay dominio de producción inventado.

## Operación

Activar DEMO solo cuando el servidor de pruebas lo permita. Fixture controlado: CI `DEMO-MOVIL-001`, nombre `PERSONA DEMOSTRACION`. Nunca sembrar en BD oficial. Elegir o decir destino, seleccionar resultado, buscar ruta e iniciar navegación. La entrada de voz utiliza el reconocedor instalado en Android y puede necesitar Internet. TTS y TalkBack utilizan servicios/voces del dispositivo.

Laravel ejecuta ACO. Flutter conserva exactamente geometría e instrucciones recibidas. La última ruta descargada permite guía GPS temporal sin Internet. No descarga mosaicos para uso offline. Mantener la app abierta: no se implementó servicio de navegación en segundo plano. Se parte del origen registrado, sin enlace automático desde cualquier posición.

La cola persistente conserva UUID y reintenta cada 20 segundos con la app abierta o manualmente. El servidor confirma antes de retirar evidencia. SOS sin red queda pendiente, nunca anuncia confirmación inexistente. Cerrar sesión borra datos locales y desconecta BLE: enviar pendientes antes de cambiar de persona. Una revocación se detecta al recuperar red. La reapertura offline permite uso temporal de la última sesión validada y su ruta, sin ejecutar ACO local.

## Bastón

BLE: servicio `4fafc201-1fb5-459e-8fcc-c5c9c331914b`; característica `beb5483e-36e1-4688-b7f5-ea07361b26a8`. Canónico recomendado, con LF: `DIST:frente,izquierda,derecha` (cm), `SOS:latitud,longitud` o `SOS:Sin GPS`. Se admite DISTANCIAS, GPS y BPM del material auditado. Alertas BLE/audio/vibración funcionan sin depender de la API; la detección queda como evidencia para revisión técnica. No hay envío real a familiares. Firmware completo y prueba física ESP32 pendientes.

Archivos: main.dart (BLE/GPS/UI/eventos), baston_protocol.dart (parser), audio.dart (TTS/prioridad), almacen_movilidad.dart (cola/ruta), sesion_segura.dart y MainActivity.kt (cifrado/voz).
