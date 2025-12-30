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
        Schema::create('member_healthcare_providers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');

            $table->string('provider_type'); // primary_doctor, specialist, dentist, therapist, emergency_doctor, other
            $table->string('name');
            $table->string('specialty')->nullable(); // pediatrician, cardiologist, etc.
            $table->string('clinic_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('preferred_contact')->nullable(); // phone, email, portal, text
            $table->text('notes')->nullable();
            $table->boolean('is_primary')->default(false);

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
        Schema::dropIfExists('member_healthcare_providers');
    }
};
