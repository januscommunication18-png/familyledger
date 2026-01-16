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
        // Modify the source enum to include 'mobile'
        DB::statement("ALTER TABLE budget_transactions MODIFY COLUMN source ENUM('manual', 'mobile', 'csv_import', 'bank_sync') DEFAULT 'manual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'mobile' from the enum (revert to original)
        DB::statement("ALTER TABLE budget_transactions MODIFY COLUMN source ENUM('manual', 'csv_import', 'bank_sync') DEFAULT 'manual'");
    }
};