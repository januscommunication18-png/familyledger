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
        Schema::table('todo_items', function (Blueprint $table) {
            // Timezone for reminders (defaults to family timezone)
            $table->string('timezone')->nullable()->after('due_time');

            // Proof/photo requirement for completion
            $table->boolean('proof_required')->default(false)->after('completion_type');
            $table->string('proof_type')->nullable()->after('proof_required'); // photo, receipt, signature

            // Separate recurrence start date (if different from due_date)
            $table->date('recurrence_start_date')->nullable()->after('is_recurring');

            // Escalation settings for reminders
            $table->json('escalation_settings')->nullable()->after('reminder_settings');
            // Structure: {
            //   "enabled": true,
            //   "first_escalation_hours": 24,
            //   "escalate_to": "parents", // parents, admins, specific_member
            //   "escalate_to_member_id": null,
            //   "max_escalations": 2
            // }

            // Digest mode settings
            $table->boolean('digest_mode')->default(false)->after('escalation_settings');
            $table->string('digest_time')->nullable()->after('digest_mode'); // e.g., "08:00"
        });

        // Add proof fields to task_occurrences for per-occurrence proof
        Schema::table('task_occurrences', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('notes');
            $table->string('proof_type')->nullable()->after('proof_path');
            $table->timestamp('proof_submitted_at')->nullable()->after('proof_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn([
                'timezone',
                'proof_required',
                'proof_type',
                'recurrence_start_date',
                'escalation_settings',
                'digest_mode',
                'digest_time',
            ]);
        });

        Schema::table('task_occurrences', function (Blueprint $table) {
            $table->dropColumn([
                'proof_path',
                'proof_type',
                'proof_submitted_at',
            ]);
        });
    }
};
