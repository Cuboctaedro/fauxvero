<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Placed at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
