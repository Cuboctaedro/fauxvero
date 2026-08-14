<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('description')->nullable(true)->change();
            $table->decimal('price', 8, 2)->nullable(true)->change();
            $table->json('dimensions')->nullable(true)->change();
            $table->json('weight')->nullable(true)->change();
            $table->json('color')->nullable(true)->change();
            $table->json('material')->nullable(true)->change();
            $table->text('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
