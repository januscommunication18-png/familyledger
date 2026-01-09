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
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->string('insurance_type');
            $table->text('provider_name')->nullable();
            $table->text('policy_number')->nullable();
            $table->text('group_number')->nullable();
            $table->text('plan_name')->nullable();
            $table->decimal('premium_amount', 10, 2)->nullable();
            $table->string('payment_frequency')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('status')->default('active');
            $table->text('agent_name')->nullable();
            $table->text('agent_phone')->nullable();
            $table->text('agent_email')->nullable();
            $table->text('claims_phone')->nullable();
            $table->string('card_front_image')->nullable();
            $table->string('card_back_image')->nullable();
            $table->json('policy_documents')->nullable();
            $table->text('coverage_details')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'insurance_type']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('insurance_policy_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_policy_id')->constrained('insurance_policies')->cascadeOnDelete();
            $table->foreignId('family_member_id')->constrained('family_members')->cascadeOnDelete();
            $table->string('member_type')->default('covered'); // policyholder, covered
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