@echo off
title RFID Bridge - Karpin
cd /d "%~dp0"

set /p server=Server URL (default: http://localhost/karpin): 
if "%server%"=="" set server=http://localhost/karpin

set /p token=Device Token: 
if "%token%"=="" (
    echo Token tidak boleh kosong.
    pause
    exit /b 1
)

echo.
echo Menjalankan bridge ke %server% ...
echo.
powershell -ExecutionPolicy Bypass -NoProfile -Command "& { . '%~dp0rfid-bridge.ps1' -ServerUrl '%server%' -DeviceToken '%token%' }"
pause
