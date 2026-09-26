<?php

namespace App\Filament\Components;

use App\Filament\Resources\Assets\Schemas\AssetForm;
use App\Models\Asset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Image pickers backed by the shared media library: records reference Assets, so
 * an image uploaded once can be reused anywhere, and its alt text is kept once.
 */
class Images
{
    public static function featured(): Section
    {
        return Section::make('Featured Image')
            ->schema([
                self::picker(Select::make('featured_asset_id'), 'featuredAsset')
                    ->hiddenLabel(),
            ]);
    }

    public static function gallery(): Section
    {
        return Section::make('Gallery')
            ->schema([
                Repeater::make('galleryItems')
                    ->hiddenLabel()
                    ->relationship()
                    ->orderColumn('order_column')
                    ->reorderable()
                    ->defaultItems(0)
                    ->addActionLabel('Add image')
                    ->grid(3)
                    ->simple(
                        self::picker(Select::make('asset_id'), 'asset')
                            ->required()
                            ->distinct(),
                    ),
            ]);
    }

    /**
     * A library image picker for JSON fields such as content blocks, which store the
     * asset id without an Eloquent relationship. Alt text is edited on the asset itself.
     */
    public static function asset(string $name = 'asset_id'): Select
    {
        return Select::make($name)
            ->label('Image')
            ->options(fn (): array => self::options(Asset::query()->latest()->limit(50)))
            ->getSearchResultsUsing(fn (string $search): array => self::options(
                Asset::query()->where('name', 'like', "%{$search}%")->latest()->limit(50),
            ))
            ->getOptionLabelUsing(fn ($value): ?string => ($asset = Asset::with('media')->find($value))
                ? self::optionLabel($asset)
                : null)
            ->allowHtml()
            ->searchable()
            ->createOptionForm(AssetForm::fields())
            ->createOptionUsing(fn (array $data, Schema $schema, $livewire): int => self::createAsset($data, $schema, $livewire));
    }

    /**
     * A searchable select of library images that can also upload a new image, or
     * edit the chosen one's name and alt text, without leaving the form. Alt text is
     * read and written in the locale the page's locale switcher is on.
     */
    protected static function picker(Select $select, string $relationship): Select
    {
        return $select
            ->relationship(
                $relationship,
                'name',
                modifyQueryUsing: fn (Builder $query) => $query->with('media')->latest(),
            )
            ->getOptionLabelFromRecordUsing(fn (Asset $record): string => self::optionLabel($record))
            ->allowHtml()
            ->searchable()
            ->preload()
            ->createOptionForm(AssetForm::fields())
            ->createOptionUsing(fn (array $data, Schema $schema, $livewire): int => self::createAsset($data, $schema, $livewire))
            ->editOptionForm([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('alt')
                    ->label('Alt text')
                    ->helperText('Shared by every place this image is used.'),
            ])
            ->fillEditOptionActionFormUsing(fn (Select $component, $livewire): ?array => ($asset = $component->getSelectedRecord())
                ? [
                    'name' => $asset->name,
                    'alt' => $asset->getTranslation('alt', self::locale($livewire), false),
                ]
                : null)
            ->updateOptionUsing(function (array $data, Schema $schema, $livewire): void {
                $asset = $schema->getRecord();

                $asset?->fill(['name' => $data['name']])
                    ->setTranslation('alt', self::locale($livewire), (string) ($data['alt'] ?? ''))
                    ->save();
            });
    }

    protected static function createAsset(array $data, Schema $schema, $livewire): int
    {
        $asset = new Asset(['name' => $data['name']]);
        $asset->setTranslation('alt', self::locale($livewire), (string) ($data['alt'] ?? ''));
        $asset->save();

        // Stores the uploaded file on the new asset.
        $schema->model($asset)->saveRelationships();

        return $asset->getKey();
    }

    protected static function options(Builder $query): array
    {
        return $query->with('media')->get()
            ->mapWithKeys(fn (Asset $asset): array => [$asset->getKey() => self::optionLabel($asset)])
            ->all();
    }

    protected static function optionLabel(Asset $asset): string
    {
        // Inline styles: the panel has no custom theme, so arbitrary utility classes
        // aren't guaranteed to exist in Filament's compiled CSS.
        return '<span style="display:flex;align-items:center;gap:.5rem">'
            .'<img src="'.e($asset->url('thumb')).'" alt="" style="width:2.5rem;height:2.5rem;flex-shrink:0;border-radius:.25rem;object-fit:cover">'
            .'<span>'.e($asset->name).'</span>'
            .'</span>';
    }

    protected static function locale($livewire): string
    {
        return $livewire->activeLocale ?? app()->getLocale();
    }
}
