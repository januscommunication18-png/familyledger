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
            $table->string('receipt_path')->nullable()->after('metadata');
            $table->string('receipt_original_filename')->nullable()->after('receipt_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'receipt_original_filename']);
        });
    }
};
