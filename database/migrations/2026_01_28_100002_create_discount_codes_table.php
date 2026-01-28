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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('discount_percentage', 5, 2);
            $table->enum('plan_type', ['monthly', 'yearly', 'both'])->default('both');

            // Optional: Link to specific package plans
            $table->foreignId('package_plan_id')->nullable()->constrained('package_plans')->nullOnDelete();

            // Usage limits
            $table->integer('max_uses')->nullable()->comment('NULL = unlimited');
            $table->integer('times_used')->default(0);

            // Validity period
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index for quick lookups
            $table->index(['code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
