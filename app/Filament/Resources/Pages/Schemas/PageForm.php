<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use App\Filament\Components\Metadata;
use App\Filament\Components\TitleWithSlug;
use App\Filament\Components\Status;
use App\Filament\Components\Editor;
use App\Filament\Components\ContentBlocks;
use Filament\Schemas\Components\Utilities\Get;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Group::make()->schema([
                            TitleWithSlug::make(),
                            Editor::make('description')
                                ->label('Content')
                                ->hidden(fn (Get $get): bool => $get('template') !== 'text'),
                            ContentBlocks::make('content')
                                ->hidden(fn (Get $get): bool => $get('template') !== 'blocks'),
                            Metadata::make(),

                        ])->columnSpan(3),

                        Group::make()->schema([
                            Status::make(),
                            Select::make('template')
                                ->options([
                                    'text' => 'Text',
                                    'blocks' => 'Blocks',
                                ])
                                ->default('text')
                                ->live(onBlur: true)
                        ])->columnSpan(1)
                    ])
            ])->columns(1);
    }
}
