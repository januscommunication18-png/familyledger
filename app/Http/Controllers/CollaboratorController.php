<?php

namespace App\Http\Controllers;

use App\Mail\CollaboratorInviteMail;
use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CollaboratorController extends Controller
{
    /**
     * Display list of collaborators and pending invites.
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get active collaborators
        $collaborators = Collaborator::where('tenant_id', $tenantId)
            ->with(['user', 'familyMembers', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending invites
        $pendingInvites = CollaboratorInvite::where('tenant_id', $tenantId)
            ->pending()
            ->with(['inviter', 'familyMembers'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent activity (accepted, declined, expired)
        $recentInvites = CollaboratorInvite::where('tenant_id', $tenantId)
            ->whereIn('status', ['accepted', 'declined', 'expired', 'revoked'])
            ->with(['inviter', 'acceptedUser'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Stats
        $stats = [
            'total_collaborators' => $collaborators->where('is_active', true)->count(),
            'pending_invites' => $pendingInvites->count(),
            'total_invited' => CollaboratorInvite::where('tenant_id', $tenantId)->count(),
        ];

        return view('pages.collaborators.index', [
            'collaborators' => $collaborators,
            'pendingInvites' => $pendingInvites,
            'recentInvites' => $recentInvites,
            'stats' => $stats,
            'relationshipTypes' => CollaboratorInvite::RELATIONSHIP_TYPES,
            'roles' => CollaboratorInvite::ROLES,
        ]);
    }

    /**
     * Show family circle selection (Step 1).
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get all family circles for this tenant
        $familyCircles = \App\Models\FamilyCircle::where('tenant_id', $tenantId)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        // If no circles exist, redirect to create one
        if ($familyCircles->isEmpty()) {
            return redirect()->route('family-circle.index')
                ->with('error', 'Please create a family circle first before inviting collaborators.');
        }

        // If circles are already selected (from step 1), show invite form
        if ($request->has('circles') && is_array($request->circles)) {
            $selectedCircleIds = $request->circles;

            // Get family members grouped by circle
            $selectedCircles = \App\Models\FamilyCircle::where('tenant_id', $tenantId)
                ->whereIn('id', $selectedCircleIds)
                ->with(['members' => function ($query) {
                    $query->orderBy('first_name');
                }])
                ->get();

            return view('pages.collaborators.invite', [
                'selectedCircles' => $selectedCircles,
                'relationshipTypes' => CollaboratorInvite::RELATIONSHIP_TYPES,
                'permissionCategories' => CollaboratorInvite::PERMISSION_CATEGORIES,
                'permissionLevels' => CollaboratorInvite::PERMISSION_LEVELS,
            ]);
        }

        // Show circle selection (Step 1)
        return view('pages.collaborators.select-circles', [
            'familyCircles' => $familyCircles,
        ]);
    }

    /**
     * Send invitation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:1000',
            'relationship_type' => 'required|string|in:' . implode(',', array_keys(CollaboratorInvite::RELATIONSHIP_TYPES)),
            'family_members' => 'required|array|min:1',
            'family_members.*' => 'exists:family_members,id',
            'permissions' => 'nullable|array',
            'selected_circles' => 'nullable|string', // Hidden field to track selected circles
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $email = strtolower($request->email);

        // Check if already a collaborator
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $existingCollaborator = Collaborator::where('tenant_id', $tenantId)
                ->where('user_id', $existingUser->id)
                ->first();

            if ($existingCollaborator) {
                return back()->with('error', 'This person is already a collaborator.');
            }
        }

        // Check for pending invite
        $pendingInvite = CollaboratorInvite::where('tenant_id', $tenantId)
            ->byEmail($email)
            ->pending()
            ->first();

        if ($pendingInvite) {
            return back()->with('error', 'An invitation is already pending for this email.');
        }

        // Default role is 'viewer' - collaborators get view access
        $role = 'viewer';

        // Create invite
        $invite = CollaboratorInvite::create([
            'tenant_id' => $tenantId,
            'invited_by' => $user->id,
            'email' => $email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'message' => $request->message,
            'relationship_type' => $request->relationship_type,
            'role' => $role,
        ]);

        // Attach family members with permissions (default viewer permissions)
        foreach ($request->family_members as $memberId) {
            $permissions = $request->permissions[$memberId] ?? $this->getDefaultPermissions($role);
            $invite->familyMembers()->attach($memberId, [
                'permissions' => json_encode($permissions),
            ]);
        }

        // Load relationships for email
        $invite->load(['inviter', 'familyMembers']);

        // Send email notification
        Mail::to($email)->send(new CollaboratorInviteMail($invite));

        return redirect()->route('collaborators.index')
            ->with('success', "Invitation sent to {$invite->full_name}!");
    }

    /**
     * Show collaborator details.
     */
    public function show(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $collaborator->load(['user', 'familyMembers', 'inviter', 'invite']);

        return view('pages.collaborators.show', [
            'collaborator' => $collaborator,
            'accessSummary' => $collaborator->getAccessSummary(),
            'roles' => Collaborator::ROLES,
            'permissionCategories' => Collaborator::PERMISSION_CATEGORIES,
            'permissionLevels' => Collaborator::PERMISSION_LEVELS,
        ]);
    }

    /**
     * Edit collaborator permissions.
     */
    public function edit(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $collaborator->load(['user', 'familyMembers']);

        $familyMembers = FamilyMember::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('first_name')
            ->get();

        // Get current permissions
        $currentPermissions = [];
        foreach ($collaborator->familyMembers as $member) {
            $currentPermissions[$member->id] = json_decode($member->pivot->permissions ?? '{}', true) ?: [];
        }

        return view('pages.collaborators.edit', [
            'collaborator' => $collaborator,
            'familyMembers' => $familyMembers,
            'currentPermissions' => $currentPermissions,
            'roles' => Collaborator::ROLES,
            'relationshipTypes' => Collaborator::RELATIONSHIP_TYPES,
            'permissionCategories' => Collaborator::PERMISSION_CATEGORIES,
            'permissionLevels' => Collaborator::PERMISSION_LEVELS,
        ]);
    }

    /**
     * Update collaborator.
     */
    public function update(Request $request, Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(Collaborator::ROLES)),
            'relationship_type' => 'required|string|in:' . implode(',', array_keys(Collaborator::RELATIONSHIP_TYPES)),
            'family_members' => 'required|array|min:1',
            'family_members.*' => 'exists:family_members,id',
            'permissions' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $collaborator->update([
            'role' => $request->role,
            'relationship_type' => $request->relationship_type,
            'notes' => $request->notes,
        ]);

        // Sync family members with permissions
        $syncData = [];
        foreach ($request->family_members as $memberId) {
            $permissions = $request->permissions[$memberId] ?? $this->getDefaultPermissions($request->role);
            $syncData[$memberId] = [
                'permissions' => json_encode($permissions),
            ];
        }
        $collaborator->familyMembers()->sync($syncData);

        return redirect()->route('collaborators.show', $collaborator)
            ->with('success', 'Collaborator updated successfully.');
    }

    /**
     * Deactivate collaborator.
     */
    public function deactivate(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $collaborator->deactivate();

        return redirect()->route('collaborators.index')
            ->with('success', 'Collaborator access has been deactivated.');
    }

    /**
     * Reactivate collaborator.
     */
    public function activate(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $collaborator->activate();

        return redirect()->route('collaborators.index')
            ->with('success', 'Collaborator access has been reactivated.');
    }

    /**
     * Update collaborator role quickly.
     */
    public function updateRole(Request $request, Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $request->validate([
            'role' => 'required|string|in:viewer,editor,admin',
        ]);

        $collaborator->update([
            'role' => $request->role,
        ]);

        return back()->with('success', "Role updated to {$collaborator->role_info['label']}.");
    }

    /**
     * Resend welcome email to collaborator.
     */
    public function resendWelcome(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        // Load relationships for email
        $collaborator->load(['user', 'familyMembers', 'inviter']);

        // Send welcome email
        if ($collaborator->user && $collaborator->user->email) {
            Mail::to($collaborator->user->email)->send(new \App\Mail\CollaboratorWelcomeMail($collaborator));
        }

        return back()->with('success', 'Welcome email has been resent.');
    }

    /**
     * Send reminder notification to collaborator.
     */
    public function sendReminder(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        // Load relationships
        $collaborator->load(['user', 'familyMembers']);

        // Send reminder email
        if ($collaborator->user && $collaborator->user->email) {
            Mail::to($collaborator->user->email)->send(new \App\Mail\CollaboratorReminderMail($collaborator));
        }

        return back()->with('success', 'Reminder has been sent.');
    }

    /**
     * Remove collaborator permanently.
     */
    public function destroy(Collaborator $collaborator)
    {
        $this->authorizeAccess($collaborator);

        $name = $collaborator->display_name;
        $collaborator->delete();

        return redirect()->route('collaborators.index')
            ->with('success', "{$name} has been removed as a collaborator.");
    }

    // ==================== INVITE MANAGEMENT ====================

    /**
     * Show pending invite details.
     */
    public function showInvite(CollaboratorInvite $invite)
    {
        $this->authorizeInviteAccess($invite);

        $invite->load(['inviter', 'familyMembers']);

        return view('pages.collaborators.invite-details', [
            'invite' => $invite,
            'roles' => CollaboratorInvite::ROLES,
            'permissionCategories' => CollaboratorInvite::PERMISSION_CATEGORIES,
        ]);
    }

    /**
     * Resend invitation.
     */
    public function resendInvite(CollaboratorInvite $invite)
    {
        $this->authorizeInviteAccess($invite);

        if ($invite->status !== 'pending') {
            return back()->with('error', 'Only pending invites can be resent.');
        }

        $invite->resend();

        // Load relationships for email
        $invite->load(['inviter', 'familyMembers']);

        // Send email again
        Mail::to($invite->email)->send(new CollaboratorInviteMail($invite));

        return back()->with('success', 'Invitation has been resent.');
    }

    /**
     * Revoke invitation.
     */
    public function revokeInvite(CollaboratorInvite $invite)
    {
        $this->authorizeInviteAccess($invite);

        if ($invite->status !== 'pending') {
            return back()->with('error', 'Only pending invites can be revoked.');
        }

        $invite->revoke();

        return redirect()->route('collaborators.index')
            ->with('success', 'Invitation has been revoked.');
    }

    // ==================== ACCEPT INVITE FLOW ====================

    /**
     * Show accept invite page (public).
     */
    public function acceptForm(string $token)
    {
        // Store the intended URL so we can redirect back after login/MFA
        session(['url.intended' => route('collaborator.accept', $token)]);

        $invite = CollaboratorInvite::findByToken($token);

        if (!$invite) {
            return view('pages.collaborators.accept-invalid', [
                'reason' => 'not_found',
            ]);
        }

        if ($invite->status === 'accepted') {
            return view('pages.collaborators.accept-invalid', [
                'reason' => 'already_accepted',
                'invite' => $invite,
            ]);
        }

        if ($invite->status === 'revoked') {
            return view('pages.collaborators.accept-invalid', [
                'reason' => 'revoked',
            ]);
        }

        if ($invite->is_expired) {
            return view('pages.collaborators.accept-invalid', [
                'reason' => 'expired',
            ]);
        }

        $invite->load(['inviter', 'familyMembers']);

        // Check if user is logged in
        $user = Auth::user();

        if ($user && strtolower($user->email) === strtolower($invite->email)) {
            // User is logged in with matching email
            return view('pages.collaborators.accept', [
                'invite' => $invite,
                'user' => $user,
                'needsSignup' => false,
            ]);
        }

        if ($user) {
            // User is logged in but email doesn't match
            return view('pages.collaborators.accept', [
                'invite' => $invite,
                'user' => $user,
                'emailMismatch' => true,
                'needsSignup' => false,
            ]);
        }

        // User is not logged in - check if account exists
        $existingUser = User::where('email', strtolower($invite->email))->first();

        return view('pages.collaborators.accept', [
            'invite' => $invite,
            'needsSignup' => !$existingUser,
            'needsLogin' => (bool) $existingUser,
        ]);
    }

    /**
     * Process invite acceptance.
     */
    public function acceptInvite(Request $request, string $token)
    {
        $invite = CollaboratorInvite::findByToken($token);

        if (!$invite || !$invite->is_pending) {
            return redirect()->route('collaborator.accept', $token)
                ->with('error', 'This invitation is no longer valid.');
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login', ['redirect' => route('collaborator.accept', $token)])
                ->with('info', 'Please log in to accept this invitation.');
        }

        // Accept the invite
        $collaborator = $invite->accept($user);

        return redirect()->route('dashboard')
            ->with('success', "You're now a collaborator! You can access the shared family information.");
    }

    /**
     * Decline invite.
     */
    public function declineInvite(string $token)
    {
        $invite = CollaboratorInvite::findByToken($token);

        if (!$invite || !$invite->is_pending) {
            return redirect()->route('home')
                ->with('error', 'This invitation is no longer valid.');
        }

        $invite->decline();

        return view('pages.collaborators.declined', [
            'invite' => $invite,
        ]);
    }

    // ==================== PRIVATE METHODS ====================

    private function authorizeAccess(Collaborator $collaborator): void
    {
        if ($collaborator->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }
    }

    private function authorizeInviteAccess(CollaboratorInvite $invite): void
    {
        if ($invite->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }
    }

    private function getDefaultPermissions(string $role): array
    {
        // Get all permission categories
        $categories = array_keys(CollaboratorInvite::PERMISSION_CATEGORIES);

        $defaults = [
            'owner' => array_fill_keys($categories, 'full'),
            'admin' => array_fill_keys($categories, 'edit'),
            'contributor' => array_fill_keys($categories, 'view'),
            'viewer' => array_fill_keys($categories, 'view'),
            'emergency_only' => [
                'date_of_birth' => 'none',
                'immigration_status' => 'none',
                'drivers_license' => 'none',
                'passport' => 'none',
                'ssn' => 'none',
                'birth_certificate' => 'none',
                'medical' => 'view',
                'emergency_contacts' => 'view',
                'school' => 'none',
                'insurance' => 'none',
                'tax_returns' => 'none',
                'assets' => 'none',
            ],
        ];

        return $defaults[$role] ?? $defaults['viewer'];
    }
}
