<?php

namespace Database\Seeders;

use App\Models\DamageType;
use Illuminate\Database\Seeder;

class DamageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DamageType::create([
            'name' => 'Force',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Bludgeoning',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Slashing',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Piercing',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Fire',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Cold',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Lightning',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Thunder',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Poison',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Acid',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Radiant',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Necrotic',
            'icon' => '',
        ]);
        DamageType::create([
            'name' => 'Psychic',
            'icon' => '',
        ]);
    }
}
