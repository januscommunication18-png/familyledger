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
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();

            $table->string('document_type'); // deed, title, registration, appraisal, insurance, receipt, photo, service_record, other
            $table->string('file_path');
            $table->string('original_filename');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->json('tags')->nullable(); // for tagify-style tagging
            $table->boolean('is_encrypted')->default(false);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'asset_id']);
            $table->index(['tenant_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asset_documents');
        Schema::enableForeignKeyConstraints();
    }
};
