<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\RfidScan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IotMeasurementController extends Controller
{
    public function scanRfid(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        $payload = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);
        $rfidUid = $this->normalizeRfidUid($payload['rfid_uid']);
        $child = Child::where('rfid_uid', $rfidUid)->first();

        if ($child && $device->posyandu_id && $child->posyandu_id !== $device->posyandu_id) {
            $child = null;
        }

        $scan = RfidScan::create([
            'device_id' => $device->id,
            'child_id' => optional($child)->id,
            'rfid_uid' => $rfidUid,
            'status' => $child ? 'recognized' : 'unrecognized',
            'payload' => $request->except(['rfid_uid']),
            'scanned_at' => now(),
        ]);

        $this->markDeviceOnline($device);

        return response()->json([
            'message' => $child ? 'RFID recognized.' : 'RFID is not registered.',
            'data' => [
                'scan_id' => $scan->id,
                'rfid_uid' => $rfidUid,
                'status' => $scan->status,
                'child' => $child ? [
                    'id' => $child->id,
                    'child_name' => $child->child_name,
                    'nik' => $child->nik,
                    'posyandu_id' => $child->posyandu_id,
                ] : null,
            ],
        ], $child ? 200 : 202);
    }

    public function ping(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        $this->markDeviceOnline($device);

        return response()->json([
            'message' => 'Device authenticated.',
            'device' => [
                'id' => $device->id,
                'device_code' => $device->device_code,
                'device_name' => $device->device_name,
                'status' => $device->status,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        $payload = $request->validate([
            'child_id' => ['nullable', 'integer', 'exists:children,id', 'required_without_all:child_nik,rfid_uid'],
            'child_nik' => ['nullable', 'string', 'exists:children,nik', 'required_without_all:child_id,rfid_uid'],
            'rfid_uid' => ['nullable', 'string', 'required_without_all:child_id,child_nik'],
            'measured_at' => ['nullable', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'height_cm' => ['required', 'numeric', 'min:0'],
            'temperature_c' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $child = $this->resolveChild($payload);

        if ($device->posyandu_id && $child->posyandu_id !== $device->posyandu_id) {
            throw ValidationException::withMessages([
                'child_id' => 'Anak tidak terdaftar pada posyandu perangkat ini.',
            ]);
        }

        $measurement = Measurement::create([
            'child_id' => $child->id,
            'device_id' => $device->id,
            'measured_at' => isset($payload['measured_at']) ? Carbon::parse($payload['measured_at']) : now(),
            'weight_kg' => $payload['weight_kg'],
            'height_cm' => $payload['height_cm'],
            'temperature_c' => $payload['temperature_c'] ?? null,
            'source' => 'iot',
            'notes' => $payload['notes'] ?? null,
        ]);

        $this->markDeviceOnline($device);

        return response()->json([
            'message' => 'Measurement stored successfully.',
            'data' => [
                'measurement_id' => $measurement->id,
                'device_code' => $device->device_code,
                'child_id' => $child->id,
                'source' => $measurement->source,
                'measured_at' => $measurement->measured_at->toDateTimeString(),
            ],
        ], 201);
    }

    protected function resolveDevice(Request $request): Device
    {
        $token = $request->header('X-Device-Token') ?: $request->bearerToken();

        if (! $token) {
            abort(response()->json([
                'message' => 'Device token is required.',
            ], 401));
        }

        $tokenHash = hash('sha256', $token);

        $device = Device::where('api_token_hash', $tokenHash)->first();

        if (! $device) {
            $device = Device::where('api_token', $token)->first();

            if ($device) {
                $device->update(['api_token_hash' => $tokenHash]);
            }
        }

        if (! $device) {
            abort(response()->json([
                'message' => 'Invalid device token.',
            ], 401));
        }

        return $device;
    }

    protected function resolveChild(array $payload): Child
    {
        if (! empty($payload['child_id'])) {
            return Child::findOrFail($payload['child_id']);
        }

        if (! empty($payload['child_nik'])) {
            return Child::where('nik', $payload['child_nik'])->firstOrFail();
        }

        return Child::where('rfid_uid', $this->normalizeRfidUid($payload['rfid_uid']))->firstOrFail();
    }

    protected function normalizeRfidUid(string $rfidUid): string
    {
        return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rfidUid));
    }

    protected function markDeviceOnline(Device $device): void
    {
        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
    }
}
