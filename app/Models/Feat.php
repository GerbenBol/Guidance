<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feat extends Model
{
    protected $table = 'feats';

    protected $fillable = [
        'name',
        'short_desc',
        'description',
        'feature_id',
    ];

    public function feature(): HasOne
    {
        return $this->hasOne(Feature::class, 'feature_id');
    }
}
