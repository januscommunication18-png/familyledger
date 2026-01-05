<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\FamilyMemberResource;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller for Family Members (read-only for Phase 1).
 */
class FamilyMemberController extends Controller
{
    /**
     * Get all members in a family circle.
     */
    public function index(Request $request, FamilyCircle $familyCircle): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        $members = $familyCircle->familyMembers()
            ->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'members' => FamilyMemberResource::collection($members),
            'total' => $members->count(),
        ]);
    }

    /**
     * Get a specific family member with details.
     */
    public function show(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        // Ensure the member belongs to this family circle
        if ($member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found in this family circle');
        }

        // Load relationships
        $member->load([
            'medicalInfo',
            'contacts',
            'documents',
            'allergies',
            'medications',
            'vaccinations',
            'healthcareProviders',
        ]);
        $member->loadCount('documents');

        return $this->success([
            'member' => new FamilyMemberResource($member),
        ]);
    }
}
