<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'galleryItems',
                'featuredOnProducts',
                'featuredOnPages',
            ]))
            ->columns([
                Stack::make([
                    SpatieMediaLibraryImageColumn::make('image')
                        ->collection(Asset::COLLECTION)
                        ->conversion('thumb')
                        ->imageHeight(160)
                        ->extraImgAttributes(['style' => 'width:100%;object-fit:contain']),
                    TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('medium'),
                    TextColumn::make('usage')
                        ->state(fn (Asset $record): string => self::usageLabel($record))
                        ->color('gray')
                        ->size('sm'),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 3,
                'xl' => 5,
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription(fn (Asset $record): string => self::deleteWarning($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function usageCount(Asset $record): int
    {
        return $record->gallery_items_count
            + $record->featured_on_products_count
            + $record->featured_on_pages_count;
    }

    protected static function usageLabel(Asset $record): string
    {
        $count = self::usageCount($record);

        return $count === 0 ? 'Unused' : 'Used in '.$count.' '.str('place')->plural($count);
    }

    protected static function deleteWarning(Asset $record): string
    {
        $count = self::usageCount($record);

        return $count === 0
            ? 'This image is not used anywhere.'
            : 'This image is used in '.$count.' '.str('place')->plural($count).' and will be removed from all of them.';
    }
}
