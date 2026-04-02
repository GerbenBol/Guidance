<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'features',
        'spell_info',
        'user_id',
    ];

    protected $casts = [
        'features' => 'array',
        'spell_info' => 'object',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
