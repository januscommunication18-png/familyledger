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
        // Add is_coparent_invite flag to collaborator_invites table
        Schema::table('collaborator_invites', function (Blueprint $table) {
            $table->boolean('is_coparent_invite')->default(false)->after('role');
        });

        // Add coparenting_enabled flag to collaborators table
        Schema::table('collaborators', function (Blueprint $table) {
            $table->boolean('coparenting_enabled')->default(false)->after('is_active');
        });

        // Create dedicated co-parent children pivot table for simplified permission management
        Schema::create('coparent_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained('family_members')->onDelete('cascade');

            // Simplified permissions for co-parenting
            // {"basic_info": "view", "medical_records": "edit", "emergency_contacts": "view", ...}
            $table->json('permissions')->nullable();

            $table->timestamps();

            $table->unique(['collaborator_id', 'family_member_id'], 'coparent_child_unique');
            $table->index('collaborator_id');
            $table->index('family_member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparent_children');

        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn('coparenting_enabled');
        });

        Schema::table('collaborator_invites', function (Blueprint $table) {
            $table->dropColumn('is_coparent_invite');
        });
    }
};
