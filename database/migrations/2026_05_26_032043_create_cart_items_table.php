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
        Schema::create('cart_items', function (Blueprint $table) {

            $table->id();

            // RELATION CART
            $table->foreignId('cart_id')
                ->constrained('carts')
                ->onDelete('cascade');

            // RELATION PRODUCT
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // QTY RENT
            $table->integer('quantity')->default(1);

            // RENT DATE
            $table->date('start_date');

            $table->date('end_date');

            // TOTAL DAY
            $table->integer('duration');

            // PRICE / DAY
            $table->decimal('price', 12, 2);

            // TOTAL PRICE
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};