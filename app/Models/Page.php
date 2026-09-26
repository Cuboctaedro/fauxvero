<?php

namespace App\Models;

use App\Models\Concerns\HasImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    ...WithMeta::FILLABLE,
    'name',
    'description',
    'content',
    'template',
    'status',
    'slug',
    'featured_asset_id',
])]
#[Translatable([
    ...WithMeta::TRANSLATABLE,
    'name',
    'description',
    'content',
])]
class Page extends WithMeta
{
    use HasImages;
    use HasTranslations;

    /**
     * Pages visible on the storefront.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
