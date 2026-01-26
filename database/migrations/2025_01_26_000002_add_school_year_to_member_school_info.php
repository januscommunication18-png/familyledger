<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_school_info', function (Blueprint $table) {
            $table->string('school_year', 20)->nullable()->after('grade_level'); // e.g., "2024-2025"
            $table->boolean('is_current')->default(true)->after('school_year');
            $table->date('start_date')->nullable()->after('is_current');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('member_school_info', function (Blueprint $table) {
            $table->dropColumn(['school_year', 'is_current', 'start_date', 'end_date']);
        });
    }
};
