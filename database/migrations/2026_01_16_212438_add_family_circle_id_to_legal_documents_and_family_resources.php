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
        // Add family_circle_id to legal_documents if it doesn't exist
        if (!Schema::hasColumn('legal_documents', 'family_circle_id')) {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->foreignUuid('family_circle_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('family_circles')
                    ->nullOnDelete();
            });
        }

        // Add family_circle_id to family_resources if it doesn't exist
        if (!Schema::hasColumn('family_resources', 'family_circle_id')) {
            Schema::table('family_resources', function (Blueprint $table) {
                $table->foreignUuid('family_circle_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('family_circles')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('legal_documents', 'family_circle_id')) {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->dropForeign(['family_circle_id']);
                $table->dropColumn('family_circle_id');
            });
        }

        if (Schema::hasColumn('family_resources', 'family_circle_id')) {
            Schema::table('family_resources', function (Blueprint $table) {
                $table->dropForeign(['family_circle_id']);
                $table->dropColumn('family_circle_id');
            });
        }
    }
};
