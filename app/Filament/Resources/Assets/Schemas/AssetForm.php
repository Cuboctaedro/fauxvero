<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields())
            ->columns(1);
    }

    /**
     * Shared with the image pickers, which create assets inline.
     */
    public static function fields(): array
    {
        return [
            SpatieMediaLibraryFileUpload::make('file')
                ->label('Image')
                ->collection(Asset::COLLECTION)
                ->image()
                ->imageEditor()
                ->conversion('thumb')
                ->maxSize(5120)
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                    if (filled($get('name'))) {
                        return;
                    }

                    $upload = collect($state)->first(fn ($file) => $file instanceof TemporaryUploadedFile);

                    if ($upload) {
                        $set('name', pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME));
                    }
                }),

            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('alt')
                ->label('Alt text'),
        ];
    }
}
