@echo off
title Python RFID Bridge - Karpin
cd /d "%~dp0"

echo ============================================
echo   Original Python RFID Bridge
echo   Port: COM8 @ 115200 baud
echo ============================================
echo.
echo Checking Python...
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python tidak ditemukan. Install Python 3 terlebih dahulu.
    pause
    exit /b 1
)

set /p api=API Base URL (default: http://localhost/karpin/api): 
if "%api%"=="" set api=http://localhost/karpin/api

set /p token=Device Token (default untuk dev): 
if "%token%"=="" set token=d1408cd7ee3746d90684274af58afe68438c41f6d7cd79f1197021d6d26ff475

echo.
echo Menjalankan bridge...
python tools/iot_serial_bridge.py --port COM8 --baud 115200 --api-base "%api%" --token "%token%"
pause
