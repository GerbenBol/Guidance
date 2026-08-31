<?php

namespace Database\Seeders;

use App\Models\Background;
use App\Models\Character;
use App\Models\Feat;
use App\Models\PlayerClass;
use App\Models\Race;
use App\Models\Spell;
use Illuminate\Database\Seeder;

class TempTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Background::create([
            'name' => 'TestBackground',
            'short_desc' => 'shorter',
            'description' => 'beatifuld escription',
            'profs' => [['type' => 'skill', 'granted' => 0, 'options' => 3], ['type' => 'skill', 'granted' => 1, 'options' => [3, 6, 9]], ['type' => 'skill', 'granted' => 0, 'options' => 8], ['type' => 'lang', 'granted' => 1, 'options' => []]],
            'feats' => [['granted' => 1, 'options' => [1]]],
            'equipment' => [['items' => [['type' => 'gp', 'amount' => 10], ['name' => 'Wowie pack', 'pack' => [['item' => null, 'amount' => 2]], 'type' => 'pack']]], ['items' => [['item' => null, 'type' => 'item', 'amount' => 5], ['type' => 'item-choice', 'items' => [], 'amount' => 2]]]],
            'user_id' => 1,
        ]);
        PlayerClass::create([
            'name' => 'OG',
            'user_id' => 1,
            'class_info' => ['prof' => [['type' => 'skill', 'amount' => 3, 'options' => [3, 6, 4, 8, 16, 17, 13]]], 'asi_lvls' => [4, 8, 12, 16], 'equipment' => [['items' => [['name' => 'Explorer\'s Pack', 'pack' => [['item' => null, 'amount' => null]], 'type' => 'pack'], ['type' => 'gp', 'amount' => 2]]], ['items' => [['item' => null, 'type' => 'item', 'amount' => 4], ['type' => 'item-choice', 'items' => [], 'amount' => 1]]], ['items' => [['type' => 'gp', 'amount' => 115]]]], 'save_prof' => ['constitution', 'intelligence'], 'subclass_name' => 'Original Gang Strength', 'multiclass_prof' => [['type' => 'skill', 'amount' => 1, 'options' => [3, 6, 4, 8, 16, 17, 13]]], 'primary_ability' => 'dexterity', 'secondary_ability' => 'constitution', 'subclass_start_lvl' => 3],
            'spell_info' => ['spells' => [1, 2, 3], 'spellslots' => '{\"1\":{\"1\":2},\"2\":{\"1\":3},\"3\":{\"1\":3,\"2\":2},\"4\":{\"1\":4,\"2\":3},\"5\":{\"1\":4,\"2\":3,\"3\":2},\"6\":{},\"7\":{},\"8\":{},\"9\":{},\"10\":{},\"11\":{},\"12\":{},\"13\":{},\"14\":{},\"15\":{},\"16\":{},\"17\":{},\"18\":{},\"19\":{},\"20\":{}}', 'spell_ability' => 'intelligence', 'can_cast_spells' => true, 'has_own_spelllist' => true, 'regains_spells_on' => 'lr', 'knows_full_spelllist' => false, 'known_prepared_amounts' => '{\"1\":{\"cantrips\":3,\"spells\":7},\"8\":{\"cantrips\":4}}'],
            'hit_die' => 'd6',
            'max_levels' => 20,
        ]);
        PlayerClass::create([
            'name' => 'Tester',
            'user_id' => 1,
            'class_info' => ['prof' => [['type' => 'skill', 'amount' => 3, 'options' => [2, 1, 6, 7, 9]]], 'asi_lvls' => [], 'save_prof' => [], 'subclass_name' => null, 'multiclass_prof' => [['type' => 'skill', 'amount' => 2, 'options' => [2, 1, 6, 7, 9]]], 'primary_ability' => null, 'secondary_ability' => null, 'subclass_start_lvl' => null],
            'spell_info' => ['spellslots' => null, 'borrows_from' => null, 'extra_spells' => [], 'spell_ability' => null, 'can_cast_spells' => false, 'has_own_spelllist' => null],
            'hit_die' => 'd8',
            'max_levels' => 20,
        ]);
        Feat::create([
            'name' => 'TestFeat',
            'features' => [['name' => null, 'snippet' => null, 'replaces' => false, 'mechanics' => [['note' => null, 'grant' => null, 'choice' => false, 'modifier_validated' => 0]], 'has_active' => false, 'description' => null, 'show_in_actions' => false]],
            'user_id' => 1,
        ]);
        Race::create([
            'name' => 'TestRace',
            'short_desc' => 'this race does stuff',
            'description' => 'this race does stuff but this is a more detailed description',
            'features' => [['name' => 'feature you get on level 1', 'level' => 1, 'snippet' => null, 'replaces' => false, 'mechanics' => [['note' => null, 'grant' => 'dadv', 'choice' => true, 'options' => ['skill-2', 'skill-4', 'skill-12', 'skill-11'], 'modifier_validated' => 0]], 'has_active' => false, 'description' => 'its something that does something in a specific case', 'show_in_actions' => false, 'optional_feature' => false], ['name' => 'one more race feature', 'level' => 1, 'snippet' => null, 'replaces' => false, 'mechanics' => [['note' => null, 'grant' => null, 'choice' => false, 'modifier_validated' => 0]], 'has_active' => false, 'description' => 'wowie now i can do this thing', 'show_in_actions' => false, 'optional_feature' => false]],
            'user_id' => 1,
        ]);
        Spell::create([
            'name' => 'TestSpell',
            'short_desc' => 'Makes you disappear as an action',
            'level' => 0,
            'components' => ['materials' => 'thingy', 'components' => ['v', 'm']],
            'arearange' => ['range' => '60 feet', 'area' => '20 Sphere'],
            'casting_time' => ['time' => 1, 'type' => 'freeAction'],
            'duration' => ['type' => 'minute', 'duration' => 1, 'concentration' => true],
            'school_id' => 6,
            'user_id' => 1,
        ]);
        Spell::create([
            'name' => 'Enchant',
            'short_desc' => 'Enchant someone',
            'level' => 1,
            'components' => ['materials' => null, 'components' => ['v', 's']],
            'arearange' => ['range' => 'self', 'area' => ' '],
            'casting_time' => ['time' => 1, 'type' => 'action'],
            'duration' => ['type' => 'instant', 'duration' => null, 'concentration' => false],
            'school_id' => 4,
            'user_id' => 1,
        ]);
        Spell::create([
            'name' => 'Discombobulate',
            'short_desc' => '<p></p>',
            'level' => 7,
            'components' => [],
            'arearange' => [],
            'casting_time' => [],
            'duration' => [],
            'school_id' => 4,
            'user_id' => 1,
        ]);
        Character::create([
            'name' => 'Mr. Test',
            'classes' => [['hp' => ['level1' => 6, 'level2' => 3], 'id' => '1', 'level' => '2', 'total_hp' => 9, 'dice_used' => ['level1', 'level2'], 'mechanics' => ['skill0' => 4, 'skill1' => 13, 'skill2' => 8], 'used_dice' => 2, 'chosen_spells' => '[2]', 'chosen_cantrips' => '[1]', 'OG Core Traits Primary Class' => null], ['hp' => ['level1' => 8, 'level2' => 3, 'level3' => 6], 'id' => '2', 'level' => '3', 'total_hp' => 17, 'dice_used' => ['level1', 'level2', 'level3'], 'used_dice' => 3, 'chosen_spells' => null, 'chosen_cantrips' => null, 'Tester Core Traits Multiclass' => null]],
            'race_id' => 1,
            'background_id' => 1,
            'race_options' => ['dadv0' => null],
            'background_options' => ['feat0' => 0, 'skill1' => 6],
            'inventory' => ['coin' => ['cp' => 0, 'ep' => 0, 'gp' => 125, 'pp' => 0, 'sp' => 0], 'items' => [['item' => null, 'amount' => 2]]],
            'extra_info' => ['scores' => [['score' => 10, 'ability' => 'Strength', 'misc_bonus' => null, 'score_override'], ['score' => 11, 'ability' => 'Dexterity', 'misc_bonus' => null, 'score_override'], ['score' => 14, 'ability' => 'Constitution', 'misc_bonus' => null, 'score_override'], ['score' => 13, 'ability' => 'Intelligence', 'misc_bonus' => null, 'score_override'], ['score' => 14, 'ability' => 'Wisdom', 'misc_bonus' => null, 'score_override'], ['score' => 11, 'ability' => 'Charisma', 'misc_bonus' => null, 'score_override']], 'gen_method' => 'buy', 'hp_modifier' => null, 'used_points' => 27, 'overwrite_hp' => null, 'use_fixed_hp' => false],
            'player_id' => 1,
        ]);
    }
}
