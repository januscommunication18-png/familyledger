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
        Schema::create('shopping_item_history', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name');
            $table->string('category')->default('other');
            $table->string('quantity')->nullable();
            $table->integer('purchase_count')->default(1);
            $table->timestamp('last_purchased_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'purchase_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('shopping_item_history');
        Schema::enableForeignKeyConstraints();
    }
};
