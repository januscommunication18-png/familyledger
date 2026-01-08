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
        // Backoffice Admins table
        Schema::create('backoffice_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('security_code')->nullable(); // 6-digit OTP for login
            $table->timestamp('security_code_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->timestamps();
        });

        // Client View Access Codes - for secure viewing of client data
        Schema::create('backoffice_view_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('tenant_id'); // String because tenants.id is string
            $table->string('code'); // Hashed code
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('backoffice_admins')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // Admin Activity Logs
        Schema::create('backoffice_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action'); // login, logout, view_client, etc.
            $table->string('tenant_id')->nullable(); // String because tenants.id is string
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('backoffice_admins')->onDelete('cascade');
        });

        // Password Reset Tokens for Backoffice Admins
        Schema::create('backoffice_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backoffice_password_reset_tokens');
        Schema::dropIfExists('backoffice_activity_logs');
        Schema::dropIfExists('backoffice_view_codes');
        Schema::dropIfExists('backoffice_admins');
    }
};
