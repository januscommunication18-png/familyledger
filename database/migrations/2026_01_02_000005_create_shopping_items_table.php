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
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('other');
            $table->string('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_checked')->default(false);
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'shopping_list_id', 'is_checked']);
            $table->index(['shopping_list_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('shopping_items');
        Schema::enableForeignKeyConstraints();
    }
};
