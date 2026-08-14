<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    ...WithMeta::FILLABLE,
    'name',
    'content',
    'status',
    'slug',
])]
#[Translatable([
    ...WithMeta::TRANSLATABLE,
    'name',
    'content',
])]
class Page extends WithMeta
{
    use HasTranslations;
}
