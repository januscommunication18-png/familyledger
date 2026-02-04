<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\PendingCoparentEdit;
use App\Services\CoparentChildSelector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingCoparentEditController extends Controller
{
    /**
     * Check if current user is owner of the tenant.
     */
    protected function isOwner(): bool
    {
        $user = auth()->user();
        return PendingCoparentEdit::where('tenant_id', $user->tenant_id)->exists()
            || $user->tenant_id === $user->tenant_id; // User is always owner of their own tenant
    }

    /**
     * Get tenant IDs the user has coparent access to.
     */
    protected function getCoparentTenantIds(): array
    {
        $user = auth()->user();
        return Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->pluck('tenant_id')
            ->toArray();
    }

    /**
     * Display list of pending edits for owner review or coparent view.
     */
    public function index(Request $request): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get selected child for filtering
        $selectedChild = CoparentChildSelector::getEffectiveChild($user);
        $selectedChildId = $selectedChild?->id;

        // Check if user is viewing as owner or coparent
        $coparentTenantIds = $this->getCoparentTenantIds();
        $isOwner = true; // Default: viewing own tenant's edits

        // Get pending edits - owner sees all for their tenant, coparent sees their own submissions
        $query = PendingCoparentEdit::query()
            ->pending()
            ->with(['familyMember', 'requester', 'editable'])
            ->orderBy('created_at', 'desc');

        // Owner: sees edits for their tenant
        // Coparent: sees their own submitted edits
        $query->where(function ($q) use ($user, $coparentTenantIds) {
            // Owner's pending edits (edits TO their tenant)
            $q->where('tenant_id', $user->tenant_id);

            // Coparent's own submissions (edits they made to other tenants)
            if (!empty($coparentTenantIds)) {
                $q->orWhere(function ($subQ) use ($user, $coparentTenantIds) {
                    $subQ->whereIn('tenant_id', $coparentTenantIds)
                        ->where('requested_by', $user->id);
                });
            }
        });

        // Filter by selected child
        if ($selectedChildId) {
            $query->where('family_member_id', $selectedChildId);
        }

        $pendingEdits = $query->get()->groupBy('family_member_id');

        // Determine if user can take actions (only owner can approve/reject)
        $canTakeAction = $pendingEdits->flatten()->contains(function ($edit) use ($user) {
            return $edit->tenant_id === $user->tenant_id;
        });

        // Get counts
        $ownerPendingCount = PendingCoparentEdit::where('tenant_id', $user->tenant_id)->pending()->count();
        $mySubmissionsCount = PendingCoparentEdit::whereIn('tenant_id', $coparentTenantIds)
            ->where('requested_by', $user->id)
            ->pending()
            ->count();

        $counts = [
            'pending' => $ownerPendingCount + $mySubmissionsCount,
            'owner_pending' => $ownerPendingCount,
            'my_submissions' => $mySubmissionsCount,
            'approved' => PendingCoparentEdit::where('tenant_id', $user->tenant_id)->approved()->count(),
            'rejected' => PendingCoparentEdit::where('tenant_id', $user->tenant_id)->rejected()->count(),
        ];

        return view('pages.coparenting.pending-edits.index', compact('pendingEdits', 'counts', 'canTakeAction'));
    }

    /**
     * Show a specific pending edit.
     */
    public function show(PendingCoparentEdit $pendingEdit): JsonResponse
    {
        $user = auth()->user();

        // Ensure the edit belongs to current tenant
        abort_unless($pendingEdit->tenant_id === $user->tenant_id, 403);

        $pendingEdit->load(['familyMember', 'requester', 'editable']);

        return response()->json([
            'success' => true,
            'pending_edit' => $pendingEdit,
        ]);
    }

    /**
     * Approve a pending edit.
     */
    public function approve(Request $request, PendingCoparentEdit $pendingEdit): JsonResponse
    {
        $user = auth()->user();

        // Ensure the edit belongs to current tenant
        abort_unless($pendingEdit->tenant_id === $user->tenant_id, 403);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $success = $pendingEdit->approve($user, $validated['notes'] ?? null);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'This edit has already been reviewed',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Edit approved and applied successfully',
        ]);
    }

    /**
     * Reject a pending edit.
     */
    public function reject(Request $request, PendingCoparentEdit $pendingEdit): JsonResponse
    {
        $user = auth()->user();

        // Ensure the edit belongs to current tenant
        abort_unless($pendingEdit->tenant_id === $user->tenant_id, 403);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $success = $pendingEdit->reject($user, $validated['notes'] ?? null);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'This edit has already been reviewed',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Edit rejected',
        ]);
    }

    /**
     * Bulk approve multiple pending edits.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pending_coparent_edits,id',
        ]);

        $approved = 0;

        foreach ($validated['ids'] as $id) {
            $pendingEdit = PendingCoparentEdit::where('id', $id)
                ->where('tenant_id', $user->tenant_id)
                ->pending()
                ->first();

            if ($pendingEdit && $pendingEdit->approve($user)) {
                $approved++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$approved} edit(s) approved successfully",
            'approved_count' => $approved,
        ]);
    }

    /**
     * Bulk reject multiple pending edits.
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pending_coparent_edits,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $rejected = 0;

        foreach ($validated['ids'] as $id) {
            $pendingEdit = PendingCoparentEdit::where('id', $id)
                ->where('tenant_id', $user->tenant_id)
                ->pending()
                ->first();

            if ($pendingEdit && $pendingEdit->reject($user, $validated['notes'] ?? null)) {
                $rejected++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$rejected} edit(s) rejected",
            'rejected_count' => $rejected,
        ]);
    }

    /**
     * Get pending edit count (for badge/notification).
     */
    public function count(): JsonResponse
    {
        $user = auth()->user();

        $count = PendingCoparentEdit::where('tenant_id', $user->tenant_id)
            ->pending()
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Show history of reviewed edits.
     */
    public function history(Request $request): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();
        $status = $request->get('status', 'all');
        $coparentTenantIds = $this->getCoparentTenantIds();

        // Get selected child for filtering
        $selectedChild = CoparentChildSelector::getEffectiveChild($user);
        $selectedChildId = $selectedChild?->id;

        $query = PendingCoparentEdit::query()
            ->with(['familyMember', 'requester', 'reviewer'])
            ->orderBy('reviewed_at', 'desc');

        // Owner sees all history for their tenant
        // Coparent sees their own submissions' history
        $query->where(function ($q) use ($user, $coparentTenantIds) {
            // Owner's reviewed edits (edits TO their tenant)
            $q->where('tenant_id', $user->tenant_id);

            // Coparent's own submissions history (edits they made to other tenants)
            if (!empty($coparentTenantIds)) {
                $q->orWhere(function ($subQ) use ($user, $coparentTenantIds) {
                    $subQ->whereIn('tenant_id', $coparentTenantIds)
                        ->where('requested_by', $user->id);
                });
            }
        });

        // Filter by selected child
        if ($selectedChildId) {
            $query->where('family_member_id', $selectedChildId);
        }

        if ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        } else {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        $edits = $query->paginate(20);

        return view('pages.coparenting.pending-edits.history', compact('edits', 'status'));
    }
}
