<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $table = 'features';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'benefits',
        'feature_options',
        'requirements',
    ];

    protected $casts = [
        'benefits' => 'object',
        'feature_options' => 'object',
        'requirements' => 'object',
    ];
}
