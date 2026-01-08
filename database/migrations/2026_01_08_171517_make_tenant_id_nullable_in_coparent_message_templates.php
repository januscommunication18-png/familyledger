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
        Schema::table('coparent_message_templates', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['tenant_id']);
            // Make tenant_id nullable for system templates
            $table->string('tenant_id', 36)->nullable()->change();
            // Re-add foreign key with nullable support
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coparent_message_templates', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->string('tenant_id', 36)->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }
};
