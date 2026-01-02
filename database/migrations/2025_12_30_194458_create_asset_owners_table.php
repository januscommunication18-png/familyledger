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
        Schema::create('asset_owners', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained()->nullOnDelete();

            // External owner (non-family member)
            $table->string('external_owner_name')->nullable();
            $table->string('external_owner_email')->nullable();
            $table->string('external_owner_phone')->nullable();

            $table->decimal('ownership_percentage', 5, 2)->default(100.00);
            $table->boolean('is_primary_owner')->default(false);

            $table->timestamps();

            // Unique constraint for family member per asset
            $table->unique(['asset_id', 'family_member_id'], 'asset_family_member_unique');
            $table->index(['tenant_id', 'asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asset_owners');
        Schema::enableForeignKeyConstraints();
    }
};
