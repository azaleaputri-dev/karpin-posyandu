<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Device;
use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IotMeasurementController extends Controller
{
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
            'child_id' => ['nullable', 'integer', 'exists:children,id', 'required_without:child_nik'],
            'child_nik' => ['nullable', 'string', 'exists:children,nik', 'required_without:child_id'],
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

        $device = Device::where('api_token', $token)->first();

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

        return Child::where('nik', $payload['child_nik'])->firstOrFail();
    }

    protected function markDeviceOnline(Device $device): void
    {
        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
    }
}
