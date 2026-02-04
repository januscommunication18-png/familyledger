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
        Schema::create('coparenting_daily_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained('family_members')->onDelete('cascade');
            $table->date('checkin_date');
            $table->enum('parent_role', ['mother', 'father', 'parent']);
            $table->string('mood', 50); // emoji or mood identifier
            $table->text('notes')->nullable();
            $table->timestamps();

            // One check-in per child per day
            $table->unique(['tenant_id', 'family_member_id', 'checkin_date'], 'unique_daily_checkin');

            // Index for quick lookups
            $table->index(['tenant_id', 'checkin_date']);
            $table->index(['family_member_id', 'checkin_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coparenting_daily_checkins');
    }
};
