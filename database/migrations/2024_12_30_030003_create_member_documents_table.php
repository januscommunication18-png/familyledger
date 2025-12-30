<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_documents', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            // Document Type
            $table->enum('document_type', [
                'drivers_license', 'passport', 'social_security',
                'birth_certificate', 'other'
            ]);

            // Common fields
            $table->string('document_number')->nullable();
            $table->string('state_of_issue')->nullable();
            $table->string('country_of_issue')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('details')->nullable();

            // File uploads (encrypted paths)
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();

            // For SSN - store encrypted
            $table->text('encrypted_number')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'family_member_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_documents');
    }
};
