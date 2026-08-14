<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\FusedGroup;
use App\Filament\Components\Metadata;
use App\Filament\Components\TitleWithSlug;
use Filament\Forms\Components\ToggleButtons;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    // ->columns(4)
                    ->schema([
                        Group::make()->schema([
                            TitleWithSlug::getComponent(),
                            RichEditor::make('description')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'link'],
                                    ['h3', 'h4'],
                                    ['orderedList', 'bulletList'],
                                ]),
                            Section::make('Additional Information')
                                ->schema([
                                    Textarea::make('dimensions'),
                                    TextInput::make('weight'),
                                    TextInput::make('color'),
                                    TextInput::make('material'),
                                ]),
                            Metadata::getComponent(),

                        ])->columnSpan(3),

                        Group::make()->schema([
                            ToggleButtons::make('status')
                                ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                                ->default('active')
                                ->inline()
                                ->required(),
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('€'),
                            Toggle::make('in_stock')
                                ->required(),

                        ])->columnSpan(1)
                    ])
            ])->columns(1);
    }
}
