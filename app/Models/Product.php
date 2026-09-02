<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Concerns\HasImages;

#[Fillable([
    ...WithMeta::FILLABLE,
    'name',
    'description',
    'dimensions',
    'weight',
    'color',
    'material',
    'status',
    'price',
    'in_stock',
    'slug',
])]
#[Translatable([
    ...WithMeta::TRANSLATABLE,
    'name',
    'description',
    'dimensions',
    'weight',
    'color',
    'material',
])]
class Product extends WithMeta implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;
    use HasImages;

    public function registerMediaCollections(): void
    {
        $this->addFeaturedImageCollection();
        $this->addGalleryCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addImageConversions();
    }
}
