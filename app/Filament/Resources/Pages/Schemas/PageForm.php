<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Components\ContentBlocks;
use App\Filament\Components\Editor;
use App\Filament\Components\Images;
use App\Filament\Components\Metadata;
use App\Filament\Components\Status;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Group::make()->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Editor::make('description')
                                ->label('Content')
                                ->hidden(fn (Get $get): bool => $get('template') !== 'text'),
                            ContentBlocks::make('content')
                                ->hidden(fn (Get $get): bool => $get('template') !== 'blocks'),
                            Metadata::make(),

                        ])->columnSpan(3),

                        Group::make()->schema([
                            Status::make(),
                            Images::featured(),
                            Select::make('template')
                                ->options([
                                    'text' => 'Text',
                                    'blocks' => 'Blocks',
                                ])
                                ->default('text')
                                ->live(onBlur: true),

                        ])->columnSpan(1),
                    ]),
            ])->columns(1);
    }
}
