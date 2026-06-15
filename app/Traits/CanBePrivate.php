<?php

namespace App\Traits;

use App\Models\RecordPrivate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

trait CanBePrivate
{
    public function privateRecord(): MorphOne
    {
        return $this->morphOne(RecordPrivate::class, 'privatable');
    }

    public function isPrivate(): bool
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

    public function scopePrivate(Builder $builder): Builder
    {
        return $builder->whereHas('privateRecord');
    }

    public function scopePublic(Builder $builder): Builder
    {
        return $builder->whereDoesntHave('privateRecord');
    }

    public function scopePublicOrOwned(Builder $builder): Builder
    {
        return $builder->whereDoesntHave('privateRecord')->orWhere('user_id', Auth::user()->id);
    }

    public static function getAllRecords(string $scope): array
    {
        return self::$scope()->get()->pluck('name', 'id')->toArray();
    }

    public function canView(): bool
    {
        return $this->isPrivate() && $this->creator->id == Auth::user()->id;
    }
}
