<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Device;
use App\Models\Posyandu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IotMeasurementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_can_store_measurement_via_api(): void
    {
        $posyandu = Posyandu::create([
            'name' => 'Posyandu Mawar',
            'code' => 'PSY-001',
            'address' => 'Jl. Melati No. 1',
            'village' => 'Sukamaju',
        ]);

        $child = Child::create([
            'posyandu_id' => $posyandu->id,
            'nik' => '1234567890123456',
            'child_name' => 'Alya',
            'gender' => 'P',
            'birth_date' => '2023-01-10',
            'mother_name' => 'Siti',
        ]);

        $device = Device::create([
            'posyandu_id' => $posyandu->id,
            'device_code' => 'DEV-001',
            'device_name' => 'Timbangan 1',
            'device_type' => 'timbangan-iot',
            'status' => 'offline',
            'api_token' => Str::random(40),
        ]);

        $response = $this->withHeaders([
            'X-Device-Token' => $device->api_token,
        ])->postJson('/api/iot/measurements', [
            'child_id' => $child->id,
            'weight_kg' => 12.4,
            'height_cm' => 87.1,
            'temperature_c' => 36.7,
            'notes' => 'Payload dari alat',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.device_code', 'DEV-001')
            ->assertJsonPath('data.child_id', $child->id);

        $this->assertDatabaseHas('measurements', [
            'child_id' => $child->id,
            'device_id' => $device->id,
            'source' => 'iot',
        ]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'status' => 'online',
        ]);
    }

    public function test_request_is_rejected_without_device_token(): void
    {
        $response = $this->postJson('/api/iot/measurements', [
            'child_id' => 1,
            'weight_kg' => 10.2,
            'height_cm' => 80.1,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Device token is required.',
            ]);
    }
}
