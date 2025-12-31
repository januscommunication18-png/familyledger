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
        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('taxpayer_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->year('tax_year');
            $table->string('filing_status')->nullable(); // single, married_joint, married_separate, head_of_household, qualifying_widow
            $table->string('status')->default('not_started'); // not_started, in_progress, filed, accepted, rejected, amended
            $table->string('tax_jurisdiction')->default('federal'); // federal, state, both
            $table->string('state_jurisdiction')->nullable(); // CA, NY, TX, etc.
            $table->string('cpa_name')->nullable();
            $table->string('cpa_phone')->nullable();
            $table->string('cpa_email')->nullable();
            $table->string('cpa_firm')->nullable();
            $table->date('filing_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->decimal('amount_owed', 12, 2)->nullable();
            $table->json('federal_returns')->nullable(); // Array of file paths
            $table->json('state_returns')->nullable(); // Array of file paths
            $table->json('supporting_documents')->nullable(); // Array of file paths (W2s, 1099s, etc.)
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'tax_year']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_returns');
    }
};
