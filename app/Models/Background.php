<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Background extends Model
{
    protected $table = 'backgrounds';

    protected $fillable = [
        'name',
        'description',
        'feature_id',
    ];

    public function feature(): HasOne
    {
        return $this->hasOne(Feature::class, 'feature_id');
    }
}
