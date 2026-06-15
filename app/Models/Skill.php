<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
{
    use CanBePrivate;

    protected $table = 'skills';

    protected $fillable = [
        'name',
        'ability',
        'custom',
        'user_id',
    ];

    protected $casts = [
        'custom' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
