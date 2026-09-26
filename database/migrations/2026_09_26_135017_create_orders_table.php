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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country', 2)->default('GR');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('locale', 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
