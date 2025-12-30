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
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('insurance_type'); // health, auto, home, life, dental, vision, disability, umbrella, other
            $table->string('provider_name');
            $table->string('policy_number')->nullable();
            $table->string('group_number')->nullable();
            $table->string('plan_name')->nullable();
            $table->decimal('premium_amount', 10, 2)->nullable();
            $table->string('payment_frequency')->nullable(); // monthly, quarterly, semi-annual, annual
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('status')->default('active'); // active, expired, cancelled, pending
            $table->string('agent_name')->nullable();
            $table->string('agent_phone')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('claims_phone')->nullable();
            $table->string('card_front_image')->nullable();
            $table->string('card_back_image')->nullable();
            $table->json('policy_documents')->nullable(); // Array of file paths
            $table->text('coverage_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'insurance_type']);
            $table->index(['tenant_id', 'status']);
        });

        // Pivot table for covered family members
        Schema::create('insurance_policy_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->constrained()->cascadeOnDelete();
            $table->string('relationship_to_policyholder')->nullable();
            $table->timestamps();

            $table->unique(['insurance_policy_id', 'family_member_id'], 'policy_member_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_policy_members');
        Schema::dropIfExists('insurance_policies');
    }
};
