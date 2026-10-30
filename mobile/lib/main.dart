import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';
import 'package:uuid/uuid.dart';
import 'api.dart';
import 'audio.dart';
import 'baston_protocol.dart';
import 'geometria.dart';
import 'sesion_segura.dart';
import 'almacen_movilidad.dart';
import 'configuracion.dart';
import 'actualizacion_ruta.dart';

void main() => runApp(
  const MaterialApp(home: Movilidad(), debugShowCheckedModeBanner: false),
);

class Movilidad extends StatefulWidget {
  const Movilidad({super.key, this.crearApi});
  final MovilidadApi Function(String)? crearApi;
  @override
  State<Movilidad> createState() => _MovilidadState();
}

class _MovilidadState extends State<Movilidad> {
  final servidor = TextEditingController(text: ConfiguracionMovil.baseUrl);
  final carnet = TextEditingController(), nombre = TextEditingController();
  final sesionSegura = SesionSegura();
  final almacen = AlmacenMovilidad();
  Timer? sincronizador;
  bool sinConexion = false, sincronizando = false;
  String nombreAfiliado = '';
  bool restaurando = true;
  int sesionGeneracion = 0;
  final buscarDestino = TextEditingController();
  int? destinoElegido;
  String ultimaIndicacion = '';
  final audio = AudioRuta();
  MovilidadApi? api;
  List<dynamic> rutas = [];
  List<ScanResult> dispositivos = [];
  Map<String, dynamic>? ruta;
  List<List<LatLng>> trazos = [];
  bool escuchando = false;
  late final Future<void> audioListo;
  StreamSubscription<Position>? gps;
  StreamSubscription<List<int>>? datosBaston;
  StreamSubscription<BluetoothConnectionState>? conexion;
  BluetoothDevice? baston;
  Position? posicion;
  String estado = 'Inicia sesión para elegir un destino.',
      estadoBaston = 'Desconectado';
  bool ocupado = false, navegando = false, demo = false;
  int pagina = 1, ultimaPagina = 1, instruccion = 1;
  DateTime ultimaAlerta = DateTime(2000);
  DateTime? ultimoSos;
  bool fueraDelTrazado = false;
  int? anticipada;
  final pendientes = <Map<String, dynamic>>[];
  final distancia = const Distance();
  @override
  void initState() {
    super.initState();
    audioListo = audio.inicializar().catchError((Object e) {
      if (mounted) setState(() => estado = 'Audio no disponible: $e');
    });
    SesionSegura.canal.setMethodCallHandler((call) async {
      if (call.method == 'volumeUp' &&
          mounted &&
          api != null &&
          !ocupado &&
          !escuchando) {
        await tarea(decirDestino);
      }
    });
    Future.microtask(
      () => tarea(() async {
        await audioListo;
        await restaurar();
        if (api == null) {
          await audio.decir(
            'Bienvenido a IBC Movilidad. Vamos a configurar tu acceso. Escribe tu carnet de identidad y nombre registrado.',
          );
        }
      }),
    );
    sincronizador = Timer.periodic(
      const Duration(seconds: 20),
      (_) => sincronizar(),
    );
  }

  Future<void> tarea(Future<void> Function() accion) async {
    if (ocupado) return;
    setState(() => ocupado = true);
    try {
      await accion();
    } on IdentificacionRequerida {
      await limpiarSesion();
      if (mounted) {
        setState(() => estado = 'Identifícate nuevamente para continuar.');
        await audio.decir(estado);
      }
    } catch (e) {
      if (mounted) {
        setState(
          () => estado =
              'No se pudo completar la acción. Reintenta o solicita ayuda al IBC.',
        );
      }
      await audio.decir(
        'No se pudo completar la acción. Reintenta o solicita ayuda al IBC.',
      );
    } finally {
      if (mounted) setState(() => ocupado = false);
    }
  }

  MovilidadApi clientePara(String base) =>
      widget.crearApi?.call(base) ?? MovilidadApi(base);

  Future<void> entrar() async {
    if (!ConfiguracionMovil.urlValida(servidor.text.trim())) {
      throw Exception(
        'El servicio no está configurado. Solicita ayuda al IBC.',
      );
    }
    final cliente = clientePara(servidor.text.trim())..demostracion = demo;
    nombreAfiliado = await cliente.login(
      carnet.text.trim(),
      nombre.text.trim(),
    );
    try {
      await sesionSegura.guardar(cliente.base, cliente.token!, demo);
    } catch (_) {
      try {
        await cliente.request('logout', body: {});
      } catch (_) {}
      rethrow;
    }
    carnet.clear();
    nombre.clear();
    api = cliente;
    await almacen.abrir(cliente.token!);
    pendientes.clear();
    pendientes.addAll(almacen.pendientes);
    almacen.datos['nombre'] = nombreAfiliado;
    almacen.datos['validada'] = DateTime.now().toUtc().toIso8601String();
    await almacen.guardar();
    await catalogo();
    await bienvenida();
  }

  Future<void> restaurar() async {
    try {
      final guardada = await sesionSegura.leer();
      if (guardada == null) return;
      // Una compilación nueva nunca envía el token a otro servidor.
      if (!ConfiguracionMovil.herramientasDesarrollo &&
          guardada['servidor'] != ConfiguracionMovil.baseUrl) {
        await sesionSegura.borrar();
        await almacen.borrar();
        return;
      }
      servidor.text = guardada['servidor'];
      demo =
          ConfiguracionMovil.herramientasDesarrollo && guardada['demo'] == true;
      final cliente = clientePara(servidor.text)
        ..token = guardada['token']
        ..demostracion = demo;
      await almacen.abrir(cliente.token!);
      pendientes.clear();
      pendientes.addAll(almacen.pendientes);
      try {
        final datos = await cliente.request('sesion');
        nombreAfiliado = datos['nombre'];
        api = cliente;
        almacen.datos['nombre'] = nombreAfiliado;
        almacen.datos['validada'] = DateTime.now().toUtc().toIso8601String();
        await almacen.guardar();
        await catalogo();
      } on SinConexion {
        final validada = DateTime.tryParse(almacen.datos['validada'] ?? '');
        if (validada == null ||
            DateTime.now().difference(validada).inDays >= 30) {
          rethrow;
        }
        api = cliente;
        nombreAfiliado = almacen.datos['nombre'] ?? '';
        final ultima = almacen.ruta;
        if (ultima != null) mostrarRuta(ultima);
        await marcarSinConexion();
      }
      if (api != null) await bienvenida();
    } finally {
      if (mounted) setState(() => restaurando = false);
    }
  }

  Future<void> limpiarSesion() async {
    sesionGeneracion++;
    await SesionSegura.canal.invokeMethod('volumeShortcut', false);
    await parar();
    await datosBaston?.cancel();
    await conexion?.cancel();
    await baston?.disconnect();
    datosBaston = null;
    conexion = null;
    baston = null;
    await sesionSegura.borrar();
    await almacen.borrar();
    carnet.clear();
    nombre.clear();
    pendientes.clear();
    if (mounted) {
      setState(() {
        api = null;
        nombreAfiliado = '';
        ruta = null;
        rutas = [];
        trazos = [];
        destinoElegido = null;
        pagina = 1;
        estadoBaston = 'Desconectado';
        dispositivos = [];
      });
    }
  }

  Future<void> cerrarSesion() async {
    try {
      await api?.request('logout', body: {});
    } catch (_) {
      /* Sin red, la credencial local se elimina igualmente. */
    } finally {
      await limpiarSesion();
    }
    if (mounted) {
      setState(
        () => estado = 'Sesión cerrada. Puedes identificar a otra persona.',
      );
    }
  }

  Future<void> catalogo() async {
    final data = await api!.catalogo(pagina);
    if (mounted) {
      setState(() {
        rutas = data['data'];
        ultimaPagina = data['last_page'];
        estado = '¿A dónde quieres ir? Elige una ruta.';
        if (rutas.isEmpty) {
          estado =
              'No hay rutas publicadas disponibles. Solicita ayuda al IBC.';
        }
      });
    }
  }

  Future<void> elegir(int id) async {
    await parar();
    Map<String, dynamic> data;
    try {
      data = await api!.ruta(id);
      sinConexion = false;
      await almacen.guardarRuta(data);
    } on SinConexion {
      final ultima = almacen.ruta;
      if (ultima == null || ultima['ruta_id'] != id) rethrow;
      data = ultima;
      await marcarSinConexion();
    }
    if (!mounted) return;
    mostrarRuta(data);
    ultimaIndicacion = data['resumen_audio'];
    await audio.decir(ultimaIndicacion);
  }

  void mostrarRuta(Map<String, dynamic> data) {
    setState(() {
      ruta = data;
      estado = data['resumen_audio'];
      trazos = (data['geometria_recomendada'] as List)
          .map(
            (s) => (s as List)
                .map(
                  (p) => LatLng(
                    (p['lat'] as num).toDouble(),
                    (p['lng'] as num).toDouble(),
                  ),
                )
                .toList(),
          )
          .toList();
    });
  }

  Future<void> marcarSinConexion() async {
    final avisar = !sinConexion;
    sinConexion = true;
    final texto = ruta != null || almacen.ruta != null
        ? 'Sin conexión. Continuando con la última ruta disponible.'
        : 'Sin conexión. Los eventos quedan pendientes.';
    if (mounted) setState(() => estado = texto);
    if (avisar) await audio.decir(texto);
  }

  Future<void> sincronizar() async {
    if (api == null || sincronizando || ocupado) return;
    sincronizando = true;
    final generacion = sesionGeneracion;
    try {
      await api!.request('sesion');
      if (generacion != sesionGeneracion) return;
      final recuperada = sinConexion;
      sinConexion = false;
      await enviarPendientes();
      if (generacion == sesionGeneracion) {
        if (recuperada) await catalogo();
        if (ruta != null) {
          final nueva = await api!.ruta(ruta!['ruta_id']);
          if (generacion != sesionGeneracion || !mounted) return;
          await almacen.guardarRuta(nueva);
          if (recomendacionCambio(ruta!, nueva)) {
            final seguir = navegando;
            await parar();
            await audio.decir(
              'Las condiciones de la ruta cambiaron. Recalculando.',
            );
            mostrarRuta(nueva);
            ultimaIndicacion = nueva['resumen_audio'];
            if (seguir) {
              await iniciar(reanudando: true);
            } else {
              await audio.decir(ultimaIndicacion);
            }
          }
        }
      }
    } on RecomendacionNoDisponible {
      await parar();
      if (mounted) {
        setState(
          () => estado =
              'Las condiciones cambiaron. No hay una recomendación disponible. Guía pausada. Solicita ayuda al IBC.',
        );
      }
      await audio.decir(estado);
    } on SinConexion {
      await marcarSinConexion();
    } on IdentificacionRequerida {
      if (generacion == sesionGeneracion) {
        await limpiarSesion();
        await audio.decir('Tu acceso caducó. Verifica tus datos nuevamente.');
      }
    } catch (_) {
      /* Un error de servidor no borra la ruta descargada. */
    } finally {
      sincronizando = false;
    }
  }

  Future<void> bienvenida() async {
    await SesionSegura.canal.invokeMethod('volumeShortcut', true);
    await audio.decir(
      'Bienvenido a IBC Movilidad. Presiona volumen arriba para indicar tu destino. También puedes tocar Decir destino.',
    );
    await reconectarBaston();
  }

  Future<void> reconectarBaston() async {
    final id = almacen.datos['baston_id'];
    if (id is! String || id.isEmpty) return;
    try {
      // Sin escaneo ni solicitud automática de nuevos permisos.
      if (await FlutterBluePlus.adapterState.first.timeout(
            const Duration(seconds: 3),
          ) !=
          BluetoothAdapterState.on) {
        return;
      }
      await conectarDispositivo(BluetoothDevice.fromId(id));
    } catch (_) {
      if (mounted) {
        setState(
          () => estadoBaston =
              'Desconectado. Toca Conectar bastón para reintentar.',
        );
      }
    }
  }

  Future<void> decirDestino() async {
    if (escuchando || api == null) return;
    escuchando = true;
    final generacion = sesionGeneracion;
    try {
      await audio.decir('¿A dónde quieres ir?');
      final texto = await SesionSegura.canal.invokeMethod<String>('voice');
      if (texto == null || !mounted || generacion != sesionGeneracion) return;
      setState(() {
        buscarDestino.text = texto;
        destinoElegido = null;
      });
      // Buscar en todo el catálogo publicado, no solo la página visible.
      final candidatos = <dynamic>[];
      var paginaVoz = 1;
      var total = 1;
      do {
        final respuesta = await api!.catalogo(paginaVoz);
        if (generacion != sesionGeneracion || !mounted) return;
        candidatos.addAll(respuesta['data']);
        total = respuesta['last_page'] as int;
        paginaVoz++;
      } while (paginaVoz <= total);
      final encontrados = candidatos
          .where((r) => coincideDestino(texto, r))
          .toList();
      if (encontrados.length != 1) {
        setState(() => rutas = encontrados);
        await audio.decir(
          encontrados.isEmpty
              ? 'No encontré ese destino. Intenta decir otro destino o escríbelo.'
              : 'Encontré varias rutas. Selecciona el destino y origen con TalkBack y toca Buscar ruta segura.',
        );
        return;
      }
      final elegido = encontrados.single;
      await audio.decir(
        'Destino reconocido: ${elegido['dst_rut']}, desde ${elegido['ori_rut']}. ¿Es correcto? Di sí o no.',
      );
      final confirmacion = await SesionSegura.canal.invokeMethod<String>(
        'voice',
      );
      if (!mounted || generacion != sesionGeneracion) return;
      if (!respuestaAfirmativa(confirmacion)) {
        await audio.decir(
          'Búsqueda cancelada. Presiona volumen arriba para indicar otro destino.',
        );
        return;
      }
      setState(() => destinoElegido = elegido['cod_rutas']);
      await elegir(destinoElegido!);
    } finally {
      escuchando = false;
    }
  }

  Future<void> iniciar({bool reanudando = false}) async {
    if (ruta == null) return;
    // La geometría ya descargada permite iniciar sin volver a depender de red.
    if (!await Geolocator.isLocationServiceEnabled()) {
      throw Exception('Activa la ubicación del teléfono.');
    }
    var permiso = await Geolocator.checkPermission();
    if (permiso == LocationPermission.denied) {
      permiso = await Geolocator.requestPermission();
    }
    if (permiso == LocationPermission.denied ||
        permiso == LocationPermission.deniedForever) {
      throw Exception('Se necesita permiso de ubicación.');
    }
    instruccion = 1;
    if (reanudando) {
      final p = posicion;
      final siguiente = p == null
          ? null
          : siguienteInstruccionActualizada(
              ruta!,
              LatLng(p.latitude, p.longitude),
              p.accuracy,
              p.timestamp,
            );
      if (siguiente == null) {
        setState(
          () => estado =
              'Ruta actualizada. Guía pausada: no se ha podido enlazar tu ubicación con la nueva ruta. Solicita ayuda o reinicia desde el origen.',
        );
        await audio.decir(estado);
        return;
      }
      instruccion = siguiente;
    }
    anticipada = null;
    setState(() {
      navegando = true;
      estado = 'Guía aproximada activa. Esperando ubicación precisa.';
    });
    await audio.decir(
      reanudando
          ? 'Ruta actualizada. Continuamos la guía desde tu ubicación.'
          : 'Ruta segura iniciada. Guía aproximada. Mantén la aplicación abierta.',
    );
    gps =
        Geolocator.getPositionStream(
          locationSettings: const LocationSettings(
            accuracy: LocationAccuracy.high,
            distanceFilter: 3,
          ),
        ).listen(
          actualizarPosicion,
          onError: (Object e) {
            if (mounted) setState(() => estado = 'Ubicación no disponible: $e');
          },
        );
  }

  void actualizarPosicion(Position p) {
    if (!mounted || !navegando || ruta == null) return;
    posicion = p;
    if (p.accuracy > 25 ||
        DateTime.now().difference(p.timestamp).inSeconds.abs() > 20) {
      setState(() => estado = 'Ubicación imprecisa. Indicaciones pausadas.');
      return;
    }
    final maniobras = ruta!['instrucciones'] as List;
    if (distanciaAlTrazado(LatLng(p.latitude, p.longitude), trazos) > 30) {
      if (!fueraDelTrazado) {
        audio.decir(
          'Tu ubicación parece fuera del trazado. Revisa la ruta antes de continuar.',
        );
      }
      fueraDelTrazado = true;
      setState(() => estado = 'Fuera del trazado. Indicaciones pausadas.');
      return;
    }
    fueraDelTrazado = false;
    if (instruccion >= maniobras.length) return;
    final siguiente = maniobras[instruccion], punto = siguiente['punto'];
    final metros = distancia.as(
      LengthUnit.Meter,
      LatLng(p.latitude, p.longitude),
      LatLng(
        (punto['lat'] as num).toDouble(),
        (punto['lng'] as num).toDouble(),
      ),
    );
    setState(
      () => estado =
          'Próxima indicación a aproximadamente ${metros.round()} metros.',
    );
    if (metros < 25) {
      ultimaIndicacion = instruccion == maniobras.length - 1
          ? 'Has llegado al punto de destino según el GPS. Comprueba el acceso.'
          : siguiente['texto'];
      audio.decir(
        '$ultimaIndicacion A aproximadamente ${metros.round()} metros.',
      );
      instruccion++;
      if (instruccion >= maniobras.length) {
        gps?.cancel();
        gps = null;
        setState(() => navegando = false);
      }
    } else if (metros < 80 && anticipada != instruccion) {
      anticipada = instruccion;
      ultimaIndicacion =
          'En aproximadamente ${metros.round()} metros, ${siguiente['texto']}';
      audio.decir(ultimaIndicacion);
    }
  }

  Future<void> parar() async {
    await gps?.cancel();
    gps = null;
    await audio.detener();
    if (mounted) setState(() => navegando = false);
  }

  Future<void> buscarBaston() async {
    await FlutterBluePlus.startScan(timeout: const Duration(seconds: 8));
    await FlutterBluePlus.isScanning.where((s) => !s).first;
    if (mounted) {
      setState(() {
        dispositivos = FlutterBluePlus.lastScanResults;
        estado = dispositivos.isEmpty
            ? 'No se encontraron dispositivos BLE.'
            : 'Selecciona tu bastón.';
      });
    }
  }

  Future<void> conectar(ScanResult resultado) async {
    await datosBaston?.cancel();
    await conexion?.cancel();
    await baston?.disconnect();
    await conectarDispositivo(resultado.device);
  }

  Future<void> conectarDispositivo(BluetoothDevice device) async {
    await device.connect(
      license: License.nonprofit,
      timeout: const Duration(seconds: 15),
    );
    try {
      final servicios = await device.discoverServices();
      final servicio = servicios.firstWhere(
        (s) => s.uuid == Guid('4fafc201-1fb5-459e-8fcc-c5c9c331914b'),
      );
      final caracteristica = servicio.characteristics.firstWhere(
        (c) => c.uuid == Guid('beb5483e-36e1-4688-b7f5-ea07361b26a8'),
      );
      datosBaston = caracteristica.onValueReceived.listen(recibirBaston);
      await caracteristica.setNotifyValue(true);
      baston = device;
      conexion = device.connectionState.listen((s) {
        if (mounted) {
          setState(
            () => estadoBaston = s == BluetoothConnectionState.connected
                ? 'Conectado'
                : 'Desconectado',
          );
        }
      });
      if (mounted) {
        setState(() {
          estadoBaston = 'Conectado';
          dispositivos = [];
        });
      }
      almacen.datos['baston_id'] = device.remoteId.str;
      await almacen.guardar();
      await audio.decir('Bastón conectado.');
    } catch (_) {
      await device.disconnect();
      rethrow;
    }
  }

  void recibirBaston(List<int> bytes) {
    try {
      for (final linea
          in utf8.decode(bytes).split('\n').where((s) => s.trim().isNotEmpty)) {
        final mensaje = BastonProtocol.parse(linea);
        if (mensaje.tipo == 'SOS') {
          if (ultimoSos != null &&
              DateTime.now().difference(ultimoSos!).inSeconds < 10) {
            continue;
          }
          ultimoSos = DateTime.now();
          HapticFeedback.heavyImpact();
          audio.decir(
            'Alerta SOS recibida. Registrando en el sistema.',
            prioridad: 2,
          );
          setState(
            () => estado =
                'SOS recibido. Ubicación: ${mensaje.valores.isEmpty ? 'no disponible' : mensaje.valores.join(', ')}.',
          );
          registrarEvento('SOS', coordenadas: mensaje.valores);
        } else if (mensaje.tipo == 'DIST') {
          final peligro = mensaje.valores.indexWhere((n) => n > 0 && n < 30);
          if (peligro >= 0 &&
              DateTime.now().difference(ultimaAlerta).inSeconds >= 2) {
            ultimaAlerta = DateTime.now();
            HapticFeedback.heavyImpact();
            audio.decir(
              'Obstáculo ${['al frente', 'a la izquierda', 'a la derecha'][peligro]}.',
              prioridad: 1,
            );
            registrarEvento('DETECCION', distancias: mensaje.valores);
          }
        }
      }
    } catch (e) {
      if (mounted) setState(() => estado = 'Dato BLE no válido: $e');
    }
  }

  Future<void> registrarEvento(
    String tipo, {
    List<double> coordenadas = const [],
    List<double>? distancias,
    String origen = 'ESP32',
  }) async {
    final generacion = sesionGeneracion;
    if (api == null) return;
    if (tipo == 'SOS' && coordenadas.isEmpty) {
      try {
        var permiso = await Geolocator.checkPermission();
        if (permiso == LocationPermission.denied) {
          permiso = await Geolocator.requestPermission();
        }
        if (permiso == LocationPermission.always ||
            permiso == LocationPermission.whileInUse) {
          posicion = await Geolocator.getCurrentPosition(
            locationSettings: const LocationSettings(
              accuracy: LocationAccuracy.high,
              timeLimit: Duration(seconds: 5),
            ),
          );
        }
      } catch (_) {
        /* Registrar SOS aunque no haya GPS disponible. */
      }
    }
    final p = posicion;
    final fresco =
        p != null &&
        DateTime.now().difference(p.timestamp).inSeconds.abs() <= 20;
    final evento = <String, dynamic>{
      'evento_uuid': const Uuid().v4(),
      'tipo': tipo,
      'origen': origen,
      'fecha_evento': DateTime.now().toUtc().toIso8601String(),
      if (ruta != null && ruta!['vista_previa'] != true)
        'ruta_id': ruta!['ruta_id'],
      'distancias_cm': ?distancias,
      if (coordenadas.length == 2) ...{
        'latitud': coordenadas[0],
        'longitud': coordenadas[1],
        'fuente_ubicacion': 'BASTON',
      } else if (fresco) ...{
        'latitud': p.latitude,
        'longitud': p.longitude,
        'precision_m': p.accuracy,
        'fuente_ubicacion': 'TELEFONO',
      },
    };
    if (generacion != sesionGeneracion || api == null) return;
    pendientes.add(evento);
    await almacen.agregar(evento);
    await enviarPendientes();
  }

  bool enviando = false;
  Future<void> enviarPendientes() async {
    if (enviando || api == null) return;
    final cliente = api!;
    final generacion = sesionGeneracion;
    enviando = true;
    try {
      while (pendientes.isNotEmpty && generacion == sesionGeneracion) {
        final evento = pendientes.first;
        final confirmacion = await cliente.request(
          'eventos${demo ? '?demostracion=1' : ''}',
          body: evento,
        );
        if (generacion != sesionGeneracion) return;
        if (confirmacion['registrado'] != true) {
          throw Exception('Evento no confirmado');
        }
        pendientes.removeAt(0);
        await almacen.confirmar(evento['evento_uuid']);
        if (evento['tipo'] == 'SOS') {
          if (mounted) {
            setState(
              () => estado =
                  'SOS registrado en el sistema. No se ha enviado aviso a familiares.',
            );
          }
          await audio.decir('SOS registrado en el sistema.', prioridad: 2);
        }
      }
    } on IdentificacionRequerida {
      if (generacion != sesionGeneracion) return;
      await limpiarSesion();
      if (mounted) setState(() => estado = 'Identifícate nuevamente.');
    } catch (e) {
      if (generacion != sesionGeneracion) return;
      if (mounted) {
        setState(
          () => estado =
              'Evento pendiente de envío: $e. Mantén la app abierta y reintenta.',
        );
      }
      if (e is SinConexion) {
        await marcarSinConexion();
        if (pendientes.any((e) => e['tipo'] == 'SOS')) {
          await audio.decir(
            'Alerta SOS activada. Sin conexión con el servidor. Envío pendiente.',
            prioridad: 2,
          );
        }
      }
    } finally {
      enviando = false;
      if (mounted) setState(() {});
    }
  }

  @override
  void dispose() {
    SesionSegura.canal.setMethodCallHandler(null);
    sincronizador?.cancel();
    gps?.cancel();
    datosBaston?.cancel();
    conexion?.cancel();
    baston?.disconnect();
    audio.detener();
    servidor.dispose();
    carnet.dispose();
    nombre.dispose();
    buscarDestino.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('IBC | RUTAS SEGURAS')),
    body: SafeArea(
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          if (restaurando)
            const LinearProgressIndicator(semanticsLabel: 'Recuperando sesión'),
          if (!restaurando && api == null) ...[
            const Text(
              'IBC | RUTAS SEGURAS',
              style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold),
            ),
            const Text(
              'Vamos a configurar tu acceso. Escribe tu carnet de identidad y nombre registrado. Solo será necesario volver a verificarte si tu acceso caduca o se revoca.',
            ),
            if (ConfiguracionMovil.herramientasDesarrollo)
              TextField(
                controller: servidor,
                decoration: const InputDecoration(
                  labelText: 'Servidor Laravel',
                ),
              ),
            TextField(
              controller: carnet,
              decoration: const InputDecoration(
                labelText: 'Carnet de identidad',
              ),
            ),
            TextField(
              controller: nombre,
              decoration: const InputDecoration(labelText: 'Nombre'),
            ),
            if (ConfiguracionMovil.herramientasDesarrollo)
              SwitchListTile(
                title: const Text('Datos de demostración'),
                value: demo,
                onChanged: (v) => setState(() => demo = v),
              ),
            FilledButton(
              onPressed: ocupado ? null : () => tarea(entrar),
              child: const Text('CONTINUAR'),
            ),
          ] else if (api != null) ...[
            Text('Hola, $nombreAfiliado', style: const TextStyle(fontSize: 24)),
            TextButton(
              onPressed: ocupado ? null : () => tarea(cerrarSesion),
              child: const Text('CERRAR SESIÓN / CAMBIAR PERSONA'),
            ),
            const Text(
              '¿A dónde quieres ir?',
              style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold),
            ),
            FilledButton(
              onPressed: ocupado ? null : () => tarea(decirDestino),
              child: const Padding(
                padding: EdgeInsets.all(16),
                child: Text('DECIR DESTINO'),
              ),
            ),
            const Text('ESCRIBIR DESTINO'),
            TextField(
              controller: buscarDestino,
              decoration: const InputDecoration(labelText: 'Buscar destino'),
              onChanged: (_) => setState(() => destinoElegido = null),
            ),
            for (final item in rutas.where(
              (r) => '${r['dst_rut']} ${r['nom_rut']}'.toLowerCase().contains(
                buscarDestino.text.toLowerCase(),
              ),
            ))
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 5),
                child: FilledButton(
                  onPressed: ocupado
                      ? null
                      : () =>
                            setState(() => destinoElegido = item['cod_rutas']),
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Text(
                      '${destinoElegido == item['cod_rutas'] ? 'SELECCIONADO: ' : ''}${item['dst_rut']} · DESDE ${item['ori_rut']}',
                    ),
                  ),
                ),
              ),
            FilledButton(
              onPressed: ocupado || destinoElegido == null
                  ? null
                  : () => tarea(() => elegir(destinoElegido!)),
              child: const Padding(
                padding: EdgeInsets.all(16),
                child: Text('BUSCAR RUTA SEGURA'),
              ),
            ),
            Row(
              children: [
                TextButton(
                  onPressed: pagina > 1 && !ocupado
                      ? () => tarea(() async {
                          pagina--;
                          await catalogo();
                        })
                      : null,
                  child: const Text('Anterior'),
                ),
                Text('$pagina / $ultimaPagina'),
                TextButton(
                  onPressed: pagina < ultimaPagina && !ocupado
                      ? () => tarea(() async {
                          pagina++;
                          await catalogo();
                        })
                      : null,
                  child: const Text('Siguiente'),
                ),
              ],
            ),
            if (ruta != null) ...[
              if (ruta!['vista_previa'] == true)
                Text(ruta!['advertencia'] as String),
              if (ruta!['demostracion'] == true)
                const Text(
                  'DATOS SIMULADOS · NO VALIDADOS EN CAMPO',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              FilledButton(
                onPressed: ocupado || navegando ? null : () => tarea(iniciar),
                child: const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('INICIAR NAVEGACIÓN'),
                ),
              ),
              OutlinedButton(
                onPressed: () => audio.decir(ultimaIndicacion),
                child: const Text('REPETIR INDICACIÓN'),
              ),
              OutlinedButton(
                onPressed: parar,
                child: const Text('Detener guía y audio'),
              ),
              OutlinedButton(
                onPressed: ocupado
                    ? null
                    : () => tarea(() => elegir(ruta!['ruta_id'])),
                child: const Text('Actualizar recomendación'),
              ),
              if (trazos.isNotEmpty && trazos.first.isNotEmpty)
                SizedBox(
                  height: 280,
                  child: FlutterMap(
                    key: ValueKey(
                      '${ruta!['ruta_id']}-${ruta!['fecha_calculo']}',
                    ),
                    options: MapOptions(
                      initialCenter: trazos.first.first,
                      initialZoom: 16,
                      initialCameraFit: CameraFit.bounds(
                        bounds: LatLngBounds.fromPoints(
                          trazos.expand((s) => s).toList(),
                        ),
                        padding: const EdgeInsets.all(32),
                      ),
                    ),
                    children: [
                      TileLayer(
                        urlTemplate:
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'bo.ibc.ibc_movilidad',
                      ),
                      PolylineLayer(
                        polylines: [
                          for (final segmento in trazos)
                            Polyline(
                              points: segmento,
                              color: Colors.green.shade800,
                              strokeWidth: 6,
                            ),
                        ],
                      ),
                      MarkerLayer(
                        markers: [
                          Marker(
                            point: trazos.first.first,
                            child: Semantics(
                              label: 'Origen registrado',
                              child: Icon(
                                Icons.trip_origin,
                                color: Colors.green,
                                size: 32,
                              ),
                            ),
                          ),
                          Marker(
                            point: trazos.last.last,
                            child: Semantics(
                              label: 'Destino',
                              child: Icon(
                                Icons.flag,
                                color: Colors.red,
                                size: 36,
                              ),
                            ),
                          ),
                          if (posicion != null)
                            Marker(
                              point: LatLng(
                                posicion!.latitude,
                                posicion!.longitude,
                              ),
                              child: Semantics(
                                label: 'Mi ubicación',
                                child: Icon(
                                  Icons.my_location,
                                  color: Colors.blue,
                                  size: 30,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SimpleAttributionWidget(
                        source: Text('OpenStreetMap contributors'),
                      ),
                    ],
                  ),
                ),
            ],
            Text('Bastón: $estadoBaston', style: const TextStyle(fontSize: 20)),
            FilledButton(
              onPressed: ocupado
                  ? null
                  : () =>
                        tarea(() => registrarEvento('SOS', origen: 'FLUTTER')),
              child: const Padding(
                padding: EdgeInsets.all(14),
                child: Text('REGISTRAR SOS'),
              ),
            ),
            const Text(
              'SOS registra un evento en el IBC. No envía avisos automáticos a familiares ni servicios de emergencia.',
            ),
            OutlinedButton(
              onPressed: ocupado ? null : () => tarea(buscarBaston),
              child: const Text('CONECTAR BASTÓN'),
            ),
            if (pendientes.isNotEmpty)
              OutlinedButton(
                onPressed: () => tarea(enviarPendientes),
                child: Text(
                  'Reintentar ${pendientes.length} eventos pendientes',
                ),
              ),
            for (final d in dispositivos)
              ListTile(
                title: Text(
                  d.device.platformName.isEmpty
                      ? 'Bastón sin nombre'
                      : d.device.platformName,
                ),
                onTap: ocupado ? null : () => tarea(() => conectar(d)),
              ),
          ],
          if (ocupado) const LinearProgressIndicator(),
          Semantics(
            liveRegion: true,
            child: Text(estado, style: const TextStyle(fontSize: 18)),
          ),
        ],
      ),
    ),
  );
}
