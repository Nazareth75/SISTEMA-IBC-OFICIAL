<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
if ($path === '/up') {
    header('Content-Type: application/json');
    echo '{"demo":true,"api":"movilidad","vista_previa":true}';
    return;
}
if ($path === '/descargar' || $path === '/apk') {
    $apkPath = __DIR__ . '/app-ibc-movil.apk';
    if (file_exists($apkPath)) {
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="app-ibc-movil.apk"');
        header('Content-Length: ' . filesize($apkPath));
        readfile($apkPath);
        return;
    }
}
if (!preg_match('#^/api/movilidad/(login|sesion|logout|eventos|rutas(?:/[0-9]+)?)$#D', $path)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo '{"message":"No disponible"}';
    return;
}
putenv('APP_DEBUG=false');
putenv('DB_DATABASE=sistema_ibc_oficial_testing');
putenv('DB_URL=');
putenv('MOVILIDAD_IDENTIFICACION_PROTOTIPO=true');
putenv('MOVILIDAD_PERMITIR_DEMO=true');
putenv('MOVILIDAD_VISTA_PREVIA=true');
chdir(__DIR__);
require __DIR__ . '/public/index.php';

