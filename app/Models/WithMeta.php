<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithMeta extends Model
{
    public const FILLABLE = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_robots',
    ];

    public const TRANSLATABLE = [
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];
}
