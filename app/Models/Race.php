<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $table = 'races';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'features',
    ];

    protected $casts = [
        'features' => 'object',
    ];
}
