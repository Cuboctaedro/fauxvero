<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('alt')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->morphs('model');
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });

        foreach (['products', 'pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('featured_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('featured_asset_id');
            });
        }

        Schema::dropIfExists('asset_gallery');
        Schema::dropIfExists('assets');
    }
};
