<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

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
class Product extends WithMeta
{
    use HasTranslations;
}
