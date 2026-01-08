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
        Schema::create('coparent_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('category'); // General, Schedule, Medical, Expense, Emergency
            $table->string('title');
            $table->text('content');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparent_message_templates');
    }
};
