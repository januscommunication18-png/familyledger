<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_circle_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('linked_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Basic Info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->date('date_of_birth');
            $table->string('profile_image')->nullable();

            // Relationship
            $table->enum('relationship', [
                'self', 'spouse', 'partner', 'child', 'stepchild',
                'parent', 'sibling', 'grandparent', 'guardian',
                'caregiver', 'relative', 'other'
            ]);

            // Parent Info
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();

            // Status flags
            $table->boolean('is_minor')->default(false);
            $table->boolean('co_parenting_enabled')->default(false);

            // Immigration Status
            $table->enum('immigration_status', [
                'citizen', 'permanent_resident', 'visa_holder',
                'work_permit', 'student_visa', 'refugee_asylum', 'other'
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'family_circle_id']);
            $table->index(['tenant_id', 'relationship']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
