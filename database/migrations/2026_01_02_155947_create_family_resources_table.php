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
        Schema::create('family_resources', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('document_type', 50);
            $table->string('custom_document_type')->nullable();
            $table->text('name');
            $table->date('digital_copy_date')->nullable();
            $table->text('original_location')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('tenant_id');
            $table->index('document_type');
        });

        Schema::create('family_resource_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('family_resource_id');
            $table->string('file_path');
            $table->text('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('folder')->nullable();
            $table->timestamps();

            $table->foreign('family_resource_id')->references('id')->on('family_resources')->onDelete('cascade');
            $table->index('family_resource_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_resource_files');
        Schema::dropIfExists('family_resources');
    }
};
