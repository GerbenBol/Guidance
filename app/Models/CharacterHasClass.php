<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CharacterHasClass extends Model
{
    protected $table = 'character_has_classes';

    protected $fillable = [
        'character_id',
        'class_id',
        'class_options',
    ];

    protected $casts = [
        'class_options' => 'object',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function class(): HasOne
    {
        return $this->hasOne(PlayerClass::class, 'class_id');
    }
}
