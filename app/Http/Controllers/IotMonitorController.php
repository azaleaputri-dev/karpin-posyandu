<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\RfidScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotMonitorController extends Controller
{
    public function startListener(): JsonResponse
    {
        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $heartbeat = $logDir . '/rfid-listener.heartbeat';

        if (file_exists($heartbeat) && time() - @filemtime($heartbeat) < 15) {
            return response()->json(['status' => 'running']);
        }

        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $logFile = $logDir . '/rfid-listener.log';
        $cmd = sprintf('start /B "%s" "%s" rfid:listen > "%s" 2>&1', $php, $artisan, $logFile);
        pclose(popen($cmd, 'r'));

        return response()->json(['status' => 'started']);
    }
    public function latestScan(Request $request): JsonResponse
    {
        $query = RfidScan::with([
            'device:id,device_code,device_name,status,last_seen_at',
            'child.posyandu:id,name',
            'child.measurements' => function ($builder) {
                $builder->latest('measured_at')->limit(1);
            },
        ])->latest('scanned_at');

        if ($request->user()->isPetugas() && $request->user()->posyandu_id) {
            $posyanduId = $request->user()->posyandu_id;
            $query->whereHas('device', function ($builder) use ($posyanduId) {
                $builder->where('posyandu_id', $posyanduId);
            });
        }

        $scan = $query->first();
        $device = $this->latestConnectedDevice($request);

        $deviceData = $device ? [
            'id' => $device->id,
            'device_code' => $device->device_code,
            'device_name' => $device->device_name,
            'status' => $device->status,
            'last_seen_at' => optional($device->last_seen_at)->toIso8601String(),
            'last_seen_at_human' => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : null,
        ] : null;

        if (! $scan) {
            return response()->json([
                'data' => [
                    'id' => null,
                    'rfid_uid' => null,
                    'status' => null,
                    'scanned_at' => null,
                    'scanned_at_human' => null,
                    'device' => $deviceData,
                    'child' => null,
                    'latest_measurement' => null,
                ],
            ]);
        }

        $child = $scan->child;
        $latestMeasurement = $child ? $child->measurements->first() : null;

        return response()->json([
            'data' => [
                'id' => $scan->id,
                'rfid_uid' => $scan->rfid_uid,
                'status' => $scan->status,
                'scanned_at' => $scan->scanned_at->toIso8601String(),
                'scanned_at_human' => $scan->scanned_at->diffForHumans(),
                'device' => $deviceData ?? $this->formatDevice($scan->device),
                'child' => $child ? [
                    'id' => $child->id,
                    'child_name' => $child->child_name,
                    'nik' => $child->nik,
                    'gender' => $child->gender,
                    'birth_date' => $child->birth_date->format('Y-m-d'),
                    'age' => $child->birth_date->diffForHumans(null, true),
                    'mother_name' => $child->mother_name,
                    'posyandu' => optional($child->posyandu)->name,
                    'detail_url' => route('children.show', $child),
                    'edit_url' => route('children.edit', $child),
                    'delete_url' => route('children.destroy', $child),
                ] : null,
                'latest_measurement' => $latestMeasurement ? [
                    'weight_kg' => $latestMeasurement->weight_kg,
                    'height_cm' => $latestMeasurement->height_cm,
                    'temperature_c' => $latestMeasurement->temperature_c,
                    'measured_at' => $latestMeasurement->measured_at->format('d M Y H:i'),
                ] : null,
            ],
        ]);
    }

    protected function latestConnectedDevice(Request $request): ?Device
    {
        $query = Device::query()
            ->where('status', 'online')
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->latest('last_seen_at');

        if ($request->user()->isPetugas() && $request->user()->posyandu_id) {
            $query->where('posyandu_id', $request->user()->posyandu_id);
        }

        return $query->first();
    }

    protected function formatDevice(?Device $device): ?array
    {
        if (! $device) {
            return null;
        }

        return [
            'id' => $device->id,
            'device_code' => $device->device_code,
            'device_name' => $device->device_name,
            'status' => $device->status,
            'last_seen_at' => optional($device->last_seen_at)->toIso8601String(),
            'last_seen_at_human' => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : null,
        ];
    }

}
