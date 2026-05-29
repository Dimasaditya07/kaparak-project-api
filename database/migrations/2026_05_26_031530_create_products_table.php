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
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            // RELATION CATEGORY
            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');

            // PRODUCT INFO
            $table->string('name', 100);

            $table->string('slug')->unique();

            $table->string('code', 50)->unique();

            $table->text('description')->nullable();

            // STOCK
            $table->integer('stock')->default(0);

            // PRICE
            $table->decimal('price', 12, 2);

            // IMAGE
            $table->string('image')->nullable();

            // STATUS
            $table->enum('status', [
                'available',
                'unavailable'
            ])->default('available');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};