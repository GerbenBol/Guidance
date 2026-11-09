<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Ability extends Model
{
    protected $fillable = [
        'name',
        'shorthand',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function savingThrow(): string
    {
        return $this->name.' Save';
    }

    public static function allSaves(): Collection
    {
        return self::all()->map(fn ($item) => $item->savingThrow());
    }
}
