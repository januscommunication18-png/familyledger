<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->string('relationship')->nullable(); // e.g., Doctor, Teacher, Coach, Emergency Contact
            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_emergency_contact')->default(false);
            $table->integer('priority')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('member_contacts');
        Schema::enableForeignKeyConstraints();
    }
};
