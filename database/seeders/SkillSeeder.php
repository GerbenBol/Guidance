<?php

namespace Database\Seeders;

use App\Models\Ability;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Ability::all() as $ability) {
            $ability->skills()->createMany(match ($ability->shorthand) {
                'STR' => [[
                    'name' => 'Athletics',
                ]],
                'DEX' => [[
                    'name' => 'Acrobatics',
                ], [
                    'name' => 'Sleight of Hand',
                ], [
                    'name' => 'Stealth',
                ]],
                'CON' => [],
                'INT' => [[
                    'name' => 'Religion',
                ], [
                    'name' => 'Nature',
                ], [
                    'name' => 'Investigation',
                ], [
                    'name' => 'History',
                ], [
                    'name' => 'Arcana',
                ]],
                'WIS' => [[
                    'name' => 'Survival',
                ], [
                    'name' => 'Perception',
                ], [
                    'name' => 'Medicine',
                ], [
                    'name' => 'Insight',
                ], [
                    'name' => 'Animal Handling',
                ]],
                'CHA' => [[
                    'name' => 'Deception',
                ], [
                    'name' => 'Intimidation',
                ], [
                    'name' => 'Persuasion',
                ], [
                    'name' => 'Performance',
                ]],
            });
        }
    }
}
