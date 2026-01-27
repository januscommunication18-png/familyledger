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
        Schema::create('pending_coparent_edits', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // What is being edited (polymorphic)
            $table->string('editable_type'); // e.g., 'App\Models\FamilyMember', 'App\Models\MemberMedicalInfo'
            $table->unsignedBigInteger('editable_id')->nullable(); // The record being edited (nullable for new records)

            // Link to the family member (for grouping/display)
            $table->foreignId('family_member_id')->constrained('family_members')->onDelete('cascade');

            // What field is changing
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            // For record creation (e.g., adding a new allergy)
            $table->boolean('is_create')->default(false);
            $table->json('create_data')->nullable(); // Full data for new record creation

            // For record deletion
            $table->boolean('is_delete')->default(false);

            // Who requested the edit (coparent)
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');

            // Status tracking (following CollaboratorInvite pattern)
            $table->enum('status', ['pending', 'approved', 'rejected', 'canceled'])->default('pending');

            // Who reviewed (owner)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Request metadata
            $table->string('ip_address')->nullable();
            $table->text('request_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['family_member_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index('editable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_coparent_edits');
    }
};
