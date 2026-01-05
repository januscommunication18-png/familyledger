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
        Schema::create('goal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('emoji', 10)->nullable();

            // Category
            $table->string('category', 50);

            // Target audience
            $table->string('audience', 20); // kids, teens, family, parents

            // Goal type defaults
            $table->string('goal_type', 30)->default('habit');
            $table->string('habit_frequency')->nullable();
            $table->integer('milestone_target')->nullable();
            $table->string('milestone_unit', 50)->nullable();

            // Suggested settings
            $table->boolean('suggested_rewards')->default(false);
            $table->string('suggested_reward_type', 30)->nullable();
            $table->string('suggested_check_in_frequency', 20)->nullable();

            // For custom templates
            $table->string('tenant_id', 36)->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();

            // System vs custom
            $table->boolean('is_system')->default(true);

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_templates');
    }
};
