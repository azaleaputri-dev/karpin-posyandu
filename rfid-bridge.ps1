param(
    [string]$ServerUrl = "http://localhost/karpin",
    [string]$DeviceToken = "",
    [string]$ComPort = "COM8",
    [int]$BaudRate = 115200
)

Write-Host "RFID Bridge - ESP32 → $ServerUrl"
Write-Host "Port: $ComPort, Baud: $BaudRate"
Write-Host ""

if (-not $DeviceToken) {
    Write-Host "ERROR: Device token is required." -ForegroundColor Red
    Write-Host "Usage: powershell -File rfid-bridge.ps1 -ServerUrl ""https://server.com"" -DeviceToken ""your-token"""
    exit 1
}

$apiUrl = "$ServerUrl/api/iot/rfid/scan"
$port = New-Object System.IO.Ports.SerialPort $ComPort, $BaudRate, None, 8, One
$port.ReadTimeout = 500
$port.WriteTimeout = 500

function Connect-Port {
    try {
        if ($port.IsOpen) { $port.Close() }
        $port.Open()
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Connected to $ComPort" -ForegroundColor Green
        return $true
    } catch {
        return $false
    }
}

function Send-Uid {
    param([string]$Uid)
    $body = @{ rfid_uid = $Uid } | ConvertTo-Json
    try {
        $response = Invoke-RestMethod -Uri $apiUrl -Method Post -Headers @{
            "X-Device-Token" = $DeviceToken
            "Content-Type" = "application/json"
        } -Body $body -TimeoutSec 5
        $status = if ($response.data.child) { "RECOGNIZED" } else { "UNRECOGNIZED" }
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $status: $Uid" -ForegroundColor $(if ($response.data.child) { "Green" } else { "Yellow" })
        if ($response.data.child) {
            Write-Host "  -> $($response.data.child.child_name)" -ForegroundColor Green
        }
    } catch {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] API error: $_" -ForegroundColor Red
    }
}

$lastUid = ""
$emptyCount = 0

while ($true) {
    if (-not $port.IsOpen) {
        if (-not (Connect-Port)) {
            Start-Sleep -Seconds 2
            continue
        }
    }

    try {
        $data = $port.ReadLine()
        $bytes = @()
        $data.ToCharArray() | ForEach-Object {
            $b = [int]$_
            if ($b -ne 0xFF -and $b -ne 0x00 -and $b -ne 0x2C) {
                $bytes += $b
            }
        }

        if ($bytes.Count -lt 2) {
            $emptyCount++
            if ($emptyCount -ge 50) {
                Write-Host "." -NoNewline
                $emptyCount = 0
            }
            Start-Sleep -Milliseconds 100
            continue
        }

        $emptyCount = 0
        $uidHex = ""
        $bytes | ForEach-Object { $uidHex += $_.ToString("X2") }

        if ($uidHex -ne $lastUid) {
            $lastUid = $uidHex
            Send-Uid -Uid $uidHex
        }
    } catch {
        if ($port.IsOpen) { $port.Close() }
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Port disconnected, reconnecting..." -ForegroundColor DarkYellow
        Start-Sleep -Seconds 2
    }
}
