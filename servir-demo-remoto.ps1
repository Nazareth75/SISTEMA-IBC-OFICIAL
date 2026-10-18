param([int]$Puerto=8003)
$ErrorActionPreference='Stop'
Set-Location -LiteralPath $PSScriptRoot
$env:APP_ENV='local';$env:APP_DEBUG='false';$env:DB_DATABASE='sistema_ibc_oficial_testing';$env:DB_URL=''
$env:MOVILIDAD_IDENTIFICACION_PROTOTIPO='true';$env:MOVILIDAD_PERMITIR_DEMO='true'
$phpIbc='C:\laragon\bin\php\php-8.3.6-nts-Win32-vs16-x64\php.exe'
if($env:IBC_RUNTIME_COMPAT -and (Test-Path -LiteralPath $env:IBC_RUNTIME_COMPAT)) {
    & $phpIbc -d disable_functions=is_readable,is_writable -d "auto_prepend_file=$env:IBC_RUNTIME_COMPAT" -S "127.0.0.1:$Puerto" -t public servidor-demo-remoto.php
}else{
    & $phpIbc -S "127.0.0.1:$Puerto" -t public servidor-demo-remoto.php
}
