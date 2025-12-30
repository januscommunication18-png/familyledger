<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_school_info', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');

            $table->string('school_name');
            $table->string('grade_level')->nullable();
            $table->string('student_id')->nullable();
            $table->string('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();

            // Teacher/Counselor info
            $table->string('teacher_name')->nullable();
            $table->string('teacher_email')->nullable();
            $table->string('counselor_name')->nullable();
            $table->string('counselor_email')->nullable();

            // Bus info
            $table->string('bus_number')->nullable();
            $table->string('bus_pickup_time')->nullable();
            $table->string('bus_dropoff_time')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_school_info');
    }
};
