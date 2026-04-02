<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'password' => bcrypt('secret'),
            'email' => 'admin@test.com',
        ]);

        new SchoolSeeder()->run();
        new DamageTypeSeeder()->run();

        // Testing only
        new TempTestingSeeder()->run();
    }
}
