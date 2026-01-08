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
        Schema::create('coparent_message_edits', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->unsignedBigInteger('message_id');
            $table->text('previous_content'); // Encrypted in model
            $table->text('new_content'); // Encrypted in model
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('coparent_messages')->onDelete('cascade');
            $table->index(['message_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparent_message_edits');
    }
};
