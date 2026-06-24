<?php
$comPort = $argv[1] ?? 'COM8';
$baudRate = $argv[2] ?? 115200;
$apiUrl = 'http://localhost/karpin/public/api/iot/rfid/scan';
$pingUrl = 'http://localhost/karpin/public/api/iot/ping';

echo "=======================================\n";
echo "   RFID BRIDGE - $comPort @ ${baudRate}baud\n";
echo "=======================================\n\n";

$token = readline('Device Token: ');
if (!$token) {
    die("Token required\n");
}

echo "\nConfiguring $comPort...\n";
system("mode $comPort: BAUD=$baudRate PARITY=N DATA=8 STOP=1 >NUL 2>NUL");

echo "Opening $comPort...\n";
$port = @fopen($comPort, 'r+b');
if (!$port) {
    echo "[ERR] Gagal buka $comPort\n";
    echo "\nTips:\n";
    echo "  1. Cek COM port di Device Manager\n";
    echo "  2. Tutup aplikasi lain yg pake port ini\n";
    echo "  3. Jalankan sebagai Administrator\n";
    exit(1);
}

stream_set_blocking($port, false);
echo "[OK] Connected! Listening...\n";
echo "     Tap kartu pada reader.\n";
echo "     Ctrl+C untuk berhenti.\n\n";

$lastPing = 0;
$buffer = '';

while (!feof($port)) {
    $now = time();

    // ping tiap 30 detik
    if ($now - $lastPing >= 30) {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "X-Device-Token: $token\r\n",
                'timeout' => 3,
            ]
        ]);
        @file_get_contents($pingUrl, false, $ctx);
        echo date('H:i:s') . " [PING]\n";
        $lastPing = $now;
    }

    // baca data dari serial
    $data = @fread($port, 1024);
    if ($data !== false && strlen($data) > 0) {
        $buffer .= $data;
        $hex = bin2hex($data);
        $text = preg_replace('/[^\x20-\x7E]/', '.', $data);
        echo date('H:i:s') . " [RAW] hex: $hex | text: $text\n";

        // pisah per baris
        if (strpos($buffer, "\n") !== false || strpos($buffer, "\r") !== false || strlen($buffer) >= 20) {
            $lines = preg_split('/\r\n|\n|\r/', $buffer);
            $buffer = array_pop($lines); // sisa yg belum lengkap

            foreach ($lines as $line) {
                $uid = trim($line);
                if (strlen($uid) > 0) {
                    $clean = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $uid));
                    if (strlen($clean) >= 4) {
                        echo date('H:i:s') . " [SCAN] $clean\n";

                        $body = json_encode(['rfid_uid' => $clean]);
                        $ctx = stream_context_create([
                            'http' => [
                                'method' => 'POST',
                                'header' =>
                                    "X-Device-Token: $token\r\n" .
                                    "Content-Type: application/json\r\n" .
                                    "Content-Length: " . strlen($body) . "\r\n",
                                'content' => $body,
                                'timeout' => 5,
                            ]
                        ]);
                        $result = @file_get_contents($apiUrl, false, $ctx);
                        $resData = json_decode($result, true);

                        if ($resData && isset($resData['data']['status'])) {
                            if ($resData['data']['status'] === 'recognized') {
                                $name = $resData['data']['child']['child_name'];
                                echo "       -> DIKENAL: $name\n";
                            } else {
                                echo "       -> TIDAK DIKENAL\n";
                            }
                        } else {
                            echo "       -> API Error\n";
                        }
                    }
                }
            }
        }
    }

    usleep(10000);
}

fclose($port);
echo "Disconnected.\n";
