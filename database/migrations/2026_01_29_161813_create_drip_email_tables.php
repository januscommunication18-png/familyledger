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
        // Drip Campaigns table
        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['signup', 'trial_expiring', 'custom'])->default('custom');
            $table->enum('status', ['active', 'paused', 'draft'])->default('draft');
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('backoffice_admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'trigger_type']);
        });

        // Drip Email Steps table
        Schema::create('drip_email_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('drip_campaigns')->onDelete('cascade');
            $table->string('subject');
            $table->longText('body');
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->integer('sequence_order')->default(1);
            $table->timestamps();

            $table->index(['drip_campaign_id', 'sequence_order']);
        });

        // Drip Email Logs table
        Schema::create('drip_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('drip_campaigns')->onDelete('cascade');
            $table->foreignId('drip_email_step_id')->constrained('drip_email_steps')->onDelete('cascade');
            $table->string('tenant_id', 36)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('email');
            $table->datetime('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'opened', 'clicked'])->default('pending');
            $table->text('error_message')->nullable();
            $table->datetime('opened_at')->nullable();
            $table->datetime('clicked_at')->nullable();
            $table->string('tracking_token', 64)->unique()->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->index(['drip_campaign_id', 'status']);
            $table->index(['email', 'drip_email_step_id']);
            $table->index('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drip_email_logs');
        Schema::dropIfExists('drip_email_steps');
        Schema::dropIfExists('drip_campaigns');
    }
};
