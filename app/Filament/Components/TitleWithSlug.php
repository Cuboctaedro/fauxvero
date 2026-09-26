<?php

namespace App\Filament\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class TitleWithSlug
{
    public static function make(): Group
    {
        return Group::make()
            ->schema([
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation, $livewire) {
                        // Only slugify on create, and only from the primary (first) locale.
                        if ($operation !== 'create' || $livewire->activeLocale !== filament('spatie-translatable')->getDefaultLocales()[0]) {
                            return;
                        }
                        $set('slug', Str::slug($state ?? ''));
                    })
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }
}
