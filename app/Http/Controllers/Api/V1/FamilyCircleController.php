<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\FamilyCircleResource;
use App\Models\FamilyCircle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller for Family Circles (read-only for Phase 1).
 */
class FamilyCircleController extends Controller
{
    /**
     * Get all family circles for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $circles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->withCount('familyMembers')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'family_circles' => FamilyCircleResource::collection($circles),
            'total' => $circles->count(),
        ]);
    }

    /**
     * Get a specific family circle with its members.
     */
    public function show(Request $request, FamilyCircle $familyCircle): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        // Load relationships
        $familyCircle->load([
            'familyMembers' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'createdBy',
        ]);

        return $this->success([
            'family_circle' => new FamilyCircleResource($familyCircle),
        ]);
    }
}
