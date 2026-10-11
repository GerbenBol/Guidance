<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Race extends Model
{
    use CanBePrivate;

    protected $table = 'races';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'user_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function features(): MorphMany
    {
        return $this->morphMany(Feature::class, 'parent');
    }
}
