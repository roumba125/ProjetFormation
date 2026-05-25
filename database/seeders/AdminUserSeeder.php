<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@bergerpro.com'],
            [
                'name'     => 'Administrateur',
                'email'    => 'admin@bergerpro.com',
                'password' => Hash::make('bergerpro2024!'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name'     => 'Client Test',
                'email'    => 'client@test.com',
                'password' => Hash::make('password'),
                'role'     => 'client',
            ]
        );
    }
}
