param([string]$ComPort="COM8", [int]$BaudRate=115200)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TEST ESP32 - $ComPort @ ${BaudRate}baud"
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

try {
    $port = New-Object System.IO.Ports.SerialPort $ComPort, $BaudRate, None, 8, One
    $port.ReadTimeout = 2000
    $port.Open()
    Write-Host "[OK] $ComPort terbuka" -ForegroundColor Green
    Write-Host "     Tap kartu RFID sekarang..."
    Write-Host "     Akan nunggu 30 detik"
    Write-Host ""

    $end = (Get-Date).AddSeconds(30)
    while ((Get-Date) -lt $end) {
        try {
            $data = $port.ReadExisting()
            if ($data.Length -gt 0) {
                $hex = [System.BitConverter]::ToString([System.Text.Encoding]::ASCII.GetBytes($data))
                Write-Host "$(Get-Date -Format 'HH:mm:ss')" -NoNewline
                Write-Host " HEX: $hex" -ForegroundColor Yellow
                Write-Host "         TXT: '$data'" -ForegroundColor Gray
                Write-Host "         LEN: $($data.Length)" -ForegroundColor Gray
            }
        } catch {
            # timeout
        }
        Start-Sleep -Milliseconds 50
    }

    $port.Close()
    Write-Host ""
    Write-Host "Selesai (30 detik)." -ForegroundColor Cyan
} catch {
    Write-Host "[ERR] $_" -ForegroundColor Red
}

Read-Host "Enter untuk tutup"
