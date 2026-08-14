<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;

class TitleWithSlug extends Component
{
    protected string $view = 'filament.components.title-with-slug';

    public static function getComponent()
    {
        return Group::make()
            ->schema([
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, $livewire) {
                        if ($livewire->activeLocale !== 'en') {
                            return;
                        }
                        $set('slug', Str::slug($state));
                    })
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}