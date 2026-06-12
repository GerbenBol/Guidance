<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecordPrivate extends Model
{
    protected $table = 'model_record_private';

    public function privatable(): MorphTo
    {
        return $this->morphTo();
    }
}
