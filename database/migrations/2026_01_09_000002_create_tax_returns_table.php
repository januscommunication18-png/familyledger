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
        if (Schema::hasTable('tax_returns')) {
            return;
        }

        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('taxpayer_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->integer('tax_year');
            $table->string('filing_status')->nullable();
            $table->string('status')->default('not_started');
            $table->string('tax_jurisdiction')->nullable();
            $table->string('state_jurisdiction')->nullable();
            $table->text('cpa_name')->nullable();
            $table->text('cpa_phone')->nullable();
            $table->text('cpa_email')->nullable();
            $table->text('cpa_firm')->nullable();
            $table->date('filing_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->decimal('amount_owed', 12, 2)->nullable();
            $table->json('federal_returns')->nullable();
            $table->json('state_returns')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'tax_year']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('tax_return_taxpayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->foreignId('family_member_id')->constrained('family_members')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tax_return_id', 'family_member_id'], 'tax_return_taxpayer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_return_taxpayers');
        Schema::dropIfExists('tax_returns');
    }
};