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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('name',50);

            $table->string('email',50)
                ->unique();

            $table->timestamp('email_verified_at')
                ->nullable();

            $table->string('password');

            // PROFILE
            $table->string('phone', 20)
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('image_ktp')
                ->nullable();

            // ROLE
            $table->enum('role', [
                'admin',
                'customer'
            ])->default('customer');

            // LARAVEL
            $table->rememberToken();

            $table->timestamps();
        });

        // PASSWORD RESET
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')
                ->primary();

            $table->string('token');

            $table->timestamp('created_at')
                ->nullable();
        });

        // SESSION
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')
                ->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->index();

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->longText('payload');

            $table->integer('last_activity')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('sessions');
    }
};