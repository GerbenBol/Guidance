<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sheet extends Model
{
    protected $table = 'sheets';

    protected $fillable = [
        'character_id',
        'info',
    ];

    protected $casts = [
        'info' => 'array',
    ];

    public function character(): BelongsTo {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
