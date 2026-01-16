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
        Schema::table('family_resources', function (Blueprint $table) {
            $table->foreignId('family_circle_id')->nullable()->after('tenant_id')->constrained('family_circles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_resources', function (Blueprint $table) {
            $table->dropForeign(['family_circle_id']);
            $table->dropColumn('family_circle_id');
        });
    }
};
