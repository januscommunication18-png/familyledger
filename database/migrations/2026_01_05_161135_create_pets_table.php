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
        // Main pets table
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');

            // Basic Info
            $table->string('name');
            $table->string('species'); // dog, cat, bird, fish, reptile, other
            $table->string('breed')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('approx_age')->nullable(); // For when exact DOB unknown
            $table->string('gender')->nullable(); // male, female, unknown
            $table->string('photo')->nullable();
            $table->string('microchip_id')->nullable();
            $table->string('status')->default('active'); // active, passed_away
            $table->date('passed_away_date')->nullable();

            // Health Snapshot
            $table->text('allergies')->nullable();
            $table->text('conditions')->nullable();
            $table->date('last_vet_visit')->nullable();
            $table->text('notes')->nullable();

            // Privacy
            $table->string('visibility')->default('family'); // family, caregivers_only

            // Vet Contact info
            $table->string('vet_name')->nullable();
            $table->string('vet_phone')->nullable();
            $table->string('vet_clinic')->nullable();
            $table->string('vet_address')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('status');
        });

        // Pet caregivers pivot table
        Schema::create('pet_caregivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('secondary'); // primary, secondary
            $table->text('notes')->nullable(); // e.g., "Stays with mom on weekends"
            $table->timestamps();

            $table->unique(['pet_id', 'family_member_id']);
        });

        // Pet vaccinations
        Schema::create('pet_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Rabies, Distemper, etc.
            $table->date('date_administered');
            $table->date('next_due_date')->nullable();
            $table->string('administered_by')->nullable(); // Vet name
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('next_due_date');
        });

        // Pet medications
        Schema::create('pet_medications', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable(); // daily, weekly, monthly, as_needed
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_medications');
        Schema::dropIfExists('pet_vaccinations');
        Schema::dropIfExists('pet_caregivers');
        Schema::dropIfExists('pets');
    }
};
