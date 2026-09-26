<?php

namespace App\Models;

use App\Models\Concerns\HasImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    ...WithMeta::FILLABLE,
    'name',
    'content',
    'status',
    'slug',
    'featured_asset_id',
])]
#[Translatable([
    ...WithMeta::TRANSLATABLE,
    'name',
    'content',
])]
class Page extends WithMeta
{
    use HasImages;
    use HasTranslations;
}
