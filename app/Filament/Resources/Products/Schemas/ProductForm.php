<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use App\Filament\Components\Metadata;
use App\Filament\Components\TitleWithSlug;
use App\Filament\Components\Status;
use App\Filament\Components\Editor;
use App\Filament\Components\Images;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Group::make()->schema([
                            TitleWithSlug::make(),
                            Editor::make('description'),
                            Images::gallery(),
                            Section::make('Additional Information')
                                ->schema([
                                    Textarea::make('dimensions'),
                                    TextInput::make('weight'),
                                    TextInput::make('color'),
                                    TextInput::make('material'),
                                ]),
                            Metadata::make(),

                        ])->columnSpan(3),

                        Group::make()->schema([
                            Status::make(),
                            Images::featured(),

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
