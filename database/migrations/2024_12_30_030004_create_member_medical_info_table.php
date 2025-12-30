<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_medical_info', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');

            // Medications
            $table->text('medications')->nullable();

            // Allergies
            $table->text('allergies')->nullable();

            // Medical conditions
            $table->text('medical_conditions')->nullable();

            // Blood type
            $table->string('blood_type')->nullable();

            // Primary physician
            $table->string('primary_physician')->nullable();
            $table->string('physician_phone')->nullable();

            // Insurance
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_group_number')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_medical_info');
    }
};
