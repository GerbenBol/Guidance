<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CharacterHasSubclass extends Model
{
    protected $table = 'character_has_subclasses';

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

    public function subclass(): HasOne
    {
        return $this->hasOne(Subclass::class, 'class_id');
    }
}
