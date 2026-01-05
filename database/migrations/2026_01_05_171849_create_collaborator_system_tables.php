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
        // Collaborator Invites - stores pending/processed invitations
        Schema::create('collaborator_invites', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');

            // Invitee info
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('message')->nullable(); // Custom invite message

            // Relationship & Role
            $table->enum('relationship_type', [
                'parent', 'spouse', 'co_parent', 'child', 'guardian',
                'grandparent', 'relative', 'caregiver', 'advisor',
                'emergency_contact', 'other'
            ])->default('other');

            $table->enum('role', [
                'owner', 'admin', 'contributor', 'viewer', 'emergency_only'
            ])->default('viewer');

            // Invite token & status
            $table->string('token', 64)->unique();
            $table->enum('status', [
                'pending', 'accepted', 'declined', 'expired', 'revoked'
            ])->default('pending');

            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // The user who accepted (if any)
            $table->foreignId('accepted_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['email', 'status']);
            $table->index('token');
        });

        // Collaborators - stores accepted collaborators
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // The user who is the collaborator
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Who invited them
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');

            // Link to original invite
            $table->foreignId('invite_id')->nullable()->constrained('collaborator_invites')->onDelete('set null');

            // Relationship & Role
            $table->enum('relationship_type', [
                'parent', 'spouse', 'co_parent', 'child', 'guardian',
                'grandparent', 'relative', 'caregiver', 'advisor',
                'emergency_contact', 'other'
            ])->default('other');

            $table->enum('role', [
                'owner', 'admin', 'contributor', 'viewer', 'emergency_only'
            ])->default('viewer');

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('deactivated_at')->nullable();

            // Notes (internal)
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Pivot table: which family members a collaborator can access
        Schema::create('collaborator_family_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained('family_members')->onDelete('cascade');

            // Granular permissions per family member
            $table->json('permissions')->nullable(); // e.g. ['medical' => 'edit', 'school' => 'view', 'financial' => 'none']

            $table->timestamps();

            $table->unique(['collaborator_id', 'family_member_id'], 'collab_family_unique');
        });

        // Pivot table for invites: which family members will be accessible
        Schema::create('collaborator_invite_family_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_invite_id')->constrained('collaborator_invites')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained('family_members')->onDelete('cascade');

            // Planned permissions
            $table->json('permissions')->nullable();

            $table->timestamps();

            $table->unique(['collaborator_invite_id', 'family_member_id'], 'invite_family_member_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborator_invite_family_member');
        Schema::dropIfExists('collaborator_family_member');
        Schema::dropIfExists('collaborators');
        Schema::dropIfExists('collaborator_invites');
    }
};
