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
        // Journal entries main table
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('body');
            $table->timestamp('entry_datetime')->useCurrent();
            $table->enum('type', ['journal', 'memory', 'note', 'milestone'])->default('journal');
            $table->enum('mood', ['happy', 'neutral', 'sad', 'angry', 'tired'])->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->enum('visibility', ['private', 'family', 'specific'])->default('private');
            $table->json('shared_with_user_ids')->nullable();
            $table->json('linked_entities')->nullable(); // [{type: 'family_member', id: 1}, {type: 'pet', id: 2}]
            $table->boolean('is_pinned')->default(false);
            $table->integer('pin_order')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['tenant_id', 'entry_datetime']);
            $table->index(['tenant_id', 'is_pinned']);
        });

        // Journal tags (user-created)
        Schema::create('journal_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable(); // Optional tag color
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'usage_count']);
        });

        // Pivot table for entry-tag relationship
        Schema::create('journal_entry_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['journal_entry_id', 'journal_tag_id']);
        });

        // Journal attachments
        Schema::create('journal_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['photo', 'file'])->default('photo');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('thumbnail_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['journal_entry_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_attachments');
        Schema::dropIfExists('journal_entry_tag');
        Schema::dropIfExists('journal_tags');
        Schema::dropIfExists('journal_entries');
    }
};
