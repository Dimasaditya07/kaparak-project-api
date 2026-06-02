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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->onDelete('cascade');

            $table->timestamp('returned_at')
                ->nullable();

            $table->decimal('late_fee', 12, 2)
                ->default(0);

            $table->decimal('damage_fee', 12, 2)
                ->default(0);

            $table->text('note')
                ->nullable();

            $table->enum('status', [
                'pending',
                'returned',
                'damaged'
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
