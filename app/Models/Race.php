<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Race extends Model
{
    protected $table = 'races';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'features',
        'user_id',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
