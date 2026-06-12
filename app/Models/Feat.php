<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feat extends Model
{
    use CanBePrivate;

    protected $table = 'feats';

    protected $fillable = [
        'name',
        'system_version',
        'description',
        'short_desc',
        'features',
        'requirements',
    ];

    protected $casts = [
        'features' => 'array',
        'requirements' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
