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
        // Activities table for co-parenting events
        Schema::create('coparenting_activities', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('created_by');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_all_day')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_frequency')->nullable(); // day, week, month
            $table->string('recurrence_end_type')->nullable(); // never, after, on
            $table->integer('recurrence_end_after')->nullable(); // number of occurrences
            $table->date('recurrence_end_on')->nullable(); // specific end date
            $table->string('reminder_type')->default('default'); // default, custom, none
            $table->integer('reminder_minutes')->default(60); // minutes before
            $table->string('color')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Link activities to children
        Schema::create('coparenting_activity_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('family_member_id');
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('coparenting_activities')->onDelete('cascade');
            $table->foreign('family_member_id')->references('id')->on('family_members')->onDelete('cascade');
            $table->unique(['activity_id', 'family_member_id'], 'activity_child_unique');
        });

        // Actual time check-ins for tracking real custody time
        Schema::create('coparenting_actual_time', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('checked_by'); // user who logged this
            $table->unsignedBigInteger('family_member_id'); // which child
            $table->date('date');
            $table->string('parent_role'); // mother, father
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->boolean('is_full_day')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('checked_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('family_member_id')->references('id')->on('family_members')->onDelete('cascade');
            $table->unique(['tenant_id', 'family_member_id', 'date'], 'actual_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparenting_actual_time');
        Schema::dropIfExists('coparenting_activity_children');
        Schema::dropIfExists('coparenting_activities');
    }
};
