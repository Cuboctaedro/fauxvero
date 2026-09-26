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

    /**
     * Responsive widths offered in srcset, mapped to the conversion that holds each.
     */
    public const WIDTHS = [
        400 => 'w400',
        800 => 'w800',
        1200 => 'w1200',
        1600 => 'web',
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

        foreach (self::WIDTHS as $width => $conversion) {
            $this->addMediaConversion($conversion)
                ->fit(Fit::Max, $width, $width)
                ->format('webp')
                ->quality(82)
                ->queued();
        }
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

    /**
     * Only lists conversions that have been generated, so it is empty until the
     * queued conversions have run and the image falls back to its src.
     */
    public function srcset(): string
    {
        $media = $this->getFirstMedia(self::COLLECTION);

        if (! $media) {
            return '';
        }

        return collect(self::WIDTHS)
            ->filter(fn (string $conversion): bool => $media->hasGeneratedConversion($conversion))
            ->map(fn (string $conversion, int $width): string => $media->getUrl($conversion).' '.$width.'w')
            ->implode(', ');
    }
}
