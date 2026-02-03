<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to add 'skipped' status
        DB::statement("ALTER TABLE drip_email_logs MODIFY COLUMN status ENUM('pending', 'sent', 'failed', 'opened', 'clicked', 'skipped') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'skipped' from enum (this will fail if there are rows with 'skipped' status)
        DB::statement("ALTER TABLE drip_email_logs MODIFY COLUMN status ENUM('pending', 'sent', 'failed', 'opened', 'clicked') DEFAULT 'pending'");
    }
};
