<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

class Image extends Component
{
    protected string $view = 'filament.components.image';

    public static function make()
    {
        return Group::make()->schema([
            FileUpload::make('url')
                ->label('Image')
                ->image()
                ->required(),
            TextInput::make('alt')
                ->label('Alt text')
                ->required(),
        ]);
    }
}