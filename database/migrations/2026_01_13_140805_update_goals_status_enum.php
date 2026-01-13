<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing 'paused' to 'active' and 'completed' to 'done'
        DB::table('goals')->where('status', 'paused')->update(['status' => 'active']);
        DB::table('goals')->where('status', 'completed')->update(['status' => 'active']);

        // Change the ENUM column to include new values
        DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('active', 'in_progress', 'done', 'skipped', 'archived') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update statuses back to old values
        DB::table('goals')->where('status', 'in_progress')->update(['status' => 'active']);
        DB::table('goals')->where('status', 'done')->update(['status' => 'completed']);
        DB::table('goals')->where('status', 'skipped')->update(['status' => 'archived']);

        // Revert the ENUM column
        DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('active', 'paused', 'completed', 'archived') DEFAULT 'active'");
    }
};
