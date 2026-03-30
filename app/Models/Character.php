<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Character extends Model
{
    protected $table = 'characters';

    protected $fillable = [
        'name',
        'race_id',
        'background_id',
        'race_options',
        'background_options',
    ];

    protected $casts = [
        'race_options' => 'object',
        'background_options' => 'object',
    ];

    public function race(): HasOne
    {
        return $this->hasOne(Race::class, 'race_id');
    }

    public function background(): HasOne
    {
        return $this->hasOne(Background::class, 'background_id');
    }

    public function classes(): HasManyThrough
    {
        return $this->hasManyThrough(
            PlayerClass::class,
            CharacterHasClass::class,
            'id',
            'id'
        );
    } // check if this works the way i think it works before also implementing it for subclasses
}
