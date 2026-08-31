<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Sheet extends Model
{
    protected $table = 'sheets';

    protected $fillable = [
        'character_id',
        'info',
    ];

    protected $casts = [
        'info' => 'array',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function isUpToDate(): bool
    {
        return $this->updated_at >= $this->character->updated_at;
    }

    public function generate()
    {
        $chara = $this->character;

        try {
            Notification::make('generateSuccess')
                ->title($chara->name.'\' sheet generation successfull')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make('generateFailed')
                ->title($chara->name.'\' sheet generation failed')
                ->danger()
                ->send();
            Log::info('Sheet generation failed for character \''.$chara->name.'\'. Error: '.$e->getCode().' Message: '.$e->getMessage());
        }
    }
}
