<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Feature extends Model
{
    protected $fillable = [
        'name',
        'level',
        'snippet',
        'description',
        'mechanics',
    ];

    protected $casts = [
        'mechanics' => 'array',
    ];

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function sheetFeature(): HasOne
    {
        return $this->hasOne(SheetFeature::class);
    }
}
