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
        'damage',
        'school_id',
    ];

    protected $casts = [
        'damage' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
