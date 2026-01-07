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
        // Main schedule table
        Schema::create('coparenting_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('created_by');
            $table->string('name')->nullable();
            $table->string('template_type')->nullable(); // every_other_week, 2_2_3, 2_2_5_5, 3_4_4_3, every_weekend, every_other_weekend, same_weekends, all_to_one, custom
            $table->date('begins_at');
            $table->date('ends_at')->nullable();
            $table->boolean('has_end_date')->default(false);
            $table->integer('repeat_every')->nullable(); // for custom: number
            $table->string('repeat_unit')->nullable(); // for custom: days, weeks
            $table->string('primary_parent')->default('mother'); // mother, father
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Additional settings
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Schedule time blocks - individual time periods assigned to parents
        Schema::create('coparenting_schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('parent_role'); // mother, father
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // weekly, biweekly, monthly
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('coparenting_schedules')->onDelete('cascade');
        });

        // Link schedules to specific children
        Schema::create('coparenting_schedule_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('family_member_id');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('coparenting_schedules')->onDelete('cascade');
            $table->foreign('family_member_id')->references('id')->on('family_members')->onDelete('cascade');
            $table->unique(['schedule_id', 'family_member_id'], 'schedule_child_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparenting_schedule_children');
        Schema::dropIfExists('coparenting_schedule_blocks');
        Schema::dropIfExists('coparenting_schedules');
    }
};
