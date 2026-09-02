<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\MetadataMigration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            MetadataMigration::addMetadataColumns($table);
            $table->json('description')->nullable(true);
            $table->enum('template', ['text', 'blocks'])->default('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down($table): void
    {
        Schema::table('pages', function (Blueprint $table) {
            MetadataMigration::dropMetadataColumns($table);
            $table->dropColumn(['description', 'template']);
        });
    }
};
