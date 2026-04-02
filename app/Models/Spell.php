<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spell extends Model
{
    protected $table = 'spells';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'level',
        'components',
        'arearange',
        'casting_time',
        'duration',
        'effect',
        'scaling',
        'school_id',
        'user_id',
    ];

    protected $casts = [
        'components' => 'array',
        'casting_time' => 'array',
        'duration' => 'array',
        'effect' => 'array',
        'scaling' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
