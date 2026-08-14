<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

class Metadata extends Component
{
    protected string $view = 'filament.components.metadata';

    public static function make()
    {
        return Section::make('Metadata')
            ->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(60),
                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->maxLength(160),
                Textarea::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->maxLength(255),
                Toggle::make('meta_robots')
                    ->label('Meta Robots')
                    ->helperText('Whether to allow search engines to index this product.'),
            ]);

    }
}