@echo off
title Python RFID Bridge - DEV
cd /d "%~dp0"

echo ============================================
echo   Python RFID Bridge - Local Development
echo   Server: http://localhost/karpin/api
echo   Token: device ID 2 (rfid-card)
echo   Port: COM8 @ 115200
echo ============================================
echo.

python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python tidak ditemukan.
    pause
    exit /b 1
)

python tools/iot_serial_bridge.py --port COM8 --baud 115200 --api-base "http://localhost/karpin/api" --token "d1408cd7ee3746d90684274af58afe68438c41f6d7cd79f1197021d6d26ff475"
pause
