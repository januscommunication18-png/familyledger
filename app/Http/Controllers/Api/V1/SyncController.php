<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SyncController extends Controller
{
    public function __construct(
        protected SyncService $syncService
    ) {}

    /**
     * Get changes since last sync
     * GET /api/v1/sync/pull
     */
    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'last_sync_at' => 'nullable|date',
            'device_id' => 'required|string|max:100',
            'entities' => 'nullable|string', // Comma-separated list
        ]);

        $lastSyncAt = isset($validated['last_sync_at']) && $validated['last_sync_at']
            ? Carbon::parse($validated['last_sync_at'])
            : null;

        // Parse entities from comma-separated string or use defaults
        $defaultEntities = ['shopping_lists', 'shopping_items', 'goals', 'tasks', 'assets'];
        $entities = $defaultEntities;

        if (!empty($validated['entities'])) {
            $requestedEntities = array_map('trim', explode(',', $validated['entities']));
            $entities = array_intersect($requestedEntities, $defaultEntities);
            if (empty($entities)) {
                $entities = $defaultEntities;
            }
        }

        try {
            $changes = $this->syncService->getChangesSince(
                user: $request->user(),
                lastSyncAt: $lastSyncAt,
                entities: $entities
            );

            return response()->json([
                'success' => true,
                'data' => $changes,
                'server_time' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch changes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Push local changes to server
     * POST /api/v1/sync/push
     */
    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:100',
            'device_name' => 'nullable|string|max:255',
            'operations' => 'required|array',
            'operations.*.local_id' => 'required|string',
            'operations.*.operation_type' => 'required|in:create,update,delete,toggle',
            'operations.*.entity_type' => 'required|string',
            'operations.*.server_id' => 'nullable|integer',
            'operations.*.version' => 'nullable|integer',
            'operations.*.data' => 'nullable|array',
            'operations.*.created_at' => 'required|string',
        ]);

        try {
            $results = $this->syncService->processOperations(
                user: $request->user(),
                deviceId: $validated['device_id'],
                deviceName: $validated['device_name'] ?? null,
                operations: $validated['operations']
            );

            return response()->json([
                'success' => true,
                'results' => $results,
                'server_time' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync push failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve a conflict
     * POST /api/v1/sync/resolve
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'resolution' => 'required|in:server_wins,client_wins,merged',
            'merged_data' => 'required_if:resolution,merged|array',
        ]);

        try {
            $result = $this->syncService->resolveConflict(
                user: $request->user(),
                entityType: $validated['entity_type'],
                entityId: $validated['entity_id'],
                resolution: $validated['resolution'],
                mergedData: $validated['merged_data'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflict: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending conflicts for user
     * GET /api/v1/sync/conflicts
     */
    public function conflicts(Request $request): JsonResponse
    {
        try {
            $conflicts = $this->syncService->getPendingConflicts($request->user());

            return response()->json([
                'success' => true,
                'conflicts' => $conflicts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conflicts: ' . $e->getMessage(),
            ], 500);
        }
    }
}