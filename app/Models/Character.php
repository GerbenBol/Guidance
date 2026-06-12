<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Character extends Model
{
    use CanBePrivate;

    protected $table = 'characters';

    protected $fillable = [
        'name',
        'race_id',
        'background_id',
        'race_options',
        'background_options',
        'player_id',
    ];

    protected $casts = [
        'race_options' => 'array',
        'background_options' => 'array',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    // public function race(): HasOne
    // {
    //     return $this->hasOne(Race::class, 'race_id');
    // }

    // public function background(): HasOne
    // {
    //     return $this->hasOne(Background::class, 'background_id');
    // }

    // public function classes(): HasManyThrough
    // {
    //     return $this->hasManyThrough(
    //         PlayerClass::class,
    //         CharacterHasClass::class,
    //         'id',
    //         'id'
    //     );
    // }
}
