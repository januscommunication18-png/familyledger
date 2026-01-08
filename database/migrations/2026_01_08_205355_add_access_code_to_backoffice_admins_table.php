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
        Schema::table('backoffice_admins', function (Blueprint $table) {
            $table->string('access_code')->nullable()->after('security_code_expires_at');
            $table->timestamp('access_code_expires_at')->nullable()->after('access_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backoffice_admins', function (Blueprint $table) {
            $table->dropColumn(['access_code', 'access_code_expires_at']);
        });
    }
};
