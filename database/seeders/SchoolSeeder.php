<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::create([
            'name' => 'Abjuration',
            'general_color' => 'Blue',
            'one_word_desc' => 'Protection',
            'description' => 'Protective magic, wards and negation',
        ]);
        School::create([
            'name' => 'Conjuration',
            'general_color' => 'Gold',
            'one_word_desc' => 'Summoning',
            'description' => 'Teleportation, summoning creatures, and creating objects',
        ]);
        School::create([
            'name' => 'Divination',
            'general_color' => 'Silver',
            'one_word_desc' => 'Knowledge',
            'description' => 'Gathering information, predicting the future, and seeing things not normally visible',
        ]);
        School::create([
            'name' => 'Enchantment',
            'general_color' => 'Pink',
            'one_word_desc' => 'Mental Control',
            'description' => 'Manipulating or influencing the minds of others',
        ]);
        School::create([
            'name' => 'Evocation',
            'general_color' => 'Red',
            'one_word_desc' => 'Energy',
            'description' => 'Manipulating magical energy to produce explosions or damage',
        ]);
        School::create([
            'name' => 'Illusion',
            'general_color' => 'Purple',
            'one_word_desc' => 'Tricks',
            'description' => 'Deceiving senses and warping reality to confuse enemies',
        ]);
        School::create([
            'name' => 'Necromancy',
            'general_color' => 'Green',
            'one_word_desc' => '(Un-)Death',
            'description' => 'Manipulating life force, creating undeath, or causing decay',
        ]);
        School::create([
            'name' => 'Transmutation',
            'general_color' => 'Bronze',
            'one_word_desc' => 'Transformation',
            'description' => 'Altering the physical form or properties of objects or creatures',
        ]);
    }
}
