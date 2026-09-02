<?php

namespace App\Filament\Components;

use App\Models\WithMeta;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Images
{
    public static function featured(): Section
    {
        return Section::make('Featured Image')
            ->schema([
                SpatieMediaLibraryFileUpload::make(WithMeta::FEATURED)
                    ->label('Image')
                    ->collection(WithMeta::FEATURED)
                    ->image()
                    ->imageEditor()
                    ->conversion('thumb')
                    ->maxSize(5120),

                TextInput::make('featured_alt')
                    ->label('Alt text')
                    ->hiddenOn('create')
                    ->dehydrated(false)
                    ->afterStateHydrated(function (TextInput $component, ?Model $record, $livewire): void {
                        $media = $record?->getFirstMedia(WithMeta::FEATURED);

                        $component->state(
                            $media?->getTranslation('alt', $livewire->activeLocale, false) ?? ''
                        );
                    })
                    ->saveRelationshipsUsing(function (TextInput $component, ?Model $record, $livewire): void {
                        $media = $record?->getFirstMedia(WithMeta::FEATURED);

                        $media?->setTranslation('alt', $livewire->activeLocale, (string) $component->getState())
                            ->save();
                    }),
            ]);
    }

    public static function gallery(): Section
    {
        return Section::make('Gallery')
            ->schema([
                SpatieMediaLibraryFileUpload::make(WithMeta::GALLERY)
                    ->label('Images')
                    ->collection(WithMeta::GALLERY)
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->image()
                    ->imageEditor()
                    ->conversion('thumb')
                    ->panelLayout('grid')
                    ->maxSize(5120),

                self::galleryAltText(),
            ]);
    }

    /**
     * Alt text for the gallery images.
     *
     * Deliberately not bound with ->relationship(): a relationship repeater deletes
     * related records missing from its state, which would wipe images uploaded by the
     * file upload component in the same request. State is hydrated and saved by hand
     * instead, against the locale the page's locale switcher is currently on.
     */
    protected static function galleryAltText(): Repeater
    {
        return Repeater::make('gallery_alt')
            ->label('Alt text')
            ->hiddenOn('create')
            ->dehydrated(false)
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->columns(2)
            ->schema([
                Hidden::make('preview'),
                // Read through $get, never by injecting $state: a Placeholder resolves
                // its own state from its content, so $state injection recurses forever.
                Placeholder::make('thumbnail')
                    ->hiddenLabel()
                    ->content(fn (Get $get): HtmlString => new HtmlString(
                        '<img src="'.e($get('preview')).'" class="h-20 w-20 rounded object-cover">'
                    )),
                TextInput::make('alt')
                    ->hiddenLabel(),
            ])
            ->afterStateHydrated(function (Repeater $component, ?Model $record, $livewire): void {
                $component->state(
                    $record
                        ?->getMedia(WithMeta::GALLERY)
                        ->mapWithKeys(fn (Media $media): array => [
                            $media->uuid => [
                                'preview' => $media->hasGeneratedConversion('thumb')
                                    ? $media->getUrl('thumb')
                                    : $media->getUrl(),
                                'alt' => $media->getTranslation('alt', $livewire->activeLocale, false),
                            ],
                        ])
                        ->all() ?? []
                );

                $component->hydrateItems();
            })
            ->saveRelationshipsUsing(function (Repeater $component, ?Model $record, $livewire): void {
                $state = $component->getState();

                $record?->getMedia(WithMeta::GALLERY)
                    ->each(function (Media $media) use ($state, $livewire): void {
                        if (! array_key_exists($media->uuid, $state)) {
                            return;
                        }

                        $media->setTranslation('alt', $livewire->activeLocale, (string) ($state[$media->uuid]['alt'] ?? ''))
                            ->save();
                    });
            });
    }
}
