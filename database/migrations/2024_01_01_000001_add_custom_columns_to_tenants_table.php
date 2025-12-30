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
            $table->string('name')->nullable()->after('id');
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('subscription_tier')->default('free')->after('slug');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_tier');
            $table->boolean('is_active')->default(true)->after('subscription_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'slug',
                'subscription_tier',
                'subscription_expires_at',
                'is_active',
            ]);
        });
    }
};
