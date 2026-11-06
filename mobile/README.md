# IBC Movilidad: operación y compilación

La app del afiliado usa el mismo Laravel, BD y recomendación de rutas registradas que la web. Flutter no ejecuta ACO ni decide caminos con OSRM. Dibuja la geometría recomendada recibida del servidor. Los candidatos, feromonas y métricas permanecen en la vista técnica web.

Primera apertura: bienvenida por voz, carnet dictado dígito a dígito y confirmación hablada, token revocable guardado con Android Keystore. Siguientes aperturas: valida el token y entra directamente. Al expirar o revocarse, vuelve a verificar. No persiste CI ni nombre del formulario; el nombre mostrado se conserva cifrado con los datos de sesión. Cambiar servidor entre builds requiere nueva vinculación.

## Build

```
flutter analyze
flutter test
flutter build apk --release --dart-define=APP_ENV=PRODUCTION --dart-define=API_BASE_URL=https://DOMINIO-REAL
```

No hay URL de producción predeterminada. Sustituir DOMINIO-REAL por el backend desplegado. Release solo acepta HTTPS. La firma release actual sigue siendo de prueba: configurar firma de distribución antes de publicar.

Desarrollo emulador: debug + APP_ENV=DEVELOPMENT_LOCAL + API_BASE_URL=http://10.0.2.2:8000. Solo debug con DEV_TOOLS=true habilita controles de servidor y demostración. API_BASE se admite como alias legado de compilación. Servidor local y túnel son herramientas de desarrollo; el afiliado no configura direcciones.

## Uso sin visión

Con la app enfocada, una pulsación corta de volumen arriba o DECIR DESTINO pregunta el destino. Consulta el catálogo completo, confirma por voz «sí/no» y obtiene la ruta. Si existen varios resultados, elegir con TalkBack el destino y origen. Se conserva búsqueda escrita. El reconocimiento depende del servicio Android disponible y puede requerir Internet. No se usa AccessibilityService.

El mapa es apoyo visual; indicaciones y estados se comunican por voz y controles etiquetados. Pendiente prueba física con TalkBack, escalado de texto y usuarios del IBC; las pruebas automatizadas no certifican accesibilidad completa.

## Actualizaciones y bastón

Poll cada 20 segundos con la sesión activa. Registrar/validar o resolver un obstáculo invalida la recomendación del servidor. Flutter compara las condiciones, actualiza mapa y habla. Durante navegación enlaza únicamente una posición precisa/reciente sobre el nuevo recorrido; de otro modo pausa. Una recomendación rechazada por el backend pausa la guía. No se genera una ruta independiente desde la posición actual.

Primera vinculación BLE manual con permisos Android. Tras descubrir correctamente el servicio/característica existentes, recuerda el dispositivo cifrado e intenta reconectar en siguientes aperturas si Bluetooth está disponible. Protocolo y UUID intactos. Alertas locales no dependen de Internet; las detecciones se acumulan como evidencia. Dejar de detectar no confirma ausencia de obstáculo. SOS es un evento separado y no envía avisos automáticos a familiares.

Ruta descargada y eventos pendientes se conservan cifrados, aislados por token. Sin conexión se usa la última ruta y no se pueden conocer nuevas condiciones. Mantener app abierta; no hay guía garantizada en segundo plano. Cerrar sesión desconecta y borra datos locales.

Detalle de auditoría: docs/AUDITORIA_FLUJO_MOVIL.md, en la raíz del repositorio.


## Rama conectar-con-la-app: acceso hablado

Al abrir sin sesión se dice «Dicte su carnet de identidad» y se abre el reconocedor Android. Se repite el carnet y se requiere «sí» antes de enviarlo. Se exige un único afiliado activo; el nombre ya no es obligatorio para login. Se mantiene compatibilidad con clientes anteriores que envían CI + nombre. Carnet solo es identificación de prototipo y no prueba identidad.

Después de entrar/restaurar se explica el menú y se abre una escucha de comando. Volumen arriba funciona también antes del acceso. Se admiten «quiero ir a/al ...», «conectar bastón», «iniciar navegación», «repetir indicación» y «detener guía». El micrófono se activa por turnos, con el reconocedor Android visible; cancelar/silencio vuelve al botón o volumen arriba. No escucha indefinidamente ni en segundo plano.

El servidor de prueba puede ser accesible por HTTPS temporal para usar datos móviles. No obliga a Wi-Fi en la calle; la misma Wi-Fi solo corresponde a una compilación LAN. La laptop/servidor/túnel deben permanecer activos. Para operación permanente hace falta HTTPS estable. GPS = ubicación del teléfono; BLE = bastón; Internet = rutas y cambios vigentes.

No publicar rutas en borrador o en revisión para habilitar una prueba callejera. Una ruta registrada no equivale a validación de accesibilidad peatonal en campo.
