<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\Device;
use App\Models\RfidScan;
use Illuminate\Console\Command;

class RfidListenCommand extends Command
{
    protected $signature = 'rfid:listen';
    protected $description = 'Listen for RFID card taps on COM8 serial port and create scans';

    public function handle(): int
    {
        $this->info('RFID listener starting (COM8, 115200 baud)...');

        while (true) {
            @touch(storage_path('logs/rfid-listener.heartbeat'));

            try {
                $this->readComPort();
            } catch (\Throwable $e) {
                $this->error('Error: ' . $e->getMessage());
            }

            usleep(200_000);
        }
    }

    protected function readComPort(): void
    {
        $port = @fopen('COM8', 'r+b');
        if (!$port) {
            $this->warn('COM8 not available, retrying in 2s...');
            sleep(2);
            return;
        }

        stream_set_blocking($port, false);
        stream_set_timeout($port, 0, 200_000);

        $data = @fread($port, 1024);
        fclose($port);

        if ($data === false || strlen($data) === 0) {
            return;
        }

        $bytes = [];
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            if ($b !== 0xFF && $b !== 0x0D && $b !== 0x0A && $b !== 0x00 && $b !== 0x2C) {
                $bytes[] = $b;
            }
        }

        if (count($bytes) < 2) {
            return;
        }

        $uidHex = '';
        foreach ($bytes as $b) {
            $uidHex .= strtoupper(str_pad(dechex($b), 2, '0', STR_PAD_LEFT));
        }

        $lastScan = RfidScan::where('rfid_uid', $uidHex)
            ->where('scanned_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($lastScan) {
            return;
        }

        $device = Device::where('device_type', 'rfid-reader')
            ->orWhere('device_type', 'timbangan-iot')
            ->orderBy('last_seen_at', 'desc')
            ->first();

        if (!$device) {
            $this->warn('No RFID reader or timbangan device found in database.');
            return;
        }

        $child = Child::where('rfid_uid', $uidHex)->first();

        RfidScan::create([
            'device_id' => $device->id,
            'child_id' => optional($child)->id,
            'rfid_uid' => $uidHex,
            'status' => $child ? 'recognized' : 'unrecognized',
            'payload' => ['source' => 'com8-daemon'],
            'scanned_at' => now(),
        ]);

        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        $status = $child ? 'RECOGNIZED' : 'UNRECOGNIZED';
        $this->info("[{$status}] UID: {$uidHex}" . ($child ? " -> {$child->child_name}" : ''));
    }
}
