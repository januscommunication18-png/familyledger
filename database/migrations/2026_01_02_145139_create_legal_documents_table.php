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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Document type and name
            $table->string('document_type'); // will, trust, power_of_attorney, medical_directive, other
            $table->string('custom_document_type')->nullable(); // for custom/other types
            $table->text('name'); // encrypted

            // Digital copy info
            $table->date('digital_copy_date')->nullable();

            // Location of original
            $table->text('original_location')->nullable(); // encrypted

            // Attorney info (can link to person from contacts)
            $table->foreignId('attorney_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->text('attorney_name')->nullable(); // encrypted - for when not linked to a person
            $table->text('attorney_phone')->nullable(); // encrypted
            $table->text('attorney_email')->nullable(); // encrypted
            $table->text('attorney_firm')->nullable(); // encrypted

            // Notes
            $table->text('notes')->nullable(); // encrypted

            // Status
            $table->string('status')->default('active'); // active, superseded, expired, revoked

            // Execution dates
            $table->date('execution_date')->nullable();
            $table->date('expiration_date')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'document_type']);
            $table->index(['tenant_id', 'status']);
        });

        // Legal document files (supports folders)
        Schema::create('legal_document_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();

            $table->string('file_path'); // storage path
            $table->text('original_name'); // encrypted
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');

            // Folder support
            $table->string('folder')->nullable(); // folder name if organized in folders

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_document_files');
        Schema::dropIfExists('legal_documents');
    }
};
