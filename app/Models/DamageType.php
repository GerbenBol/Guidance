<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageType extends Model
{
    protected $table = 'damage_types';

    protected $fillable = [
        'name',
        'icon',
    ];
}
