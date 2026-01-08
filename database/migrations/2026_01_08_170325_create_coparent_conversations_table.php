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
        Schema::create('coparent_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->unsignedBigInteger('child_id'); // Links to coparent_children
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('coparent_children')->onDelete('cascade');
            $table->index(['tenant_id', 'last_message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparent_conversations');
    }
};
