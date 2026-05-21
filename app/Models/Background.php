<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Background extends Model
{
    protected $table = 'backgrounds';

    protected $fillable = [
        'name',
        'system_version',
        // 'short_desc',
        'description',
        'skill_profs',
        'feats',
        'user_id',
    ];

    protected $casts = [
        'skill_profs' => 'array',
        'feats' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
