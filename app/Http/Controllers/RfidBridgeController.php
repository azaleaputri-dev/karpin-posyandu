<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Device;
use App\Models\RfidScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidBridgeController extends Controller
{
    public function index()
    {
        return view('rfid-bridge.index', [
            'devices' => Device::orderBy('device_name')->get(),
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'exists:devices,id'],
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);

        $device = Device::findOrFail($data['device_id']);
        $rfidUid = $this->normalizeRfidUid($data['rfid_uid']);
        $child = Child::where('rfid_uid', $rfidUid)->first();

        if ($child && $device->posyandu_id && $child->posyandu_id !== $device->posyandu_id) {
            $child = null;
        }

        $scan = RfidScan::create([
            'device_id' => $device->id,
            'child_id' => optional($child)->id,
            'rfid_uid' => $rfidUid,
            'status' => $child ? 'recognized' : 'unrecognized',
            'payload' => ['source' => 'bridge'],
            'scanned_at' => now(),
        ]);

        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

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
                    'gender' => $child->gender,
                    'birth_date' => $child->birth_date->format('Y-m-d'),
                    'age' => $child->birth_date->diffForHumans(null, true),
                    'mother_name' => $child->mother_name,
                    'posyandu_id' => $child->posyandu_id,
                ] : null,
            ],
        ], $child ? 200 : 202);
    }

    protected function normalizeRfidUid(?string $rfidUid): ?string
    {
        if (! $rfidUid) {
            return null;
        }

        return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rfidUid));
    }
}
