<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'features',
        'spell_info',
    ];

    protected $casts = [
        'features' => 'array',
        'spell_info' => 'object',
    ];
}
