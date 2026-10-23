<?php

namespace App\Services;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ModifierService
{
    public static function validateModifier(?string $modifier): int
    {
        if (! in_array(null, self::splitModifier($modifier))) {
            return 2;
        }

        return 1;
    }

    public static function splitModifier(?string $modifier): array
    {
        $modifier = str_replace(' ', '', $modifier);
        $modifiers = [];

        foreach (preg_split('/([+\-])/', $modifier, flags: PREG_SPLIT_DELIM_CAPTURE) as $mod) {
            if (in_array($mod, ['+', '-', '*', '/'])) {
                $modifiers[] = [
                    'type' => 'addSub',
                    'do' => $mod,
                ];
            } elseif (in_array($mod, ['PB', 'LVL', 'STR', 'DEX', 'CON', 'INT', 'WIS', 'CHA'])) {
                $modifiers[] = [
                    'type' => 'character',
                    'do' => $mod,
                ];
            } elseif (str_contains($mod, 'd')) {
                $split = explode('d', $mod);
                if (! is_numeric($split[0]) || ! is_numeric($split[1])) {
                    $modifiers[] = null;
                } else {
                    $modifiers[] = [
                        'type' => 'dice',
                        'amount' => $split[0],
                        'dice' => 'd'.$split[1],
                    ];
                }
            } elseif (is_numeric($mod)) {
                $modifiers[] = [
                    'type' => 'straight',
                    'modifier' => $mod,
                ];
            } else {
                $modifiers[] = null;
            }
        }

        return $modifiers;
    }

    public static function mechanicToText(array $mechanic, ?string $choice = null): string
    {
        $modifier = $mechanic['grant'] != 'mod' ? '' :
            ($mechanic['modifier'] ?? $mechanic['modifier1'] ?? $mechanic['modifier2']).
            (in_array($mechanic['on'], ['walk', 'climb', 'fly', 'swim', 'burrow']) ? ' ft.' : '');

        try {
            return match ($mechanic['grant']) {
                default => '',
                'mod' => 'Modifier on: ',
                'adv' => 'Advantage on: ',
                'dadv' => 'Disadvantage on: ',
                'vul' => 'Vulnerability against: ',
                'res' => 'Resistance against: ',
                'imm' => 'Immunity against: ',
                'prof' => 'Proficiency on: ',
                'sense' => 'Sense: ',
                'lang' => 'Language: ',
                // 'ign' => 'Ignore: ',
                'wm' => 'Weapon Mastery: ',
            }.
            FormService::getMechanicName($mechanic['choice'] ? explode('-', $choice) : $mechanic['on']).
            ($mechanic['grant'] == 'mod' ? ' ('.$modifier.')' : '');
        } catch (\Exception $e) {
            Log::alert('Something went wrong while opening '.$mechanic['grant'].'. Error message: '.$e->getMessage());
            Notification::make('featureOptionNotChosen')
                ->title('Something went wrong')
                ->body('It might be a feature with an unchosen mechanic.')
                ->danger()
                ->send();
        }

        return '';
    }
}
