<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;

trait HasImages
{
    public const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ];

    protected function addFeaturedImageCollection(): void
    {
        $this->addMediaCollection(self::FEATURED)
            ->singleFile()
            ->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    protected function addGalleryCollection(): void
    {
        $this->addMediaCollection(self::GALLERY)
            ->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    protected function addImageConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 320, 320)
            ->performOnCollections(self::FEATURED, self::GALLERY)
            ->nonQueued();

        $this->addMediaConversion('web')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82)
            ->performOnCollections(self::FEATURED, self::GALLERY)
            ->queued();
    }
}
