<?php

namespace App\Models\Concerns;

use App\Models\Asset;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Images come from the shared media library (Asset): a model points at a featured
 * asset and, optionally, an ordered gallery of assets.
 */
trait HasImages
{
    public function featuredAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'featured_asset_id');
    }

    public function galleryItems(): MorphMany
    {
        return $this->morphMany(GalleryItem::class, 'model')->orderBy('order_column');
    }

    public function galleryAssets(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'model', 'asset_gallery')
            ->withPivot('order_column')
            ->withTimestamps()
            ->orderByPivot('order_column');
    }

    protected function featuredImage(): Attribute
    {
        return Attribute::get(fn (): string => $this->featuredAsset?->url('web') ?? '');
    }
}
