<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * An uploaded image in the shared media library. Products and pages reference
 * assets rather than owning files, so one upload can be used in many places.
 */
#[Fillable([
    'name',
    'alt',
])]
#[Translatable([
    'alt',
])]
class Asset extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

    public const COLLECTION = 'image';

    public const MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(self::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 320, 320)
            ->nonQueued();

        $this->addMediaConversion('web')
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82)
            ->queued();
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function featuredOnProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'featured_asset_id');
    }

    public function featuredOnPages(): HasMany
    {
        return $this->hasMany(Page::class, 'featured_asset_id');
    }

    /**
     * Falls back to the original file while a queued conversion hasn't run yet.
     */
    public function url(string $conversion = ''): string
    {
        $media = $this->getFirstMedia(self::COLLECTION);

        if (! $media) {
            return '';
        }

        return $conversion !== '' && $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }
}
