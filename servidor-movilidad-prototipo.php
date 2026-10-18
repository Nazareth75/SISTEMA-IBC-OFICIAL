<?php
// Dedicated prototype API entry point. No web admin, files or database port exposed.
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/';
if($path==='/up'){header('Content-Type: application/json');echo '{"api":"movilidad","vista_previa":true}';return;}
if(!preg_match('#^/api/movilidad/(login|sesion|logout|eventos|rutas(?:/[0-9]+)?)$#D',$path)){
 http_response_code(404);header('Content-Type: application/json');echo '{"message":"No disponible"}';return;
}
if(getenv('IBC_API_PROTOTIPO')!=='1'){http_response_code(503);return;}
putenv('APP_DEBUG=false');
putenv('MOVILIDAD_IDENTIFICACION_PROTOTIPO=true');
putenv('MOVILIDAD_VISTA_PREVIA=true');
require __DIR__.'/public/index.php';
