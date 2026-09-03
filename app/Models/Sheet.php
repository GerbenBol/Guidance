<?php

namespace App\Models;

use App\Services\SheetService;
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
                'inventory', 'settings', 'extra_info',
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

    public function classes(): Collection
    {
        $classes = [];

        foreach ($this->info->classes as $class) {
            $classes[] = $class->id;
        }

        return PlayerClass::find($classes);
    }

    public function getAbilityScore(string $ability): int
    {
        $ability = match (strtolower($ability)) {
            default => $ability,
            'str' => 'Strength',
            'dex' => 'Dexterity',
            'con' => 'Constitution',
            'int' => 'Intelligence',
            'wis' => 'Wisdom',
            'cha' => 'Charisma'
        };

        foreach ($this->abilities as $abi) {
            if ($abi->ability == $ability) {
                return ($abi->score_override ?? $abi->score) + ($abi->misc_bonus ?? 0);
            }
        }

        return 0;
    }

    public array $classes { get => $this->info->classes; }

    public int $ch_lvl { get => collect($this->classes)->pluck('level')->sum(); }

    public Race $race { get => Race::find($this->info->race_id); }

    public object $race_options { get => $this->info->race_options; }

    public Background $background { get => Background::find($this->info->background_id); }

    public object $background_options { get => $this->info->background_options; }

    public object $inventory { get => $this->info->inventory; }

    public object $settings { get => $this->info->settings; }

    public object $einfo { get => $this->info->extra_info; }

    public array|object $abilities { get => $this->einfo->scores; }

    public bool $fixed_hp { get => $this->einfo->use_fixed_hp; }

    public int $hp { get => $hp_override ?? SheetService::getHitPoints($this); }
}
