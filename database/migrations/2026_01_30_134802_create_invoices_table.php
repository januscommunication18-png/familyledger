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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('package_plan_id')->nullable()->constrained()->onDelete('set null');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Invoice details
            $table->string('invoice_number')->unique();
            $table->string('paddle_transaction_id')->nullable()->index();
            $table->string('paddle_subscription_id')->nullable()->index();

            // Billing info
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Discount info
            $table->string('discount_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();

            // Status
            $table->enum('status', ['paid', 'pending', 'failed', 'refunded'])->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_date')->nullable();

            // Period covered
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();

            // Customer details (snapshot at time of invoice)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('billing_address')->nullable();

            // Email tracking
            $table->timestamp('emailed_at')->nullable();
            $table->integer('email_count')->default(0);

            // Additional data
            $table->json('paddle_data')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
