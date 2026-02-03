<?php

namespace App\Services;

use App\Models\User;
use App\Models\SyncLog;
use App\Models\ConflictResolution;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Models\Goal;
use App\Models\Task;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncService
{
    /**
     * Entity type to Model class mapping
     */
    protected array $entityModels = [
        'shopping_lists' => ShoppingList::class,
        'shoppingList' => ShoppingList::class,
        'shopping_items' => ShoppingItem::class,
        'shoppingItem' => ShoppingItem::class,
        'goals' => Goal::class,
        'goal' => Goal::class,
        'goal_tasks' => Task::class,
        'goalTask' => Task::class,
        'tasks' => Task::class,
        'assets' => Asset::class,
        'asset' => Asset::class,
    ];

    /**
     * Get all changes since last sync
     */
    public function getChangesSince(User $user, ?Carbon $lastSyncAt, array $entities): array
    {
        $changes = [
            'updated' => [],
            'deleted' => [],
        ];

        $tenantId = $user->current_tenant_id;

        foreach ($entities as $entityType) {
            $modelClass = $this->getModelClass($entityType);
            if (!$modelClass) {
                continue;
            }

            // Build base query
            $query = $modelClass::query()->where('tenant_id', $tenantId);

            // Get updated records
            if ($lastSyncAt) {
                $updatedRecords = (clone $query)
                    ->where('updated_at', '>', $lastSyncAt)
                    ->get();
            } else {
                // First sync - get all records
                $updatedRecords = (clone $query)->get();
            }

            if ($updatedRecords->isNotEmpty()) {
                $changes['updated'][$entityType] = $updatedRecords->map(function ($record) {
                    $data = $record->toArray();
                    $data['version'] = $record->version ?? 1;
                    $data['server_updated_at'] = $record->updated_at?->toIso8601String();
                    return $data;
                })->toArray();
            }

            // Get soft-deleted records since last sync
            if ($lastSyncAt && method_exists($modelClass, 'onlyTrashed')) {
                $deletedRecords = $modelClass::onlyTrashed()
                    ->where('tenant_id', $tenantId)
                    ->where('deleted_at', '>', $lastSyncAt)
                    ->pluck('id')
                    ->toArray();

                if (!empty($deletedRecords)) {
                    $changes['deleted'][$entityType] = $deletedRecords;
                }
            }
        }

        return $changes;
    }

    /**
     * Process batch of operations from client
     */
    public function processOperations(
        User $user,
        string $deviceId,
        ?string $deviceName,
        array $operations
    ): array {
        $results = [];

        DB::beginTransaction();

        try {
            foreach ($operations as $op) {
                $result = $this->processOperation($user, $deviceId, $deviceName, $op);
                $results[] = $result;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Process single operation
     */
    protected function processOperation(
        User $user,
        string $deviceId,
        ?string $deviceName,
        array $op
    ): array {
        $entityType = $op['entity_type'];
        $operationType = $op['operation_type'];
        $localId = $op['local_id'];
        $serverId = $op['server_id'] ?? null;
        $clientVersion = $op['version'] ?? null;
        $data = $op['data'] ?? [];

        $modelClass = $this->getModelClass($entityType);

        if (!$modelClass) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => 'Unknown entity type: ' . $entityType,
            ];
        }

        try {
            return match ($operationType) {
                'create' => $this->handleCreate($user, $modelClass, $localId, $data, $deviceId, $deviceName, $entityType),
                'update' => $this->handleUpdate($user, $modelClass, $localId, $serverId, $clientVersion, $data, $deviceId, $deviceName, $entityType),
                'delete' => $this->handleDelete($user, $modelClass, $localId, $serverId, $deviceId, $entityType),
                'toggle' => $this->handleToggle($user, $modelClass, $localId, $serverId, $deviceId, $entityType),
                default => [
                    'local_id' => $localId,
                    'status' => 'error',
                    'error' => 'Unknown operation type: ' . $operationType,
                ],
            };
        } catch (\Exception $e) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle CREATE operation
     */
    protected function handleCreate(
        User $user,
        string $modelClass,
        string $localId,
        array $data,
        string $deviceId,
        ?string $deviceName,
        string $entityType
    ): array {
        // Add tenant/user context
        $data['tenant_id'] = $user->current_tenant_id;
        $data['user_id'] = $user->id;
        $data['version'] = 1;
        $data['last_modified_device'] = $deviceId;

        // Remove fields that shouldn't be mass assigned
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        $record = $modelClass::create($data);

        // Log the sync
        $this->logSync($user, $deviceId, $entityType, $record->id, 'create', $data);

        return [
            'local_id' => $localId,
            'server_id' => $record->id,
            'status' => 'created',
            'version' => $record->version,
            'server_updated_at' => $record->updated_at->toIso8601String(),
        ];
    }

    /**
     * Handle UPDATE operation with conflict detection
     */
    protected function handleUpdate(
        User $user,
        string $modelClass,
        string $localId,
        ?int $serverId,
        ?int $clientVersion,
        array $data,
        string $deviceId,
        ?string $deviceName,
        string $entityType
    ): array {
        if (!$serverId) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => 'Server ID required for update',
            ];
        }

        $record = $modelClass::where('tenant_id', $user->current_tenant_id)
            ->find($serverId);

        if (!$record) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => 'Record not found',
            ];
        }

        // Check for conflict (optimistic locking)
        $serverVersion = $record->version ?? 1;
        if ($clientVersion !== null && $serverVersion !== $clientVersion) {
            // Conflict detected!
            return $this->createConflict(
                user: $user,
                entityType: $entityType,
                record: $record,
                localId: $localId,
                clientData: $data,
                clientVersion: $clientVersion
            );
        }

        // No conflict - apply update
        $data['version'] = $serverVersion + 1;
        $data['last_modified_device'] = $deviceId;

        // Remove fields that shouldn't be updated
        unset($data['id'], $data['tenant_id'], $data['user_id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        $record->update($data);

        $this->logSync($user, $deviceId, $entityType, $record->id, 'update', $data);

        return [
            'local_id' => $localId,
            'server_id' => $record->id,
            'status' => 'updated',
            'version' => $record->version,
            'server_updated_at' => $record->updated_at->toIso8601String(),
        ];
    }

    /**
     * Handle DELETE operation
     */
    protected function handleDelete(
        User $user,
        string $modelClass,
        string $localId,
        ?int $serverId,
        string $deviceId,
        string $entityType
    ): array {
        if (!$serverId) {
            return [
                'local_id' => $localId,
                'status' => 'deleted', // Already doesn't exist on server
            ];
        }

        $record = $modelClass::where('tenant_id', $user->current_tenant_id)
            ->find($serverId);

        if (!$record) {
            return [
                'local_id' => $localId,
                'status' => 'deleted',
            ];
        }

        $record->delete(); // Soft delete if trait is present, otherwise hard delete

        $this->logSync($user, $deviceId, $entityType, $serverId, 'delete', []);

        return [
            'local_id' => $localId,
            'server_id' => $serverId,
            'status' => 'deleted',
        ];
    }

    /**
     * Handle TOGGLE operation (e.g., task completion, shopping item check)
     */
    protected function handleToggle(
        User $user,
        string $modelClass,
        string $localId,
        ?int $serverId,
        string $deviceId,
        string $entityType
    ): array {
        if (!$serverId) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => 'Server ID required for toggle',
            ];
        }

        $record = $modelClass::where('tenant_id', $user->current_tenant_id)
            ->find($serverId);

        if (!$record) {
            return [
                'local_id' => $localId,
                'status' => 'error',
                'error' => 'Record not found',
            ];
        }

        // Toggle based on entity type
        $currentValue = null;
        switch ($entityType) {
            case 'goal_tasks':
            case 'goalTask':
            case 'tasks':
                $record->status = $record->status === 'completed' ? 'open' : 'completed';
                $currentValue = $record->status;
                break;

            case 'shopping_items':
            case 'shoppingItem':
                $record->is_checked = !$record->is_checked;
                $currentValue = $record->is_checked;
                break;
        }

        $record->version = ($record->version ?? 1) + 1;
        $record->last_modified_device = $deviceId;
        $record->save();

        $this->logSync($user, $deviceId, $entityType, $serverId, 'toggle', []);

        return [
            'local_id' => $localId,
            'server_id' => $serverId,
            'status' => 'toggled',
            'version' => $record->version,
            'current_value' => $currentValue,
        ];
    }

    /**
     * Create conflict record
     */
    protected function createConflict(
        User $user,
        string $entityType,
        $record,
        string $localId,
        array $clientData,
        int $clientVersion
    ): array {
        ConflictResolution::create([
            'user_id' => $user->id,
            'tenant_id' => $user->current_tenant_id,
            'entity_type' => $entityType,
            'entity_id' => $record->id,
            'server_data' => $record->toArray(),
            'client_data' => $clientData,
            'resolution' => 'pending',
        ]);

        return [
            'local_id' => $localId,
            'server_id' => $record->id,
            'status' => 'conflict',
            'server_version' => $record->version ?? 1,
            'client_version' => $clientVersion,
            'server_data' => $record->toArray(),
        ];
    }

    /**
     * Resolve a conflict
     */
    public function resolveConflict(
        User $user,
        string $entityType,
        int $entityId,
        string $resolution,
        ?array $mergedData
    ): array {
        $modelClass = $this->getModelClass($entityType);

        if (!$modelClass) {
            throw new \InvalidArgumentException('Unknown entity type');
        }

        $record = $modelClass::where('tenant_id', $user->current_tenant_id)
            ->find($entityId);

        if (!$record) {
            throw new \Exception('Record not found');
        }

        $conflict = ConflictResolution::where('user_id', $user->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('resolution', 'pending')
            ->first();

        if ($resolution === 'client_wins' || $resolution === 'merged') {
            $dataToApply = $resolution === 'merged' ? $mergedData : ($conflict?->client_data ?? []);
            $dataToApply['version'] = ($record->version ?? 1) + 1;

            // Remove protected fields
            unset($dataToApply['id'], $dataToApply['tenant_id'], $dataToApply['user_id'], $dataToApply['created_at'], $dataToApply['updated_at']);

            $record->update($dataToApply);
        }
        // For 'server_wins', we don't need to do anything - server data is already correct

        if ($conflict) {
            $conflict->update([
                'resolution' => $resolution,
                'resolved_data' => $record->toArray(),
                'resolved_by' => (string) $user->id,
            ]);
        }

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'resolution' => $resolution,
            'data' => $record->fresh()->toArray(),
        ];
    }

    /**
     * Get pending conflicts for user
     */
    public function getPendingConflicts(User $user): array
    {
        return ConflictResolution::where('user_id', $user->id)
            ->where('tenant_id', $user->current_tenant_id)
            ->where('resolution', 'pending')
            ->get()
            ->toArray();
    }

    /**
     * Log sync operation
     */
    protected function logSync(
        User $user,
        string $deviceId,
        string $entityType,
        int $entityId,
        string $operation,
        array $changes
    ): void {
        SyncLog::create([
            'user_id' => $user->id,
            'tenant_id' => $user->current_tenant_id,
            'device_id' => $deviceId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'operation' => $operation,
            'changes' => $changes,
            'synced_at' => now(),
        ]);
    }

    /**
     * Get model class for entity type
     */
    protected function getModelClass(string $entityType): ?string
    {
        return $this->entityModels[$entityType] ?? null;
    }
}