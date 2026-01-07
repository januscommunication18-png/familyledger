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
        Schema::table('collaborators', function (Blueprint $table) {
            $table->string('parent_role')->nullable()->after('coparenting_enabled'); // mother, father, parent
        });

        Schema::table('collaborator_invites', function (Blueprint $table) {
            $table->string('parent_role')->nullable()->after('is_coparent_invite'); // mother, father, parent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn('parent_role');
        });

        Schema::table('collaborator_invites', function (Blueprint $table) {
            $table->dropColumn('parent_role');
        });
    }
};
