<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subclass extends Model
{
    protected $table = 'subclasses';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'features',
        'class_id',
    ];

    protected $casts = [
        'features' => 'object',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(PlayerClass::class, 'class_id');
    }
}
