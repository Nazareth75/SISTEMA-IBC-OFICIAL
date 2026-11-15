@echo off
title SERVIDOR Y TUNEL IBC
color 0A
echo ===================================================
echo     INICIANDO SERVICIO MOVILIDAD IBC (ACO + VOZ)
echo ===================================================
echo.

:: 1. Iniciar servidor PHP en segundo plano
start /B "" "C:\laragon\bin\php\php-8.3.6-nts-Win32-vs16-x64\php.exe" -d display_errors=0 -S 127.0.0.1:8016 "C:\laragon\www\SISTEMA-IBC-OFICIAL\router-app.php"
echo [OK] Servidor Laravel activo en puerto 8016.
echo.

:: 2. Iniciar Cloudflare Tunnel
echo [OK] Conectando tunel seguro Cloudflare (HTTP/2)...
echo.
echo Presiona Ctrl+C si deseas detener el servidor.
echo ===================================================
"C:\Users\HP\Documents\Codex\2026-10-18\y\work\herramientas\cloudflared.exe" tunnel --url http://127.0.0.1:8016 --protocol http2 --no-autoupdate
pause

