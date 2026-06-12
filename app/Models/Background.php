<?php

namespace App\Models;

use App\Traits\CanBePrivate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Background extends Model
{
    use CanBePrivate;

    protected $table = 'backgrounds';

    protected $fillable = [
        'name',
        'system_version',
        // 'short_desc',
        'description',
        'profs',
        'feats',
        'equipment',
        'user_id',
    ];

    protected $casts = [
        'skill_profs' => 'array',
        'feats' => 'array',
        'equipment' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
