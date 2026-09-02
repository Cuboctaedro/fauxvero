<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable([
    'alt',
])]
class Media extends BaseMedia
{
    use HasTranslations;
}
