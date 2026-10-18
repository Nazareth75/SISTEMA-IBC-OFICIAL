param([switch]$Pruebas,[int]$Puerto=8000)
$ErrorActionPreference='Stop'
Set-Location -LiteralPath $PSScriptRoot
$phpIbc='C:\laragon\bin\php\php-8.3.6-nts-Win32-vs16-x64\php.exe'
if(-not(Test-Path -LiteralPath $phpIbc)){throw 'Configura phpIbc con PHP 8.3 o superior.'}
$direccionesIbc=@([System.Net.Dns]::GetHostAddresses([System.Net.Dns]::GetHostName()) | Where-Object {$_.AddressFamily -eq 'InterNetwork' -and $_.IPAddressToString -notlike '127.*' -and $_.IPAddressToString -notlike '169.254.*'})
$ipIbc=if($direccionesIbc.Count){$direccionesIbc[0].IPAddressToString}else{'127.0.0.1'}
$env:APP_URL="http://${ipIbc}:$Puerto"
$env:APP_DEBUG='false'
# Identificación CI + nombre autorizada solo para este prototipo.
$env:MOVILIDAD_IDENTIFICACION_PROTOTIPO='true'
$env:MOVILIDAD_PERMITIR_DEMO='false'
if($Pruebas){
    $env:APP_ENV='local'
    $env:DB_DATABASE='sistema_ibc_oficial_testing'
    $env:DB_URL=''
    $env:MOVILIDAD_PERMITIR_DEMO='true'
}
Write-Output "Servidor: $env:APP_URL | Pruebas: $Pruebas"
Write-Output 'La app debe usar esta URL. 127.0.0.1 en Android apunta al teléfono.'
# Adaptador opcional de las comprobaciones ACL del entorno Codex Windows.
$compatIbc=$env:IBC_RUNTIME_COMPAT
if($compatIbc -and (Test-Path -LiteralPath $compatIbc)){
    & $phpIbc -d disable_functions=is_readable,is_writable -d "auto_prepend_file=$compatIbc" -S "0.0.0.0:$Puerto" -t public servidor-lan.php
}else{
    & $phpIbc -S "0.0.0.0:$Puerto" -t public servidor-lan.php
}
