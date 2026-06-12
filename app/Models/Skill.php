<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use CanBePrivate;

    protected $table = 'skills';

    protected $fillable = [
        'name',
        'ability',
        'custom',
    ];

    protected $casts = [
        'custom' => 'boolean',
    ];
}
