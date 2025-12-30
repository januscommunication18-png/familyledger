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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Basic information
            $table->string('name');
            $table->string('asset_category'); // property, vehicle, valuable, inventory
            $table->string('asset_type'); // specific type within category
            $table->string('ownership_type')->default('individual'); // individual, joint, trust_company
            $table->string('status')->default('active'); // active, sold, disposed, transferred
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // Value & Dates
            $table->date('acquisition_date')->nullable();
            $table->decimal('purchase_value', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            // Location fields
            $table->string('location_address')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_state')->nullable();
            $table->string('location_zip')->nullable();
            $table->string('location_country')->nullable();
            $table->string('storage_location')->nullable(); // for valuables/inventory (room-based)

            // Vehicle-specific fields
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->integer('vehicle_year')->nullable();
            $table->string('vin_registration')->nullable();
            $table->string('vehicle_ownership')->nullable(); // owned, leased, financed
            $table->string('license_plate')->nullable();
            $table->integer('mileage')->nullable();

            // Collectable-specific fields
            $table->string('collectable_category')->nullable(); // art, jewelry, watch, antique, etc.
            $table->string('appraised_by')->nullable();
            $table->date('appraisal_date')->nullable();
            $table->decimal('appraisal_value', 12, 2)->nullable();
            $table->string('condition')->nullable(); // mint, excellent, good, fair, poor
            $table->text('provenance')->nullable();

            // Inventory-specific fields
            $table->string('serial_number')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('room_location')->nullable();

            // Insurance linkage
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_renewal_date')->nullable();
            $table->boolean('is_insured')->default(false);

            // Security
            $table->boolean('is_encrypted')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'asset_category']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'asset_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
