# Auditoría y corrección del flujo IBC Movilidad

Fecha: 29/10/2026. Rama: `fix-flujo-accesible-movilidad`.

## Auditoría inicial

| Punto solicitado | Estado inicial | Resultado |
|---|---|---|
| 1. Ocultar servidor/demo | FALTA | Solo con debug + DEV_TOOLS=true. |
| 2. Backend único | PARCIAL | App normal usa API y AsistenteRutaService; vista previa independiente restringida a demostración explícita. |
| 3. Entornos | PARCIAL | API_BASE_URL en build; release exige HTTPS, sin IP predeterminada. |
| 4. Primera vinculación | YA EXISTE | CI + nombre contra registro existente; token cifrado Keystore. |
| 5. Sesión automática | PARCIAL | Restauración existente + bienvenida hablada y acceso directo. |
| 6. Volumen arriba | FALTA | Pulsación corta con Activity enfocada; botón Decir destino conservado. |
| 7. Reconexión BLE | FALTA | Guarda bastón tras conexión válida e intenta reconectar al abrir sesión, sin cambiar UUID/protocolo. |
| 8. Mapa único | PARCIAL | Flutter dibuja únicamente geometria_recomendada; no candidatos/feromonas. |
| 9. Tramo físico | YA EXISTE | Se conserva modelo y relación; no se crean tramos por detecciones. |
| 10. Distancia + riesgo | YA EXISTE | ServicioAco y función canónica intactos. |
| 11. Alternativas reales | PARCIAL | ConstructorGrafoRuta exige grafo válido. DEMO La Paz es escenario de prueba, no validación física. |
| 12. Obstáculo y recálculo | PARCIAL | Invalida caché; API ahora admite recalcular rutas previamente publicadas y desactualizadas. |
| 13. Actualización navegando | PARCIAL | Polling 20 s compara secuencia, geometría, instrucciones, riesgo, costo y resumen; habla y actualiza. |
| 14. Detección del bastón | YA EXISTE | Alerta local y evidencia pendiente de revisión; no obstáculo permanente automático. |
| 15. SOS separado | YA EXISTE | Evento de emergencia; no riesgo ni feromona. No envía avisos a familiares. |
| 16. Inicio accesible | PARCIAL | Sin controles técnicos, entrada hablada con confirmación, controles etiquetados y estado hablado. |
| 17. Información técnica | YA EXISTE | Web/notebook conservados, retirada de la app del afiliado. |
| 18. Auditar antes | REALIZADO | Había cambios previos en ReporteObstaculo, main.dart, DemoLaPazDinamicaTest y scratch. |
| 19. Demostración final | PARCIAL | Pruebas automatizadas; pendiente teléfono físico, TalkBack y BLE. |
| 20. Entrega | EN VERIFICACIÓN | Ver resultados al final. |

## Si un obstáculo ya no está

Registrar/verificar «YA_NO_ESTA» mediante el flujo web existente. El consenso genera un candidato; un usuario autorizado confirma RESUELTO. Eso invalida la recomendación y excluye ese obstáculo del riesgo vigente. No borra otros riesgos ni convierte un tramo sin evaluación en riesgo cero. La siguiente consulta ejecuta el mismo recomendador y mantiene el equilibrio distancia + riesgo. Puede volver a ganar el camino anterior, pero no se fuerza ese resultado.

La ausencia de ultrasonido por sí sola no resuelve un obstáculo. SOS tampoco modifica riesgo. Las verificaciones y permisos web existentes se conservan.

Flutter anuncia cambios incluso si el ganador mantiene sus tramos pero cambian las condiciones. Si estaba navegando, solo continúa cuando una ubicación GPS reciente y precisa encaja sobre el nuevo recorrido; elige una indicación aún por delante. Si no encaja, pausa y pide ayuda: no calcula un enlace local ni inventa un origen. Si el backend no puede producir recomendación, pausa la guía. No existe aún recálculo universal desde cualquier ubicación.

## Servidor, privacidad y producción

Un Laravel, una BD y un recomendador compartido para rutas registradas. La vista previa de La Merced procedía de datos estáticos separados; se conserva como herramienta técnica/demo, fuera del catálogo normal. No se importaron esos datos como rutas validadas.

Servidor/túnel son herramientas de desarrollo. Compilar con `--dart-define=API_BASE_URL=https://DOMINIO-REAL --dart-define=APP_ENV=PRODUCTION`. Sin URL configurada, la app pide ayuda al IBC; no solicita una IP al afiliado. La variable antigua API_BASE se admite como compatibilidad de build. Al cambiar servidor entre builds no se transmite el token anterior al nuevo servidor.

El token vence a los 30 días o por revocación; entonces hay que vincular nuevamente. CI y nombre del formulario se borran después del acceso. Token, nombre de presentación, ruta, cola y bastón recordado se guardan cifrados en Android. Se mantiene acceso offline temporal a la última sesión validada, sin afirmar que se conocen cambios de obstáculos sin conexión.

Falta para producción: backend HTTPS estable, firma de distribución, método definitivo de vinculación (CI + nombre sigue siendo identificación de prototipo), evaluación geográfica en campo de los recorridos y pruebas físicas de accesibilidad, GPS y bastón. No se contrató hosting ni se modificó Python/notebook ni la matemática ACO. El atajo usa KeyEvent y el reconocedor Android existente; no incorpora AccessibilityService. Pulsaciones largas no activan destino y se conserva el manejo de volumen del sistema.

Fuentes técnicas: [KeyEvent](https://developer.android.com/reference/android/view/KeyEvent), [RecognizerIntent](https://developer.android.com/reference/android/speech/RecognizerIntent).

## Validación automatizada

- Laravel: 33 pruebas correctas, 546 verificaciones (BD sistema_ibc_oficial_testing, transacciones).
- Flutter: 17 pruebas correctas, incluidas primera apertura, restauración con token, confirmación de voz, ruta actualizada y rechazo de recomendación no disponible.
- Análisis Dart: sin problemas.
- git diff --check: correcto.
- No se afirma validación física de TalkBack, tecla de volumen, navegación GPS ni ESP32.

Se conservan sin incluir en este cambio los archivos previos ReporteObstaculo.php, DemoLaPazDinamicaTest.php y scratch/. En main.dart se integra el sondeo periódico previo porque forma parte del flujo solicitado. Las pruebas se ejecutaron sobre ese árbol de trabajo completo.
