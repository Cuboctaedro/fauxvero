<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'asset_id',
    'order_column',
])]
class GalleryItem extends Model
{
    protected $table = 'asset_gallery';

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
