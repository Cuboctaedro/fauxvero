<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Forms\Components\ToggleButtons;

class Status extends Component
{
    protected string $view = 'filament.components.status';

    public static function make()
    {
        return ToggleButtons::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->inline()
                    ->required();
    }
}