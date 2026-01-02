<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks login attempts for security auditing and rate limiting.
     */
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('auth_method'); // email, otp, google, apple, facebook
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index('email');
            $table->index('ip_address');
            $table->index('created_at');
            $table->index(['email', 'successful']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('login_attempts');
        Schema::enableForeignKeyConstraints();
    }
};
