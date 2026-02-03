<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\MemberDocument;
use App\Models\LegalDocument;
use App\Models\AssetDocument;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature  The feature to check (family_circles, family_members, documents)
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant) {
            return $next($request);
        }

        $tenant = $user->tenant;
        $plan = $tenant->getCurrentPlan();

        // If no plan, allow (might be in free tier with no limits)
        if (!$plan) {
            return $next($request);
        }

        // Get current count based on feature
        $currentCount = $this->getCurrentCount($tenant, $feature, $request);

        // Check if limit is reached
        if ($tenant->hasReachedLimit($feature, $currentCount)) {
            $limitName = $this->getFeatureLimitName($feature);
            $limit = $tenant->getFeatureLimit($feature);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "You have reached your plan's limit of {$limit} {$limitName}. Please upgrade your plan to add more.",
                    'upgrade_url' => route('subscription.pricing'),
                ], 403);
            }

            return redirect()
                ->back()
                ->with('error', "You have reached your plan's limit of {$limit} {$limitName}. Please upgrade your plan to add more.")
                ->with('upgrade_url', route('subscription.pricing'));
        }

        return $next($request);
    }

    /**
     * Get the current count for a feature.
     */
    protected function getCurrentCount($tenant, string $feature, Request $request): int
    {
        return match ($feature) {
            'family_circles', 'family_circles_limit' => FamilyCircle::where('tenant_id', $tenant->id)->count(),
            'family_members', 'family_members_limit' => FamilyMember::where('tenant_id', $tenant->id)->count(),
            'documents', 'document_storage', 'document_storage_limit' =>
                MemberDocument::where('tenant_id', $tenant->id)->count()
                + LegalDocument::where('tenant_id', $tenant->id)->count()
                + AssetDocument::where('tenant_id', $tenant->id)->count(),
            default => 0,
        };
    }

    /**
     * Get a human-readable name for the feature limit.
     */
    protected function getFeatureLimitName(string $feature): string
    {
        return match ($feature) {
            'family_circles', 'family_circles_limit' => 'family circles',
            'family_members', 'family_members_limit' => 'family members',
            'documents', 'document_storage', 'document_storage_limit' => 'documents',
            default => $feature,
        };
    }
}
