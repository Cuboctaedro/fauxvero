<?php

namespace App\Helpers;

use Illuminate\Database\Schema\Blueprint;

class MetadataMigration
{
    public static function addMetadataColumns(Blueprint $table): void
    {
        $table->json('meta_title')->nullable();
        $table->json('meta_description')->nullable();
        $table->json('meta_keywords')->nullable(); 
        $table->boolean('meta_robots')->default(true); 
    }

    public static function dropMetadataColumns(Blueprint $table): void
    {
        $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'meta_robots']);
    }
}
