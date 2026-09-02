<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Sheet extends Model
{
    protected $table = 'sheets';

    protected $fillable = [
        'character_id',
        'info',
    ];

    protected $casts = [
        'info' => 'object',
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
            $info = [];
            $fields = [
                'classes', 'race_id', 'background_id',
                'race_options', 'background_options',
                'inventory', 'settings', 'extra_info'
            ];

            foreach ($fields as $field) {
                $info[$field] = $chara->$field;
            }
            $this->update(['info' => $info]);
            
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

    public function classes(): Collection {
        $classes = [];

        foreach ($this->info->classes as $class) {
            $classes[] = $class->id;
        }
        return PlayerClass::find($classes);
    }

    public function race(): Race {
        return Race::find($this->info->race_id);
    }

    public function race_options(): object {
        return $this->info->race_options;
    }

    public object $race_options { get => $this->info->race_options; }
    public object $background_options { get => $this->info->background_options; }
    public Background $background { get => Background::find($this->info->background_id); }
}
