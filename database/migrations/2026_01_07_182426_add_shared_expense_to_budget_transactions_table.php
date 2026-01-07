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
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('metadata');
            $table->json('shared_with')->nullable()->after('is_shared'); // Array of family_member IDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropColumn(['is_shared', 'shared_with']);
        });
    }
};
