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
        Schema::create('task_occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('todo_item_id')->constrained()->cascadeOnDelete();

            // Occurrence specific data
            $table->integer('occurrence_number');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();

            // Status tracking
            $table->string('status')->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('snoozed_until')->nullable();
            $table->string('skipped_reason')->nullable();

            // Per-occurrence assignment (for rotation)
            $table->foreignId('assigned_to')->nullable()->constrained('family_members')->nullOnDelete();

            // Notes specific to this occurrence
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'scheduled_date', 'status']);
            $table->index(['todo_item_id', 'occurrence_number']);
            $table->unique(['todo_item_id', 'occurrence_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_occurrences');
    }
};
