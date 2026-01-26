<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_education_documents', function (Blueprint $table) {
            $table->foreignId('school_record_id')->nullable()->after('family_member_id')->constrained('member_school_info')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('member_education_documents', function (Blueprint $table) {
            $table->dropForeign(['school_record_id']);
            $table->dropColumn('school_record_id');
        });
    }
};
