<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    /**
     * Only the status is editable; items and customer details are a record of
     * what was ordered.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Group::make()->schema([
                            Section::make('Items')
                                ->schema([
                                    RepeatableEntry::make('items')
                                        ->hiddenLabel()
                                        ->table([
                                            TableColumn::make('Product'),
                                            TableColumn::make('Unit price'),
                                            TableColumn::make('Quantity'),
                                            TableColumn::make('Total'),
                                        ])
                                        ->schema([
                                            TextEntry::make('product_name'),
                                            TextEntry::make('unit_price')->money('EUR'),
                                            TextEntry::make('quantity'),
                                            TextEntry::make('line_total')->money('EUR'),
                                        ]),
                                    TextEntry::make('total')
                                        ->money('EUR')
                                        ->weight('bold'),
                                ]),
                            Section::make('Customer')
                                ->schema([
                                    TextEntry::make('name'),
                                    TextEntry::make('email')->copyable(),
                                    TextEntry::make('phone')->copyable(),
                                    TextEntry::make('address'),
                                    TextEntry::make('city'),
                                    TextEntry::make('postal_code'),
                                    TextEntry::make('country'),
                                    TextEntry::make('notes')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ])->columnSpan(3),

                        Group::make()->schema([
                            Select::make('status')
                                ->options(OrderStatus::class)
                                ->required(),
                            TextEntry::make('number'),
                            TextEntry::make('created_at')
                                ->label('Placed at')
                                ->dateTime(),
                        ])->columnSpan(1),
                    ]),
            ])->columns(1);
    }
}
