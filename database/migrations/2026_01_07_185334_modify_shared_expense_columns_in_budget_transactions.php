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
            // Drop the old shared_with column (was JSON array)
            $table->dropColumn('shared_with');

            // Add new column for tracking which child the expense is for
            $table->foreignId('shared_for_child_id')->nullable()->after('is_shared')
                ->constrained('family_members')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropForeign(['shared_for_child_id']);
            $table->dropColumn('shared_for_child_id');
            $table->json('shared_with')->nullable()->after('is_shared');
        });
    }
};
