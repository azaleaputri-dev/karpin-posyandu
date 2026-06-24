@echo off
title RFID Test ESP32
cd /d "%~dp0"
echo.
echo Coba 115200 baud...
powershell -ExecutionPolicy Bypass -NoProfile -File "%~dp0rfid-test.ps1" -ComPort COM8 -BaudRate 115200
if errorlevel 1 (
    echo.
    echo Coba 9600 baud...
    powershell -ExecutionPolicy Bypass -NoProfile -File "%~dp0rfid-test.ps1" -ComPort COM8 -BaudRate 9600
)
pause
