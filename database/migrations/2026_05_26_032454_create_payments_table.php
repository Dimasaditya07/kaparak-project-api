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

            // RELATION
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->onDelete('cascade');

            // RETURN INFO
            $table->timestamp('returned_at')
                ->nullable();

            // DENDA TELAT
            $table->decimal('late_fee', 12, 2)
                ->default(0);

            // DENDA KERUSAKAN
            $table->decimal('damage_fee', 12, 2)
                ->default(0);

            // CATATAN ADMIN
            $table->text('note')
                ->nullable();

            // pending | returned | damaged
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