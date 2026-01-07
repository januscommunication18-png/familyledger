<?php

namespace App\Http\Controllers;

use App\Mail\CoparentInviteMail;
use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\CoparentChild;
use App\Models\CoparentingActivity;
use App\Models\CoparentingActualTime;
use App\Models\CoparentingSchedule;
use App\Models\CoparentingScheduleBlock;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CoparentingController extends Controller
{
    /**
     * Display the co-parenting dashboard/index.
     */
    public function index()
    {
        // Set co-parenting mode
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get children with co-parenting enabled from user's own tenant
        $ownChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->with(['coparents.user'])
            ->get();

        // Get children the user has co-parent access to (from other tenants)
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with(['coparentChildren' => function($query) {
                $query->with(['coparents.user']);
            }, 'inviter'])
            ->get();

        // Combine children - own children + children from co-parent access
        $sharedChildren = collect();
        foreach ($coparentAccess as $collaborator) {
            foreach ($collaborator->coparentChildren as $child) {
                // Add collaborator info to understand the context
                $child->accessed_via_collaborator = $collaborator;
                $child->other_parent_name = $collaborator->inviter->name ?? 'Unknown';
                $sharedChildren->push($child);
            }
        }

        // Merge own children and shared children, avoiding duplicates
        $children = $ownChildren->merge($sharedChildren)->unique('id');

        // If no children with co-parenting enabled and no co-parent access, show intro
        if ($children->isEmpty()) {
            return redirect()->route('coparenting.intro');
        }

        // Get pending co-parent invites sent by this user
        $pendingInvites = CollaboratorInvite::forCurrentTenant()
            ->coparentInvites()
            ->pending()
            ->where('invited_by', $user->id)
            ->with('familyMembers')
            ->get();

        // Get active co-parents for this tenant (people who accepted invites to co-parent)
        $coparents = Collaborator::forCurrentTenant()
            ->coparents()
            ->with(['user', 'coparentChildren'])
            ->get();

        // Check if current user is viewing as a co-parent (not tenant owner)
        $isCoparent = $coparentAccess->isNotEmpty();
        $currentUser = $user;

        return view('pages.coparenting.index', compact('children', 'pendingInvites', 'coparents', 'currentUser', 'isCoparent', 'sharedChildren'));
    }

    /**
     * Display the co-parenting intro page.
     */
    public function intro()
    {
        // Set co-parenting mode
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Check if there are children with co-parenting enabled - redirect to dashboard
        $hasCoparentingChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->exists();

        if ($hasCoparentingChildren) {
            return redirect()->route('coparenting.index');
        }

        // Check if there are any existing co-parents or invites
        $hasCoparents = Collaborator::forCurrentTenant()
            ->coparents()
            ->exists();

        $hasPendingInvites = CollaboratorInvite::forCurrentTenant()
            ->coparentInvites()
            ->pending()
            ->exists();

        // Get minors for co-parenting
        $minors = FamilyMember::forCurrentTenant()
            ->minors()
            ->get();

        return view('pages.coparenting.intro', compact('hasCoparents', 'hasPendingInvites', 'minors'));
    }

    /**
     * Enter co-parenting mode (set session).
     */
    public function enterMode(Request $request)
    {
        session(['coparenting_mode' => true]);

        return redirect()->route('coparenting.intro');
    }

    /**
     * Exit co-parenting mode (clear session).
     */
    public function exitMode(Request $request)
    {
        session()->forget('coparenting_mode');

        return redirect()->route('dashboard');
    }

    /**
     * Display the invite form.
     */
    public function inviteForm(): View
    {
        session(['coparenting_mode' => true]);

        // Get family circles
        $familyCircles = FamilyCircle::forCurrentTenant()->get();

        // Get all minors
        $minors = FamilyMember::forCurrentTenant()
            ->minors()
            ->get();

        // Get potential co-parents (spouse/partner from family circle)
        $potentialCoparents = FamilyMember::forCurrentTenant()
            ->whereIn('relationship', ['spouse', 'partner'])
            ->get();

        return view('pages.coparenting.invite', compact('familyCircles', 'minors', 'potentialCoparents'));
    }

    /**
     * Send a co-parent invitation.
     */
    public function sendInvite(Request $request)
    {
        $validated = $request->validate([
            'coparent_source' => 'nullable|string',
            'email' => 'nullable|email|required_if:coparent_source,new_invite',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'parent_role' => 'required|in:mother,father,parent',
            'message' => 'nullable|string|max:500',
            'children' => 'required|array|min:1',
            'children.*' => 'exists:family_members,id',
            'permissions' => 'nullable|array',
        ]);

        $user = auth()->user();

        // Determine email and name based on coparent source
        $email = $validated['email'] ?? null;
        $firstName = $validated['first_name'] ?? null;
        $lastName = $validated['last_name'] ?? null;

        // Check if selecting existing family member
        $coparentSource = $validated['coparent_source'] ?? 'new_invite';
        if (str_starts_with($coparentSource, 'existing_')) {
            $familyMemberId = (int) str_replace('existing_', '', $coparentSource);
            $familyMember = FamilyMember::forCurrentTenant()
                ->whereIn('relationship', ['spouse', 'partner'])
                ->find($familyMemberId);

            if ($familyMember) {
                $email = $familyMember->email;
                $firstName = $familyMember->first_name;
                $lastName = $familyMember->last_name;
            }
        }

        // Validate that we have an email
        if (!$email) {
            return back()->withErrors(['email' => 'Email address is required.'])->withInput();
        }

        // Create the co-parent invite
        $invite = CollaboratorInvite::create([
            'tenant_id' => $user->tenant_id,
            'invited_by' => $user->id,
            'email' => strtolower($email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'message' => $validated['message'] ?? null,
            'relationship_type' => 'co_parent',
            'role' => 'contributor',
            'is_coparent_invite' => true,
            'parent_role' => $validated['parent_role'],
        ]);

        // Attach children with default permissions
        $defaultPermissions = CoparentChild::getDefaultPermissions();
        foreach ($validated['children'] as $childId) {
            // Use provided permissions or default to view-only for common categories
            $permissions = $validated['permissions'][$childId] ?? [
                'basic_info' => 'view',
                'medical_records' => 'view',
                'emergency_contacts' => 'view',
                'school_info' => 'view',
                'documents' => 'none',
                'insurance' => 'none',
                'vaccinations' => 'view',
                'healthcare_providers' => 'view',
            ];

            $invite->familyMembers()->attach($childId, [
                'permissions' => json_encode($permissions),
            ]);

            // Enable co-parenting on the child if not already
            FamilyMember::where('id', $childId)->update(['co_parenting_enabled' => true]);
        }

        // Send email - load inviter relationship for email
        $invite->load('inviter');
        $children = FamilyMember::whereIn('id', $validated['children'])->get();
        Mail::to($invite->email)->send(new CoparentInviteMail($invite, $children));

        return redirect()->route('coparenting.index')
            ->with('success', 'Co-parent invitation sent successfully!');
    }

    /**
     * Resend a co-parent invitation email.
     */
    public function resendInvite(CollaboratorInvite $invite): RedirectResponse
    {
        // Ensure the invite belongs to the current tenant
        abort_unless($invite->tenant_id === auth()->user()->tenant_id, 403);

        // Ensure it's a pending co-parent invite
        abort_unless($invite->status === 'pending' && $invite->relationship_type === 'co_parent', 404);

        // Extend expiration by 7 days from now
        $invite->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Get children for the email
        $children = $invite->familyMembers;

        // Resend the email
        $invite->load('inviter');
        Mail::to($invite->email)->send(new CoparentInviteMail($invite, $children));

        return redirect()->route('coparenting.index')
            ->with('success', 'Invitation resent to ' . ($invite->full_name ?: $invite->email) . '!');
    }

    /**
     * Display list of children in co-parenting arrangements.
     */
    public function children(): View
    {
        session(['coparenting_mode' => true]);

        $children = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->with('coparents.user')
            ->get();

        return view('pages.coparenting.children.index', compact('children'));
    }

    /**
     * Display a specific child's details.
     */
    public function showChild(FamilyMember $child): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Check if user owns the tenant OR is a co-parent with access to this child
        $isOwner = $child->tenant_id === $user->tenant_id;
        $isCoparent = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->whereHas('coparentChildren', function($query) use ($child) {
                $query->where('family_member_id', $child->id);
            })
            ->exists();

        abort_unless($isOwner || $isCoparent, 403);

        // Load co-parents and related data for this child
        $child->load([
            'coparents.user',
            'medicalInfo',
            'schoolInfo',
            'emergencyContacts',
            'allergies',
            'medicalConditions',
            'medications',
            'healthcareProviders',
            'documents',
            'insurancePolicies',
            'taxReturns',
            'assets'
        ]);

        // Get the collaborator record if user is a co-parent (for permission checking)
        $collaborator = null;
        if ($isCoparent) {
            $collaborator = Collaborator::where('user_id', $user->id)
                ->where('coparenting_enabled', true)
                ->whereHas('coparentChildren', function($query) use ($child) {
                    $query->where('family_member_id', $child->id);
                })
                ->first();
        }

        return view('pages.coparenting.children.show', compact('child', 'isOwner', 'isCoparent', 'collaborator'));
    }

    /**
     * Display the access management form for a child.
     */
    public function manageAccess(FamilyMember $child): View
    {
        session(['coparenting_mode' => true]);

        // Only owners can manage access
        abort_unless($child->tenant_id === auth()->user()->tenant_id, 403);

        // Get all co-parents who have access to this child
        $coparents = $child->coparents()->with('user')->get();

        // Get permission categories
        $permissionCategories = CoparentChild::PERMISSION_CATEGORIES;
        $permissionLevels = CoparentChild::PERMISSION_LEVELS;

        return view('pages.coparenting.children.access', compact('child', 'coparents', 'permissionCategories', 'permissionLevels'));
    }

    /**
     * Update access permissions for a child.
     */
    public function updateAccess(Request $request, FamilyMember $child)
    {
        // Ensure the child belongs to the current tenant
        abort_unless($child->tenant_id === auth()->user()->tenant_id, 403);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*.collaborator_id' => 'required|exists:collaborators,id',
            'permissions.*.categories' => 'required|array',
        ]);

        foreach ($validated['permissions'] as $permData) {
            $collaboratorId = $permData['collaborator_id'];
            $categories = $permData['categories'];

            // Update or create the coparent_children record
            $pivot = CoparentChild::updateOrCreate(
                [
                    'collaborator_id' => $collaboratorId,
                    'family_member_id' => $child->id,
                ],
                [
                    'permissions' => $categories,
                ]
            );
        }

        return redirect()->route('coparenting.children.access', $child)
            ->with('success', 'Access permissions updated successfully!');
    }

    // ==================== CALENDAR & SCHEDULE ====================

    /**
     * Display the co-parenting calendar page.
     */
    public function calendar(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get active schedules for this tenant
        $schedules = CoparentingSchedule::forCurrentTenant()
            ->active()
            ->with('children')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available template types
        $templateTypes = CoparentingSchedule::TEMPLATE_TYPES;

        // Get co-parenting children
        $children = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get co-parents for parent dropdown
        $coparents = Collaborator::forCurrentTenant()
            ->coparents()
            ->with('user')
            ->get();

        return view('pages.coparenting.calendar', compact('schedules', 'templateTypes', 'children', 'coparents'));
    }

    /**
     * Get calendar events for AJAX request.
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()));

        // Get active schedules
        $schedules = CoparentingSchedule::forCurrentTenant()
            ->active()
            ->current()
            ->get();

        $events = [];

        foreach ($schedules as $schedule) {
            $scheduleEvents = $schedule->generateEventsForRange($start, $end);

            foreach ($scheduleEvents as $event) {
                $color = $event['parent'] === 'mother' ? '#ec4899' : '#3b82f6'; // pink for mother, blue for father

                $events[] = [
                    'id' => $schedule->id . '-' . $event['start'],
                    'title' => $event['title'],
                    'start' => $event['start'],
                    'end' => Carbon::parse($event['end'])->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'parent' => $event['parent'],
                        'schedule_id' => $schedule->id,
                    ],
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * Store a new schedule.
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'template_type' => 'required|string',
            'begins_at' => 'required|date',
            'has_end_date' => 'boolean',
            'ends_at' => 'nullable|date|after_or_equal:begins_at',
            'repeat_every' => 'nullable|integer|min:1',
            'repeat_unit' => 'nullable|string|in:days,weeks',
            'primary_parent' => 'required|string|in:mother,father',
            'children' => 'nullable|array',
            'children.*' => 'exists:family_members,id',
        ]);

        $user = auth()->user();

        // Deactivate any existing active schedules (only one active schedule allowed)
        CoparentingSchedule::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $hasEndDate = $validated['has_end_date'] ?? false;

        $schedule = CoparentingSchedule::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'name' => $validated['name'] ?? null,
            'template_type' => $validated['template_type'],
            'begins_at' => $validated['begins_at'],
            'has_end_date' => $hasEndDate,
            'ends_at' => $hasEndDate ? ($validated['ends_at'] ?? null) : null,
            'repeat_every' => $validated['repeat_every'] ?? null,
            'repeat_unit' => $validated['repeat_unit'] ?? null,
            'primary_parent' => $validated['primary_parent'],
        ]);

        // Attach children if provided
        if (!empty($validated['children'])) {
            $schedule->children()->attach($validated['children']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully!',
                'schedule' => $schedule->load('children'),
            ]);
        }

        return redirect()->route('coparenting.calendar')
            ->with('success', 'Schedule created successfully!');
    }

    /**
     * Update a schedule.
     */
    public function updateSchedule(Request $request, CoparentingSchedule $schedule)
    {
        // Ensure schedule belongs to current tenant
        abort_unless($schedule->tenant_id === auth()->user()->tenant_id, 403);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'template_type' => 'required|string',
            'begins_at' => 'required|date',
            'has_end_date' => 'boolean',
            'ends_at' => 'nullable|date|after_or_equal:begins_at',
            'repeat_every' => 'nullable|integer|min:1',
            'repeat_unit' => 'nullable|string|in:days,weeks',
            'primary_parent' => 'required|string|in:mother,father',
            'is_active' => 'boolean',
            'children' => 'nullable|array',
            'children.*' => 'exists:family_members,id',
        ]);

        $hasEndDate = $validated['has_end_date'] ?? false;

        $schedule->update([
            'name' => $validated['name'] ?? null,
            'template_type' => $validated['template_type'],
            'begins_at' => $validated['begins_at'],
            'has_end_date' => $hasEndDate,
            'ends_at' => $hasEndDate ? ($validated['ends_at'] ?? null) : null,
            'repeat_every' => $validated['repeat_every'] ?? null,
            'repeat_unit' => $validated['repeat_unit'] ?? null,
            'primary_parent' => $validated['primary_parent'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync children if provided
        if (isset($validated['children'])) {
            $schedule->children()->sync($validated['children']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully!',
                'schedule' => $schedule->load('children'),
            ]);
        }

        return redirect()->route('coparenting.calendar')
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * Delete a schedule.
     */
    public function deleteSchedule(CoparentingSchedule $schedule)
    {
        // Ensure schedule belongs to current tenant
        abort_unless($schedule->tenant_id === auth()->user()->tenant_id, 403);

        $schedule->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully!',
            ]);
        }

        return redirect()->route('coparenting.calendar')
            ->with('success', 'Schedule deleted successfully!');
    }

    /**
     * Add a custom time block to a schedule.
     */
    public function addScheduleBlock(Request $request, CoparentingSchedule $schedule)
    {
        // Ensure schedule belongs to current tenant
        abort_unless($schedule->tenant_id === auth()->user()->tenant_id, 403);

        $validated = $request->validate([
            'parent_role' => 'required|string|in:mother,father',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $block = CoparentingScheduleBlock::create([
            'schedule_id' => $schedule->id,
            'parent_role' => $validated['parent_role'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Time block added successfully!',
                'block' => $block,
            ]);
        }

        return redirect()->route('coparenting.calendar')
            ->with('success', 'Time block added successfully!');
    }

    // ==================== ACTIVITIES ====================

    /**
     * Display the activities page.
     */
    public function activities(Request $request): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get activities from user's own tenant
        $ownActivities = CoparentingActivity::forCurrentTenant()
            ->with('children')
            ->get();

        // Get activities from tenants where user is a co-parent (viewing owner's activities)
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->pluck('tenant_id');

        $sharedActivities = collect();
        if ($coparentAccess->isNotEmpty()) {
            $sharedActivities = CoparentingActivity::whereIn('tenant_id', $coparentAccess)
                ->with('children')
                ->get();
        }

        // Get activities from co-parents' tenants (owner viewing co-parent's activities)
        $coparentUsers = Collaborator::forCurrentTenant()
            ->where('coparenting_enabled', true)
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        foreach ($coparentUsers as $coparent) {
            if ($coparent->user) {
                $coparentActivities = CoparentingActivity::where('tenant_id', $coparent->user->tenant_id)
                    ->with('children')
                    ->get();
                $sharedActivities = $sharedActivities->merge($coparentActivities);
            }
        }

        // Merge activities and remove duplicates
        $activities = $ownActivities->merge($sharedActivities)->unique('id')->sortByDesc('starts_at');

        $upcomingActivities = $activities->filter(function($activity) {
            return $activity->starts_at >= now();
        })->sortBy('starts_at')->take(5);

        // Get children for the form
        $ownChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get children user has co-parent access to
        $coparentChildAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with('coparentChildren')
            ->get();

        $sharedChildren = collect();
        foreach ($coparentChildAccess as $collaborator) {
            foreach ($collaborator->coparentChildren as $child) {
                $sharedChildren->push($child);
            }
        }

        $children = $ownChildren->merge($sharedChildren)->unique('id');

        $activityColors = CoparentingActivity::COLORS;
        $recurrenceFrequencies = CoparentingActivity::RECURRENCE_FREQUENCIES;
        $reminderTypes = CoparentingActivity::REMINDER_TYPES;

        // Calendar data
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $currentDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Get calendar events for the month
        $calendarEvents = [];
        foreach ($activities as $activity) {
            $occurrences = $activity->generateOccurrences($startOfMonth, $endOfMonth);
            foreach ($occurrences as $occurrence) {
                $eventDate = Carbon::parse($occurrence['start'])->format('Y-m-d');
                if (!isset($calendarEvents[$eventDate])) {
                    $calendarEvents[$eventDate] = [];
                }
                $calendarEvents[$eventDate][] = $occurrence;
            }
        }

        return view('pages.coparenting.activities', compact(
            'activities',
            'upcomingActivities',
            'children',
            'activityColors',
            'recurrenceFrequencies',
            'reminderTypes',
            'currentDate',
            'calendarEvents'
        ));
    }

    /**
     * Get activity events for calendar AJAX.
     */
    public function activityEvents(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()));

        $activities = CoparentingActivity::forCurrentTenant()->get();
        $events = [];

        foreach ($activities as $activity) {
            $occurrences = $activity->generateOccurrences($start, $end);
            $events = array_merge($events, $occurrences);
        }

        return response()->json($events);
    }

    /**
     * Store a new activity.
     */
    public function storeActivity(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_frequency' => 'nullable|string|in:day,week,month',
            'recurrence_end_type' => 'nullable|string|in:never,after,on',
            'recurrence_end_after' => 'nullable|integer|min:1',
            'recurrence_end_on' => 'nullable|date',
            'reminder_type' => 'required|string|in:default,custom,none',
            'reminder_minutes' => 'nullable|integer|min:1',
            'color' => 'nullable|string',
            'children' => 'nullable|array',
            'children.*' => 'exists:family_members,id',
        ]);

        $user = auth()->user();

        $activity = CoparentingActivity::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_all_day' => $validated['is_all_day'] ?? false,
            'is_recurring' => $validated['is_recurring'] ?? false,
            'recurrence_frequency' => $validated['recurrence_frequency'] ?? null,
            'recurrence_end_type' => $validated['recurrence_end_type'] ?? null,
            'recurrence_end_after' => $validated['recurrence_end_after'] ?? null,
            'recurrence_end_on' => $validated['recurrence_end_on'] ?? null,
            'reminder_type' => $validated['reminder_type'],
            'reminder_minutes' => $validated['reminder_type'] === 'custom' ? ($validated['reminder_minutes'] ?? 60) : 60,
            'color' => $validated['color'] ?? 'blue',
        ]);

        if (!empty($validated['children'])) {
            $activity->children()->attach($validated['children']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity created successfully!',
                'activity' => $activity->load('children'),
            ]);
        }

        return redirect()->route('coparenting.activities')
            ->with('success', 'Activity created successfully!');
    }

    /**
     * Update an activity.
     */
    public function updateActivity(Request $request, CoparentingActivity $activity)
    {
        abort_unless($activity->tenant_id === auth()->user()->tenant_id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'is_all_day' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_frequency' => 'nullable|string|in:day,week,month',
            'recurrence_end_type' => 'nullable|string|in:never,after,on',
            'recurrence_end_after' => 'nullable|integer|min:1',
            'recurrence_end_on' => 'nullable|date',
            'reminder_type' => 'required|string|in:default,custom,none',
            'reminder_minutes' => 'nullable|integer|min:1',
            'color' => 'nullable|string',
            'children' => 'nullable|array',
            'children.*' => 'exists:family_members,id',
        ]);

        $activity->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_all_day' => $validated['is_all_day'] ?? false,
            'is_recurring' => $validated['is_recurring'] ?? false,
            'recurrence_frequency' => $validated['recurrence_frequency'] ?? null,
            'recurrence_end_type' => $validated['recurrence_end_type'] ?? null,
            'recurrence_end_after' => $validated['recurrence_end_after'] ?? null,
            'recurrence_end_on' => $validated['recurrence_end_on'] ?? null,
            'reminder_type' => $validated['reminder_type'],
            'reminder_minutes' => $validated['reminder_type'] === 'custom' ? ($validated['reminder_minutes'] ?? 60) : 60,
            'color' => $validated['color'] ?? 'blue',
        ]);

        if (isset($validated['children'])) {
            $activity->children()->sync($validated['children']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully!',
                'activity' => $activity->load('children'),
            ]);
        }

        return redirect()->route('coparenting.activities')
            ->with('success', 'Activity updated successfully!');
    }

    /**
     * Delete an activity.
     */
    public function deleteActivity(CoparentingActivity $activity)
    {
        abort_unless($activity->tenant_id === auth()->user()->tenant_id, 403);

        $activity->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully!',
            ]);
        }

        return redirect()->route('coparenting.activities')
            ->with('success', 'Activity deleted successfully!');
    }

    // ==================== ACTUAL TIME TRACKING ====================

    /**
     * Display the actual time tracking page.
     */
    public function actualTime(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();
        $currentMonth = now();
        $year = request('year', $currentMonth->year);
        $month = request('month', $currentMonth->month);

        // Get children with co-parenting enabled
        $children = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        $selectedChildId = request('child_id', $children->first()?->id);

        // Get actual time records for the month
        $checkins = CoparentingActualTime::forCurrentTenant()
            ->forMonth($year, $month)
            ->when($selectedChildId, fn($q) => $q->forChild($selectedChildId))
            ->with('child')
            ->orderBy('date', 'asc')
            ->get();

        // Calculate statistics
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $stats = CoparentingActualTime::calculateStats(
            $user->tenant_id,
            $selectedChildId,
            $monthStart,
            $monthEnd
        );

        // Get schedule events for comparison
        $schedules = CoparentingSchedule::forCurrentTenant()->active()->current()->get();
        $plannedEvents = [];
        foreach ($schedules as $schedule) {
            $plannedEvents = array_merge($plannedEvents, $schedule->generateEventsForRange($monthStart, $monthEnd));
        }

        // Compare actual vs planned
        $comparison = CoparentingActualTime::compareWithSchedule(
            $user->tenant_id,
            $selectedChildId,
            $monthStart,
            $monthEnd,
            $plannedEvents
        );

        // Generate calendar days for the month
        $calendarDays = [];
        $currentDate = $monthStart->copy();
        while ($currentDate->lte($monthEnd)) {
            $dayCheckin = $checkins->firstWhere('date', $currentDate->format('Y-m-d'));
            $calendarDays[] = [
                'date' => $currentDate->copy(),
                'checkin' => $dayCheckin,
                'is_today' => $currentDate->isToday(),
                'is_past' => $currentDate->isPast() && !$currentDate->isToday(),
            ];
            $currentDate->addDay();
        }

        return view('pages.coparenting.actual-time', compact(
            'children',
            'selectedChildId',
            'checkins',
            'stats',
            'comparison',
            'calendarDays',
            'year',
            'month',
            'monthStart',
            'monthEnd'
        ));
    }

    /**
     * Get actual time statistics.
     */
    public function actualTimeStats(Request $request): JsonResponse
    {
        $user = auth()->user();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $childId = $request->get('child_id');

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $stats = CoparentingActualTime::calculateStats(
            $user->tenant_id,
            $childId,
            $monthStart,
            $monthEnd
        );

        return response()->json($stats);
    }

    /**
     * Store actual time check-in.
     */
    public function storeActualTime(Request $request)
    {
        $validated = $request->validate([
            'family_member_id' => 'required|exists:family_members,id',
            'date' => 'required|date',
            'parent_role' => 'required|string|in:mother,father',
            'is_full_day' => 'boolean',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        // Check for existing record
        $existing = CoparentingActualTime::where('tenant_id', $user->tenant_id)
            ->where('family_member_id', $validated['family_member_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existing) {
            $existing->update([
                'parent_role' => $validated['parent_role'],
                'is_full_day' => $validated['is_full_day'] ?? true,
                'check_in_time' => $validated['check_in_time'] ?? null,
                'check_out_time' => $validated['check_out_time'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            $checkin = $existing;
        } else {
            $checkin = CoparentingActualTime::create([
                'tenant_id' => $user->tenant_id,
                'checked_by' => $user->id,
                'family_member_id' => $validated['family_member_id'],
                'date' => $validated['date'],
                'parent_role' => $validated['parent_role'],
                'is_full_day' => $validated['is_full_day'] ?? true,
                'check_in_time' => $validated['check_in_time'] ?? null,
                'check_out_time' => $validated['check_out_time'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in recorded successfully!',
                'checkin' => $checkin,
            ]);
        }

        return redirect()->route('coparenting.actual-time')
            ->with('success', 'Check-in recorded successfully!');
    }

    /**
     * Update actual time check-in.
     */
    public function updateActualTime(Request $request, CoparentingActualTime $checkin)
    {
        abort_unless($checkin->tenant_id === auth()->user()->tenant_id, 403);

        $validated = $request->validate([
            'parent_role' => 'required|string|in:mother,father',
            'is_full_day' => 'boolean',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'notes' => 'nullable|string|max:500',
        ]);

        $checkin->update([
            'parent_role' => $validated['parent_role'],
            'is_full_day' => $validated['is_full_day'] ?? true,
            'check_in_time' => $validated['check_in_time'] ?? null,
            'check_out_time' => $validated['check_out_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in updated successfully!',
                'checkin' => $checkin,
            ]);
        }

        return redirect()->route('coparenting.actual-time')
            ->with('success', 'Check-in updated successfully!');
    }

    /**
     * Delete actual time check-in.
     */
    public function deleteActualTime(CoparentingActualTime $checkin)
    {
        abort_unless($checkin->tenant_id === auth()->user()->tenant_id, 403);

        $checkin->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in deleted successfully!',
            ]);
        }

        return redirect()->route('coparenting.actual-time')
            ->with('success', 'Check-in deleted successfully!');
    }

    // ==================== PLACEHOLDER PAGES ====================

    /**
     * Display the child info placeholder.
     */
    public function childInfo(): View
    {
        session(['coparenting_mode' => true]);

        // Get children with co-parenting enabled
        $children = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        return view('pages.coparenting.placeholders.child-info', compact('children'));
    }

    /**
     * Display the messages placeholder.
     */
    public function messages(): View
    {
        session(['coparenting_mode' => true]);
        return view('pages.coparenting.placeholders.messages');
    }

    /**
     * Display the co-parenting expenses page.
     */
    public function expenses(Request $request): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get children with co-parenting enabled from user's own tenant
        $ownChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get children the user has co-parent access to (from other tenants)
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with('coparentChildren')
            ->get();

        $sharedChildren = collect();
        foreach ($coparentAccess as $collaborator) {
            foreach ($collaborator->coparentChildren as $child) {
                $sharedChildren->push($child);
            }
        }

        // Merge own children and shared children, avoiding duplicates
        $children = $ownChildren->merge($sharedChildren)->unique('id');
        $childIds = $children->pluck('id')->toArray();

        // Get filter parameter
        $childFilter = $request->get('child_id', 'all');

        // Get shared budget transactions for these children
        $transactionsQuery = \App\Models\BudgetTransaction::where('is_shared', true)
            ->whereIn('shared_for_child_id', $childIds)
            ->with(['category', 'creator', 'budget', 'sharedForChild']);

        // Apply child filter
        if ($childFilter !== 'all' && is_numeric($childFilter)) {
            $transactionsQuery->where('shared_for_child_id', (int) $childFilter);
        }

        $transactions = $transactionsQuery->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Calculate totals
        $totalExpenses = \App\Models\BudgetTransaction::where('is_shared', true)
            ->whereIn('shared_for_child_id', $childIds)
            ->where('type', 'expense')
            ->when($childFilter !== 'all' && is_numeric($childFilter), fn($q) => $q->where('shared_for_child_id', (int) $childFilter))
            ->sum('amount');

        // Group by child for summary
        $expensesByChild = \App\Models\BudgetTransaction::where('is_shared', true)
            ->whereIn('shared_for_child_id', $childIds)
            ->where('type', 'expense')
            ->selectRaw('shared_for_child_id, SUM(amount) as total')
            ->groupBy('shared_for_child_id')
            ->pluck('total', 'shared_for_child_id');

        return view('pages.coparenting.expenses', compact(
            'children',
            'transactions',
            'totalExpenses',
            'expensesByChild',
            'childFilter'
        ));
    }

    /**
     * Display the parenting plan placeholder.
     */
    public function parentingPlan(): View
    {
        session(['coparenting_mode' => true]);
        return view('pages.coparenting.placeholders.parenting-plan');
    }

    /**
     * Display the actual time placeholder.
     */
    public function actualTimePlaceholder(): View
    {
        session(['coparenting_mode' => true]);
        return view('pages.coparenting.placeholders.actual-time');
    }
}
