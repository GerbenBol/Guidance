<?php

namespace App\Traits;

use App\Models\RecordPrivate;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

trait CanBePrivate
{
    protected function privateRecord(): MorphOne
    {
        return $this->morphOne(RecordPrivate::class, 'privatable');
    }

    public function private(): bool
    {
        return $this->privateRecord()->exists();
    }

    public function setPrivate()
    {
        $this->privateRecord()->create();
    }

    public function setPublic()
    {
        $this->privateRecord()->delete();
    }

    public function canView(): bool
    {
        return $this->private() && $this->creator->id == Auth::user()->id;
    }
}
