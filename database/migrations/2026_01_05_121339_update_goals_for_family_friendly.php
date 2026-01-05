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
        Schema::table('goals', function (Blueprint $table) {
            // Goal category (replacing color/icon system)
            $table->string('category', 50)->default('personal_growth')->after('description');

            // Goal assignment type
            $table->string('assignment_type', 30)->default('individual')->after('category');

            // Assigned family member (for individual goals)
            $table->foreignId('assigned_to')->nullable()->after('assignment_type')
                ->constrained('family_members')->nullOnDelete();

            // Goal type
            $table->string('goal_type', 30)->default('one_time')->after('assigned_to');

            // Habit frequency (for habit goals)
            $table->string('habit_frequency')->nullable()->after('goal_type');

            // Milestone settings (for milestone goals)
            $table->integer('milestone_target')->nullable()->after('habit_frequency');
            $table->integer('milestone_current')->default(0)->after('milestone_target');
            $table->string('milestone_unit', 50)->nullable()->after('milestone_current');

            // Kid-friendly settings
            $table->boolean('is_kid_goal')->default(false)->after('milestone_unit');
            $table->boolean('show_emoji_status')->default(true)->after('is_kid_goal');
            $table->integer('star_rating')->nullable()->after('show_emoji_status'); // 1-3 stars for kids

            // Reward settings
            $table->boolean('rewards_enabled')->default(false)->after('star_rating');
            $table->string('reward_type', 30)->nullable()->after('rewards_enabled');
            $table->string('reward_custom')->nullable()->after('reward_type');
            $table->boolean('reward_claimed')->default(false)->after('reward_custom');

            // Check-in settings
            $table->string('check_in_frequency', 20)->nullable()->after('reward_claimed');
            $table->timestamp('last_check_in')->nullable()->after('check_in_frequency');
            $table->timestamp('next_check_in')->nullable()->after('last_check_in');

            // Visibility settings
            $table->boolean('visible_to_kids')->default(true)->after('next_check_in');
            $table->boolean('kids_can_update')->default(false)->after('visible_to_kids');

            // Template reference
            $table->foreignId('template_id')->nullable()->after('kids_can_update');

            // Soft status (simplified)
            // Keeping existing 'status' column but will use: active, in_progress, done, skipped, archived
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'category',
                'assignment_type',
                'assigned_to',
                'goal_type',
                'habit_frequency',
                'milestone_target',
                'milestone_current',
                'milestone_unit',
                'is_kid_goal',
                'show_emoji_status',
                'star_rating',
                'rewards_enabled',
                'reward_type',
                'reward_custom',
                'reward_claimed',
                'check_in_frequency',
                'last_check_in',
                'next_check_in',
                'visible_to_kids',
                'kids_can_update',
                'template_id',
            ]);
        });
    }
};
