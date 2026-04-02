<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\PlayerClass;
use App\Models\Spell;
use Illuminate\Database\Seeder;

class TempTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Character::create([
            'name' => 'Mr. Test',
            'player_id' => 1,
        ]);
        PlayerClass::create([
            'name' => 'OG',
            'user_id' => 1,
        ]);
        PlayerClass::create([
            'name' => 'Tester',
            'user_id' => 1,
        ]);
        Spell::create([
            'name' => 'TestSpell',
            'short_desc' => 'Makes you disappear as an action',
            'level' => 3,
            'school_id' => 6,
            'user_id' => 1,
        ]);
        Spell::create([
            'name' => 'Enchant',
            'short_desc' => 'Enchant someone',
            'level' => 1,
            'school_id' => 4,
            'user_id' => 1,
        ]);
    }
}
