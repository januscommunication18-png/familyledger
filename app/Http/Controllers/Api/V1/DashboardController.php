<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use App\Models\Asset;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller for dashboard data.
 */
class DashboardController extends Controller
{
    /**
     * Get dashboard overview data.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get counts
        $familyCirclesCount = FamilyCircle::where('tenant_id', $tenant->id)->count();
        $familyMembersCount = FamilyMember::where('tenant_id', $tenant->id)->count();
        $assetsCount = Asset::where('tenant_id', $tenant->id)->count();

        // Calculate total asset value
        $totalAssetValue = Asset::where('tenant_id', $tenant->id)
            ->whereNotNull('current_value')
            ->sum('current_value');

        return $this->success([
            'user' => new UserResource($user),
            'tenant' => new TenantResource($tenant),
            'stats' => [
                'family_circles' => $familyCirclesCount,
                'family_members' => $familyMembersCount,
                'assets' => $assetsCount,
                'total_asset_value' => round($totalAssetValue, 2),
                'formatted_asset_value' => '$' . number_format($totalAssetValue, 2),
            ],
            'quick_actions' => $this->getQuickActions($user),
        ]);
    }

    /**
     * Get dashboard statistics only.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Family members by relationship
        $membersByRelationship = FamilyMember::where('tenant_id', $tenant->id)
            ->selectRaw('relationship, COUNT(*) as count')
            ->groupBy('relationship')
            ->pluck('count', 'relationship')
            ->toArray();

        // Assets by category
        $assetsByCategory = Asset::where('tenant_id', $tenant->id)
            ->selectRaw('asset_category, COUNT(*) as count, SUM(current_value) as total_value')
            ->groupBy('asset_category')
            ->get()
            ->keyBy('asset_category')
            ->map(fn($item) => [
                'count' => $item->count,
                'total_value' => round($item->total_value ?? 0, 2),
            ])
            ->toArray();

        return $this->success([
            'members_by_relationship' => $membersByRelationship,
            'assets_by_category' => $assetsByCategory,
            'totals' => [
                'family_circles' => FamilyCircle::where('tenant_id', $tenant->id)->count(),
                'family_members' => FamilyMember::where('tenant_id', $tenant->id)->count(),
                'assets' => Asset::where('tenant_id', $tenant->id)->count(),
                'total_asset_value' => Asset::where('tenant_id', $tenant->id)->sum('current_value'),
            ],
        ]);
    }

    /**
     * Get quick actions based on user role and setup status.
     */
    protected function getQuickActions($user): array
    {
        $actions = [
            [
                'id' => 'add_member',
                'title' => 'Add Family Member',
                'icon' => 'user-plus',
                'route' => '/family-circle/add-member',
            ],
            [
                'id' => 'add_asset',
                'title' => 'Add Asset',
                'icon' => 'package-plus',
                'route' => '/assets/add',
            ],
            [
                'id' => 'view_documents',
                'title' => 'Documents',
                'icon' => 'file-text',
                'route' => '/documents',
            ],
            [
                'id' => 'settings',
                'title' => 'Settings',
                'icon' => 'settings',
                'route' => '/settings',
            ],
        ];

        return $actions;
    }
}
