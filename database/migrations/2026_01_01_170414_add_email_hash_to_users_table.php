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
        // First, drop the unique index on email
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        // Then add email_hash and modify columns
        Schema::table('users', function (Blueprint $table) {
            // Add email_hash for lookups (SHA-256 = 64 chars)
            $table->string('email_hash', 64)->nullable()->after('email')->unique();

            // Change columns to TEXT to accommodate encrypted data
            $table->text('name')->nullable()->change();
            $table->text('first_name')->nullable()->change();
            $table->text('last_name')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('backup_email')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('country_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_hash');

            $table->string('name')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('backup_email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('country_code')->nullable()->change();
        });

        // Restore unique index on email
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
