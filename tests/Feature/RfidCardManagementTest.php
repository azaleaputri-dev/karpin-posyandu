<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfidCardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_and_remove_child_rfid_card(): void
    {
        $posyandu = Posyandu::create([
            'name' => 'Posyandu Melati',
            'code' => 'PSY-001',
            'address' => 'Jl. Melati',
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
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-rfid@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/rfid-cards', [
                'child_id' => $child->id,
                'rfid_uid' => '32:750:123 456 789',
            ])
            ->assertRedirect('/rfid-cards');

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'rfid_uid' => '32750123456789',
        ]);

        $this->actingAs($admin)
            ->delete('/rfid-cards/' . $child->id)
            ->assertRedirect('/rfid-cards');

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'rfid_uid' => null,
        ]);
    }
}
