<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerClass extends Model
{
    use CanBePrivate;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'system_version',
        'short_desc',
        'description',
        'class_info',
        'features',
        'spell_info',
        'hit_die',
        'max_levels',
        'user_id',
    ];

    protected $casts = [
        'class_info' => 'object',
        'features' => 'array',
        'spell_info' => 'object',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
