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
            'class_info' => ['asi_lvls' => [], 'save_prof' => ['constitution', 'intelligence'], 'skill_prof' => [3, 4, 6, 5, 1, 17, 14], 'subclass_name' => null, 'primary_ability' => 'dexterity', 'secondary_ability' => 'constitution', 'subclass_start_lvl' => null, 'amount_of_skill_prof' => 3],
            'spell_info' => ['spellslots' => null, 'borrows_from' => null, 'extra_spells' => [], 'spell_ability' => null, 'can_cast_spells' => false, 'has_own_spelllist' => null],
            'hit_die' => 'd6',
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
