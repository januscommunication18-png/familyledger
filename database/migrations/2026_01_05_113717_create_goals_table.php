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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color')->default('violet');
            $table->string('icon')->default('target');

            // Target tracking
            $table->enum('target_type', ['none', 'count', 'amount', 'date'])->default('none');
            $table->decimal('target_value', 12, 2)->nullable(); // For count or amount
            $table->date('target_date')->nullable(); // For date type
            $table->decimal('current_progress', 12, 2)->default(0);

            // Status management
            $table->enum('status', ['active', 'paused', 'completed', 'archived'])->default('active');

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
