<?php

namespace App\Models;

use App\Models\Concerns\HasImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    ...WithMeta::FILLABLE,
    'name',
    'type',
    'description',
    'dimensions',
    'weight',
    'color',
    'material',
    'status',
    'price',
    'in_stock',
    'slug',
    'featured_asset_id',
])]
#[Translatable([
    ...WithMeta::TRANSLATABLE,
    'name',
    'type',
    'description',
    'dimensions',
    'weight',
    'color',
    'material',
])]
class Product extends WithMeta
{
    use HasFactory;
    use HasImages;
    use HasTranslations;
}
