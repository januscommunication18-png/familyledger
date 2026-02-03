<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need sync support
     */
    protected array $syncTables = [
        'shopping_lists',
        'shopping_items',
        'goals',
        'tasks',
        'assets',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add sync columns to existing tables
        foreach ($this->syncTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Version number for optimistic locking
                    if (!Schema::hasColumn($table->getTable(), 'version')) {
                        $table->unsignedInteger('version')->default(1);
                    }

                    // Track which device last modified
                    if (!Schema::hasColumn($table->getTable(), 'last_modified_device')) {
                        $table->string('last_modified_device', 100)->nullable();
                    }

                    // Soft delete support for sync (if not already exists)
                    if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                        $table->softDeletes();
                    }
                });
            }
        }

        // Create sync_logs table for tracking sync operations
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tenant_id')->nullable(); // String to match tenants.id
            $table->string('device_id', 100);
            $table->string('device_name')->nullable();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('operation', 20); // create, update, delete, toggle
            $table->json('changes')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index(['user_id', 'entity_type', 'synced_at']);
            $table->index(['device_id', 'synced_at']);
            $table->index(['tenant_id', 'entity_type']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // Create conflict_resolutions table
        Schema::create('conflict_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tenant_id')->nullable(); // String to match tenants.id
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->json('server_data');
            $table->json('client_data');
            $table->string('resolution', 20)->default('pending'); // pending, server_wins, client_wins, merged
            $table->json('resolved_data')->nullable();
            $table->string('resolved_by')->nullable(); // user_id or 'auto'
            $table->timestamps();

            $table->index(['user_id', 'entity_type', 'resolution']);
            $table->index(['tenant_id', 'entity_type']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conflict_resolutions');
        Schema::dropIfExists('sync_logs');

        foreach ($this->syncTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'version')) {
                        $table->dropColumn('version');
                    }
                    if (Schema::hasColumn($table->getTable(), 'last_modified_device')) {
                        $table->dropColumn('last_modified_device');
                    }
                    // Note: Not dropping soft deletes as it might be used elsewhere
                });
            }
        }
    }
};
