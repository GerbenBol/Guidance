<?php

namespace Database\Seeders;

use App\Enums\Ability;
use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Skill::create([
            'name' => 'Acrobatics',
            'ability' => Ability::dexterity,
        ]);
        Skill::create([
            'name' => 'Animal Handling',
            'ability' => Ability::wisdom,
        ]);
        Skill::create([
            'name' => 'Arcana',
            'ability' => Ability::intelligence,
        ]);
        Skill::create([
            'name' => 'Athletics',
            'ability' => Ability::strength,
        ]);
        Skill::create([
            'name' => 'Deception',
            'ability' => Ability::charisma,
        ]);
        Skill::create([
            'name' => 'History',
            'ability' => Ability::intelligence,
        ]);
        Skill::create([
            'name' => 'Insight',
            'ability' => Ability::wisdom,
        ]);
        Skill::create([
            'name' => 'Intimidation',
            'ability' => Ability::charisma,
        ]);
        Skill::create([
            'name' => 'Investigation',
            'ability' => Ability::intelligence,
        ]);
        Skill::create([
            'name' => 'Medicine',
            'ability' => Ability::wisdom,
        ]);
        Skill::create([
            'name' => 'Nature',
            'ability' => Ability::intelligence,
        ]);
        Skill::create([
            'name' => 'Perception',
            'ability' => Ability::wisdom,
        ]);
        Skill::create([
            'name' => 'Performance',
            'ability' => Ability::charisma,
        ]);
        Skill::create([
            'name' => 'Persuasion',
            'ability' => Ability::charisma,
        ]);
        Skill::create([
            'name' => 'Religion',
            'ability' => Ability::intelligence,
        ]);
        Skill::create([
            'name' => 'Sleight of Hand',
            'ability' => Ability::dexterity,
        ]);
        Skill::create([
            'name' => 'Stealth',
            'ability' => Ability::dexterity,
        ]);
        Skill::create([
            'name' => 'Survival',
            'ability' => Ability::wisdom,
        ]);
    }
}
