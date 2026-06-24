<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Device;
use App\Models\Posyandu;
use App\Models\User;
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

    public function test_rfid_scan_resolves_child_and_can_store_measurement(): void
    {
        $posyandu = Posyandu::create([
            'name' => 'Posyandu RFID',
            'code' => 'PSY-RFID',
            'address' => 'Jl. RFID',
            'village' => 'Sukamaju',
        ]);
        $child = Child::create([
            'posyandu_id' => $posyandu->id,
            'nik' => '9876543210987654',
            'rfid_uid' => 'A1B2C3D4',
            'child_name' => 'Bima',
            'gender' => 'L',
            'birth_date' => '2022-02-02',
            'mother_name' => 'Ani',
        ]);
        $token = Str::random(40);
        $device = Device::create([
            'posyandu_id' => $posyandu->id,
            'device_code' => 'RFID-READER-01',
            'device_name' => 'RFID Reader',
            'device_type' => 'rfid-reader',
            'status' => 'offline',
            'api_token' => $token,
            'api_token_hash' => hash('sha256', $token),
        ]);

        $this->withHeaders(['X-Device-Token' => $token])
            ->postJson('/api/iot/rfid/scan', ['rfid_uid' => 'a1:b2:c3:d4'])
            ->assertOk()
            ->assertJsonPath('data.child.id', $child->id);

        $this->withHeaders(['X-Device-Token' => $token])
            ->postJson('/api/iot/measurements', [
                'rfid_uid' => 'A1 B2 C3 D4',
                'weight_kg' => 14.2,
                'height_cm' => 95.4,
            ])
            ->assertCreated()
            ->assertJsonPath('data.child_id', $child->id);

        $this->assertDatabaseHas('rfid_scans', [
            'device_id' => $device->id,
            'child_id' => $child->id,
            'rfid_uid' => 'A1B2C3D4',
            'status' => 'recognized',
        ]);

        $user = User::create([
            'name' => 'Admin RFID',
            'email' => 'rfid-admin@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->getJson('/iot/latest-scan')
            ->assertOk()
            ->assertJsonPath('data.child.id', $child->id)
            ->assertJsonPath('data.rfid_uid', 'A1B2C3D4');
    }

    public function test_latest_scan_shows_connected_device_even_before_first_rfid_tap(): void
    {
        $posyandu = Posyandu::create([
            'name' => 'Posyandu Live',
            'code' => 'PSY-LIVE',
            'address' => 'Jl. Live',
            'village' => 'Sukamaju',
        ]);

        $token = Str::random(40);
        $device = Device::create([
            'posyandu_id' => $posyandu->id,
            'device_code' => 'RFID-LIVE-01',
            'device_name' => 'RFID Live Reader',
            'device_type' => 'rfid-reader',
            'status' => 'offline',
            'api_token' => $token,
            'api_token_hash' => hash('sha256', $token),
        ]);

        $this->withHeaders(['X-Device-Token' => $token])
            ->getJson('/api/iot/ping')
            ->assertOk()
            ->assertJsonPath('device.device_name', 'RFID Live Reader');

        $user = User::create([
            'name' => 'Admin Live',
            'email' => 'admin-live@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->getJson('/iot/latest-scan')
            ->assertOk()
            ->assertJsonPath('data.device.device_name', 'RFID Live Reader')
            ->assertJsonPath('data.id', null);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'status' => 'online',
        ]);
    }
}
