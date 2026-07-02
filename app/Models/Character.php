<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Character extends Model
{
    use CanBePrivate;

    protected $table = 'characters';

    protected $fillable = [
        'name',
        'system_version',
        'classes',
        'race_id',
        'background_id',
        'race_options',
        'background_options',
        'player_id',
        'inventory',
        'settings',
        'extra_info',
        'updated',
    ];

    protected $casts = [
        'classes' => 'array',
        'race_options' => 'array',
        'background_options' => 'array',
        'inventory' => 'array',
        'settings' => 'array',
        'extra_info' => 'array',
        'updated' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function race(): HasOne
    {
        return $this->hasOne(Race::class, 'race_id');
    }

    public function background(): HasOne
    {
        return $this->hasOne(Background::class, 'background_id');
    }

    public function classes(): Collection
    {
        $classes = [];

        foreach ($this->classes as $class) {
            $classes[] = $class['id'];
        }

        return PlayerClass::find($classes);
    }
}
