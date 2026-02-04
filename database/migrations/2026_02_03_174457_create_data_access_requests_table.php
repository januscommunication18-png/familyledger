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
        Schema::create('data_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('backoffice_admins')->onDelete('cascade');
            $table->string('tenant_id', 36);
            $table->string('token', 64)->unique();
            $table->string('reason')->nullable(); // Why admin needs access
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->timestamp('expires_at'); // Request expires if not acted upon
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->timestamp('access_expires_at')->nullable(); // When approved access expires
            $table->string('approved_by_email')->nullable(); // Email of user who approved
            $table->text('denial_reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_access_requests');
    }
};
