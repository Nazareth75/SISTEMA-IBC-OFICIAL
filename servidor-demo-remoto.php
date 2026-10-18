<?php
// Entrada mínima del túnel: solo API móvil de la base de pruebas.
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/';
if($path==='/up') {header('Content-Type: application/json');echo '{"demo":true,"api":"movilidad"}';return;}
if(!preg_match('#^/api/movilidad/(login|sesion|logout|eventos|rutas(?:/[0-9]+)?)$#D',$path)) {
    http_response_code(404);header('Content-Type: application/json');echo '{"message":"No disponible"}';return;
}
if(getenv('DB_DATABASE')!=='sistema_ibc_oficial_testing') {http_response_code(503);return;}
require __DIR__.'/public/index.php';
