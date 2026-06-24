<?php

namespace Database\Seeders;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posyandu = Posyandu::firstOrCreate(
            ['code' => 'PSY-001'],
            [
                'name' => 'Posyandu Melati',
                'address' => 'Jl. Posyandu No. 1',
                'village' => 'Sukamaju',
                'contact_phone' => '081234567890',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Posyandu',
                'role' => 'admin',
                'posyandu_id' => null,
                'password' => Hash::make('admin12345'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@gmail.com'],
            [
                'name' => 'Petugas Posyandu',
                'role' => 'petugas',
                'posyandu_id' => $posyandu->id,
                'password' => Hash::make('petugas12345'),
            ]
        );
    }
}
