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
        Schema::create('member_allergies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');

            $table->string('allergy_type'); // food, medication, environmental, latex, other
            $table->string('allergen_name'); // e.g., Penicillin, Peanuts, Pollen
            $table->string('severity'); // mild, moderate, severe, life_threatening
            $table->json('symptoms')->nullable(); // ['rash', 'breathing_difficulty', 'swelling', etc.]
            $table->text('emergency_instructions')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'family_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('member_allergies');
        Schema::enableForeignKeyConstraints();
    }
};
