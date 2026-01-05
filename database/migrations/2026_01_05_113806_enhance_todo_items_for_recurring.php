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
        Schema::table('todo_items', function (Blueprint $table) {
            // Make todo_list_id nullable (tasks are now flat)
            $table->foreignId('todo_list_id')->nullable()->change();

            // Goal linkage
            $table->foreignId('goal_id')->nullable()->after('todo_list_id')
                ->constrained('goals')->nullOnDelete();
            $table->boolean('count_toward_goal')->default(true)->after('goal_id');

            // Enhanced recurring fields
            $table->string('recurrence_frequency')->nullable()->after('is_recurring');
            $table->integer('recurrence_interval')->default(1)->after('recurrence_frequency');

            // Monthly recurrence options
            $table->string('monthly_type')->nullable()->after('recurrence_interval');
            $table->integer('monthly_day')->nullable()->after('monthly_type');
            $table->integer('monthly_week')->nullable()->after('monthly_day');
            $table->string('monthly_weekday')->nullable()->after('monthly_week');

            // Yearly recurrence
            $table->integer('yearly_month')->nullable()->after('monthly_weekday');
            $table->integer('yearly_day')->nullable()->after('yearly_month');

            // End conditions
            $table->string('recurrence_end_type')->default('never')->after('yearly_day');
            $table->integer('recurrence_max_occurrences')->nullable()->after('recurrence_end_type');
            $table->boolean('skip_weekends')->default(false)->after('recurrence_max_occurrences');

            // Recurrence behavior
            $table->string('generate_mode')->default('on_complete')->after('skip_weekends');
            $table->integer('schedule_ahead_days')->nullable()->after('generate_mode');
            $table->string('missed_policy')->default('carryover')->after('schedule_ahead_days');

            // Parent series reference (for recurring instances)
            $table->foreignId('parent_task_id')->nullable()->after('missed_policy')
                ->constrained('todo_items')->nullOnDelete();
            $table->boolean('is_series_template')->default(false)->after('parent_task_id');

            // Assignment rotation
            $table->string('rotation_type')->default('none')->after('is_series_template');
            $table->integer('rotation_current_index')->default(0)->after('rotation_type');
            $table->string('completion_type')->default('any_one')->after('rotation_current_index');

            // Series status for recurring tasks
            $table->string('series_status')->nullable()->after('completion_type');

            // Reminder settings
            $table->json('reminder_settings')->nullable()->after('series_status');

            // Indexes
            $table->index(['parent_task_id']);
            $table->index(['goal_id']);
            $table->index(['is_recurring', 'series_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['parent_task_id']);
            $table->dropIndex(['goal_id']);
            $table->dropIndex(['is_recurring', 'series_status']);

            // Drop foreign keys
            $table->dropForeign(['goal_id']);
            $table->dropForeign(['parent_task_id']);

            // Drop columns
            $table->dropColumn([
                'goal_id',
                'count_toward_goal',
                'recurrence_frequency',
                'recurrence_interval',
                'monthly_type',
                'monthly_day',
                'monthly_week',
                'monthly_weekday',
                'yearly_month',
                'yearly_day',
                'recurrence_end_type',
                'recurrence_max_occurrences',
                'skip_weekends',
                'generate_mode',
                'schedule_ahead_days',
                'missed_policy',
                'parent_task_id',
                'is_series_template',
                'rotation_type',
                'rotation_current_index',
                'completion_type',
                'series_status',
                'reminder_settings',
            ]);
        });
    }
};
