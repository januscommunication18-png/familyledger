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
        Schema::create('goal_check_ins', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();

            // Check-in response
            $table->string('status', 20); // done, in_progress, skipped
            $table->text('note')->nullable();

            // For milestone goals - progress increment
            $table->integer('progress_added')->nullable();

            // For habit goals - was it done?
            $table->boolean('habit_completed')->nullable();

            // Kid-friendly star rating given
            $table->integer('star_rating')->nullable();

            // Parent encouragement message
            $table->text('parent_message')->nullable();

            // Check-in date (the date this check-in is for)
            $table->date('check_in_date');

            $table->timestamps();

            $table->index(['goal_id', 'check_in_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_check_ins');
    }
};
