# Informe: conectar con la app
Fecha: 06/11/2026. Rama: conectar-con-la-app. Sin push.

## Resultado
Se implementó el acceso hablado por carnet, sin exigir nombre en la app, y un menú hablado. Se generó un APK de prueba para datos móviles que conecta por HTTPS temporal con el mismo Laravel y la misma base de datos. No se instaló en un teléfono desde esta sesión.

IMPORTANTE: la base sistema_ibc_oficial contiene ahora dos rutas no-demo en BORRADOR y una EN REVISIÓN; ninguna no-demo PUBLICADA. No se modificaron sus estados. El APK puede probar ingreso y comandos por Internet, pero no permite afirmar que ya existe un recorrido aprobado para navegar en la calle. Un ensayo peatonal autónomo sigue pendiente de validación.

## Cambios de app
1. Apertura sin sesión: dice «Dicte su carnet de identidad», abre el reconocedor Android y pide números uno por uno.
2. Interpreta dígitos hablados; lee el resultado y pide «sí/no». Solo envía el CI tras confirmación. Si no comprende, no inventa un número.
3. Volumen arriba activa la voz también antes del acceso. Se mantiene botón grande DICTAR CARNET DE IDENTIDAD. Se eliminan los campos CI/nombre y el botón de acceso escrito.
4. Carnet aceptado: token cifrado mediante Android Keystore. Aperturas siguientes validan la sesión, saludan y abren el menú sin pedir otra vez carnet mientras sea válido. Conserva vencimiento/revocación existentes.
5. Menú por voz: «quiero ir a/al ...», «conectar bastón», «iniciar navegación», «repetir indicación» y «detener guía».
6. El micrófono funciona por turnos, después del TTS, mediante el reconocedor Android visible. No escucha indefinidamente ni en segundo plano. Volumen arriba/botón permiten reintentar tras silencio o cancelación. La primera autorización del sistema operativo requiere interacción del usuario; TalkBack puede asistir.
7. Consulta todas las páginas del catálogo Laravel. Si hay varios resultados pregunta por voz, uno a uno. Confirma destino y ofrece iniciar la guía. Si no hay ruta, lo anuncia.
8. Se preservan GPS, geometría del servidor, alertas BLE, cola offline, polling y actualizaciones ACO existentes.
9. El reconocedor muestra la pregunta correspondiente, no siempre «¿A dónde quieres ir?». Un fallo de reconocimiento no borra el token cifrado.

## Laravel / API
- MovilidadController::login acepta solo CI; nombre es opcional para compatibilidad con clientes anteriores.
- IdentificacionMovil exige exactamente una persona con ese carnet, activa y con afiliación vigente. Los duplicados no emiten token.
- Si un cliente antiguo envía nombre, sigue verificándolo.
- No se crearon afiliados ni se modificó el padrón.
- Se conserva límite de intentos, expiración y revocación de tokens.
- Se mantuvieron ACO, fórmula distancia + riesgo, notebook y rutas existentes.
- No se alteró el diseño administrativo web.
- Carnet solo es identificación de prototipo: cualquier persona que conozca un carnet activo podría entrar como su titular. No constituye autenticación fuerte ni una verificación biométrica.

## Cómo se conecta con Laragon
App Flutter -> Internet HTTPS -> túnel temporal -> API móvil del mismo Laravel en la PC -> base sistema_ibc_oficial -> recomendador y rutas del sistema original.

No se conecta a una página HTML para iniciar sesión ni calcula otro ACO en el teléfono. Laragon reúne Apache/MySQL/PHP en la PC; el túnel transporta únicamente peticiones de API. Se comprobó que /phpmyadmin retorna 404 en el túnel. No se expuso un puerto MySQL por este túnel.

La URL incorporada en este APK es:
https://grab-search-week-florence.trycloudflare.com

## Wi-Fi, datos, GPS y Bluetooth
- En la calle: datos móviles suficientes para API, sin compartir Wi-Fi con la PC.
- GPS: ubicación del teléfono. No implica que conozca el catálogo o los obstáculos sin Internet.
- Bluetooth BLE: conexión local al ESP32, independiente del Wi-Fi y de datos.
- Sin conexión: última ruta descargada y evidencias pendientes; no hay actualización de obstáculos ni nuevas búsquedas online. La voz depende del reconocedor Android instalado y puede requerir Internet.
- Prueba actual: laptop, MySQL, API y túnel deben permanecer activos y con Internet. Suspensión/apagado/cierre rompe acceso remoto.
- HTTPS temporal no es hosting estable. Si se reinicia el túnel y cambia la URL, el APK actual deja de servir para nuevas peticiones; hay que compilar con la nueva URL o disponer de dominio estable.
- Para operación permanente: servidor HTTPS estable, firma de distribución, estrategia definitiva de autenticación, monitorización y pruebas de campo. No se compró hosting.

## Bastón
- Flutter conecta directamente por BLE; no usa el HTML como puente.
- Recuerda el bastón autorizado e intenta reconexión; no se promete conexión instantánea.
- Primera vinculación por voz: escanea y ofrece los dispositivos que anuncian el UUID del servicio esperado; pide confirmación antes de conectar.
- Si el firmware no anuncia ese UUID, permite selección en lista con TalkBack. Esto requiere verificar firmware físico.
- Protocolo/UUID conservados. Detección ultrasónica es evidencia y alerta inmediata; no crea automáticamente obstáculo permanente.
- SOS sigue siendo otro evento, sin envío automático a familiares.

## Pruebas
- Flutter: 21 pruebas correctas.
- Incluyen primer acceso sin campos técnicos/nombre, carnet dictado y confirmado, rechazo sin enviar CI, sesión recordada, petición «quiero ir al hospital» al backend y confirmación antes de iniciar.
- Laravel: 10 pruebas correctas, 160 verificaciones, solo BD sistema_ibc_oficial_testing.
- Incluyen CI solo con afiliación activa y rechazo del afiliado suspendido; regresiones de sesión, rutas y actualización.
- Dart analyze: sin problemas.
- APK verificado internamente: contiene URL HTTPS nueva y funciones de voz; no es el APK antiguo de emulador.
- HTTPS remoto /up responde y /phpmyadmin retorna 404. Un CI inexistente se rechaza sin requerir nombre.
- Pendiente prueba física: primer permiso de reconocimiento, TalkBack, volumen, datos móviles reales desde el teléfono, BLE/ESP32, precisión GPS y rutas accesibles en campo.
- No se afirma instalación ni prueba física completada.

## Archivos del cambio
app/Http/Controllers/MovilidadController.php
app/Services/IdentificacionMovil.php
mobile/lib/main.dart
mobile/lib/api.dart
mobile/lib/actualizacion_ruta.dart
mobile/android/app/src/main/kotlin/bo/ibc/ibc_movilidad/MainActivity.kt
mobile/README.md
mobile/test/actualizacion_ruta_test.dart
mobile/test/flujo_accesible_test.dart
mobile/test/sesion_test.dart
tests/Unit/MovilidadAfiliadoTest.php

Se conservaron fuera del commit los cambios previos en ReporteObstaculo.php, DemoLaPazDinamicaTest.php, scratch/, .scannerwork/ y sonar-project.properties. Durante el trabajo se detectó una eliminación externa de servidor-movilidad-prototipo.php: no se restauró ni se incluirá en el commit. Para la prueba HTTPS se usó un router local independiente en la carpeta de trabajo, que permite únicamente los endpoints móviles, con vistas demo deshabilitadas.

## Cómo probar
1. Mantener laptop/servidor/túnel encendidos.
2. Instalar IBC-Movilidad-voz-datos-moviles-prueba.apk en Android; no instalar el APK anterior de emulador.
3. Con datos móviles del teléfono, abrir /up de la URL anterior para comprobar conectividad.
4. Abrir la app, autorizar los permisos de voz que Android solicite y dictar dígitos uno por uno.
5. Escuchar y confirmar el carnet con «sí»; después pedir una opción del menú.
6. Probar reapertura: debe recordar la sesión mientras sea válida.
7. Probar bastón encendido con Bluetooth y permisos. No asumir éxito sin oír «Bastón conectado».
8. Búsqueda de ruta real pendiente de publicación autorizada/validada: no cambiar borradores para forzar una demostración.

No se hizo push.
