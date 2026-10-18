<?php
$path=urldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
$public=realpath(__DIR__.'/public');
$file=realpath($public.$path);
if($path!=='/' && $file && str_starts_with($file,$public.DIRECTORY_SEPARATOR) && is_file($file)) {
    if(strtolower(pathinfo($file, PATHINFO_EXTENSION))==='php') {
        require __DIR__.'/public/index.php';
        return;
    }
    $types=['js'=>'application/javascript','css'=>'text/css','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','ico'=>'image/x-icon','woff2'=>'font/woff2'];
    header('Content-Type: '.($types[strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? 'application/octet-stream'));
    readfile($file);
    return;
}
require __DIR__.'/public/index.php';
