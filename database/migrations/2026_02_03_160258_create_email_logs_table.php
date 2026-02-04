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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mailable_class')->nullable(); // e.g., App\Mail\WelcomeEmail
            $table->string('mailable_type')->nullable(); // Friendly name: welcome, password_reset, etc.
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('subject');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('tenant_id', 36)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'sent', 'failed', 'opened', 'clicked', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->datetime('opened_at')->nullable();
            $table->datetime('clicked_at')->nullable();
            $table->string('tracking_token', 64)->unique()->nullable();
            $table->string('message_id')->nullable(); // SMTP message ID
            $table->json('metadata')->nullable(); // Extra data like drip_campaign_id, etc.
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->index(['status', 'created_at']);
            $table->index('to_email');
            $table->index('mailable_type');
            $table->index('tracking_token');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
