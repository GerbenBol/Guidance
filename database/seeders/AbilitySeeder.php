<?php

namespace Database\Seeders;

use App\Models\Ability;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ability::insert([[
            'name' => 'Strength',
            'shorthand' => 'STR',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'name' => 'Dexterity',
            'shorthand' => 'DEX',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'name' => 'Constitution',
            'shorthand' => 'CON',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'name' => 'Intelligence',
            'shorthand' => 'INT',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'name' => 'Wisdom',
            'shorthand' => 'WIS',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'name' => 'Charisma',
            'shorthand' => 'CHA',
            'created_at' => now(),
            'updated_at' => now(),
        ]]);
    }
}
