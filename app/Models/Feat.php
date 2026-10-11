<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Feat extends Model
{
    use CanBePrivate;

    protected $table = 'feats';

    protected $fillable = [
        'name',
        'system_version',
        'user_id',
        'description',
        'short_desc',
        'requirements',
    ];

    protected $casts = [
        'requirements' => 'array',
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
