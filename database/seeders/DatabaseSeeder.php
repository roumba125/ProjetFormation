<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BreedSeeder::class,
            SheepSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
