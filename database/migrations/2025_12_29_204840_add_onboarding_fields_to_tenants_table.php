<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('country')->nullable()->after('slug');
            $table->string('timezone')->nullable()->after('country');
            $table->string('family_type')->nullable()->after('timezone');
            $table->json('goals')->nullable()->after('family_type');
            $table->json('quick_setup')->nullable()->after('goals');
            $table->boolean('onboarding_completed')->default(false)->after('quick_setup');
            $table->integer('onboarding_step')->default(1)->after('onboarding_completed');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'timezone',
                'family_type',
                'goals',
                'quick_setup',
                'onboarding_completed',
                'onboarding_step',
            ]);
        });
    }
};
