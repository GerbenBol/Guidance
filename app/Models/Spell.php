<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spell extends Model
{
    protected $table = 'spells';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'level',
        'damage',
    ];
}
