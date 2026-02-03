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
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('package_plan_id')->nullable()->after('subscription_tier')
                ->constrained('package_plans')->nullOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->nullable()->after('package_plan_id');
            $table->string('paddle_customer_id')->nullable()->after('billing_cycle');
            $table->string('paddle_subscription_id')->nullable()->after('paddle_customer_id');
            $table->timestamp('trial_ends_at')->nullable()->after('paddle_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['package_plan_id']);
            $table->dropColumn([
                'package_plan_id',
                'billing_cycle',
                'paddle_customer_id',
                'paddle_subscription_id',
                'trial_ends_at',
            ]);
        });
    }
};
