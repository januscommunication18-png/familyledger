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
        Schema::create('package_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['free', 'paid'])->default('free');
            $table->text('description')->nullable();
            $table->integer('trial_period_days')->default(30);
            $table->decimal('cost_per_month', 10, 2)->default(0);
            $table->decimal('cost_per_year', 10, 2)->default(0);

            // Feature limits (0 = unlimited)
            $table->integer('family_circles_limit')->default(0)->comment('0 = unlimited');
            $table->integer('family_members_limit')->default(0)->comment('0 = unlimited');
            $table->integer('document_storage_limit')->default(0)->comment('0 = unlimited');

            // Reminder features (stored as JSON array)
            $table->json('reminder_features')->nullable()->comment('push_notification, email_reminder, sms_reminder');

            // Paddle integration
            $table->string('paddle_product_id')->nullable();
            $table->string('paddle_monthly_price_id')->nullable();
            $table->string('paddle_yearly_price_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_plans');
    }
};
