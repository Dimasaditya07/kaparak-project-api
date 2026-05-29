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
        Schema::create('reservations', function (Blueprint $table) {

            $table->id();

            // RELATION USER
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // RESERVATION CODE
            $table->string('code')->unique();

            // TOTAL PAYMENT
            $table->decimal('total', 12, 2);

            // PICKUP & RETURN DATE
            $table->date('pickup_date');

            $table->date('return_date');

            // RESERVATION STATUS
            $table->enum('status', [
                'pending',
                'confirmed',
                'picked_up',
                'returned',
                'cancelled'
            ])->default('pending');

            // PAYMENT STATUS
            $table->enum('payment_status', [
                'unpaid',
                'paid',
                'failed',
                'refunded'
            ])->default('unpaid');

            // CUSTOMER NOTE
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};