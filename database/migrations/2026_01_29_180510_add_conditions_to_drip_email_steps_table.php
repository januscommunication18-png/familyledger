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
        Schema::table('drip_email_steps', function (Blueprint $table) {
            // Condition type: none, has_family_circle, no_family_circle, has_logged_in, has_family_member
            $table->string('condition_type')->default('none')->after('sequence_order');

            // Trigger type: time_based (default - uses delays) or event_based (triggered by specific events)
            $table->string('trigger_type')->default('time_based')->after('condition_type');

            // For event-based: which event triggers this step
            // e.g., 'family_circle_created', 'family_member_added', 'document_uploaded'
            $table->string('trigger_event')->nullable()->after('trigger_type');

            // Whether to skip subsequent time-based steps when this event step is sent
            $table->boolean('skip_if_event_sent')->default(false)->after('trigger_event');

            $table->index(['drip_campaign_id', 'trigger_type']);
            $table->index(['trigger_event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drip_email_steps', function (Blueprint $table) {
            $table->dropIndex(['drip_campaign_id', 'trigger_type']);
            $table->dropIndex(['trigger_event']);
            $table->dropColumn([
                'condition_type',
                'trigger_type',
                'trigger_event',
                'skip_if_event_sent',
            ]);
        });
    }
};
