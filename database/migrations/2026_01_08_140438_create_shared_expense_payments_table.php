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
        Schema::create('shared_expense_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('budget_transactions')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_from')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('split_percentage', 5, 2)->default(50.00);
            $table->enum('status', ['pending', 'paid', 'declined', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_filename')->nullable();
            $table->text('note')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['requested_from', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_expense_payments');
    }
};
