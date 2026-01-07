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
        // 1. Budgets table
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['envelope', 'traditional'])->default('envelope');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('period', ['weekly', 'biweekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Budget categories/envelopes table
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->decimal('allocated_amount', 12, 2)->default(0);
            $table->decimal('spent_amount', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Transactions table
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('budget_categories')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['expense', 'income', 'transfer'])->default('expense');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->string('payee')->nullable();
            $table->date('transaction_date');
            $table->enum('source', ['manual', 'csv_import', 'bank_sync'])->default('manual');
            $table->string('import_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'transaction_date']);
            $table->index(['budget_id', 'category_id']);
        });

        // 4. Budget shares table (for collaboration)
        Schema::create('budget_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->enum('permission', ['view', 'edit', 'admin'])->default('view');
            $table->timestamps();

            $table->unique(['budget_id', 'collaborator_id']);
        });

        // 5. Budget alerts table
        Schema::create('budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('budget_categories')->nullOnDelete();
            $table->enum('type', ['percentage', 'amount'])->default('percentage');
            $table->decimal('threshold', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
        Schema::dropIfExists('budget_shares');
        Schema::dropIfExists('budget_transactions');
        Schema::dropIfExists('budget_categories');
        Schema::dropIfExists('budgets');
    }
};
