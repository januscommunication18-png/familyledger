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
        Schema::create('member_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('family_member_id')->constrained()->cascadeOnDelete();
            $table->string('vaccine_type'); // From predefined list or custom
            $table->string('custom_vaccine_name')->nullable(); // For custom vaccines
            $table->date('vaccination_date')->nullable();
            $table->date('next_vaccination_date')->nullable();
            $table->string('administered_by')->nullable(); // Doctor/clinic name
            $table->string('lot_number')->nullable(); // Vaccine lot number
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable(); // For uploaded file
            $table->string('document_name')->nullable(); // Original file name
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'family_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_vaccinations');
    }
};
