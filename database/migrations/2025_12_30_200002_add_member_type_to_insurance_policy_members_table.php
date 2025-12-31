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
        Schema::table('insurance_policy_members', function (Blueprint $table) {
            $table->string('member_type')->default('covered')->after('family_member_id');
            // member_type: 'policyholder' or 'covered'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_policy_members', function (Blueprint $table) {
            $table->dropColumn('member_type');
        });
    }
};
