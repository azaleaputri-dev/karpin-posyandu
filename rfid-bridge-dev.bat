@echo off
title RFID Bridge - DEV (localhost)
cd /d "%~dp0"

echo ========================================
echo  RFID Bridge - Local Development
echo  Server: http://localhost/karpin
echo  Port: COM8 @ 115200 baud
echo ========================================
echo.

powershell -ExecutionPolicy Bypass -NoProfile -Command "& { . '%~dp0rfid-bridge.ps1' -ServerUrl 'http://localhost/karpin' -DeviceToken 'dev-token-123' }"
pause
