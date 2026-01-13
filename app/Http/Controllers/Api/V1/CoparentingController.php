<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\CoparentChild;
use App\Models\CoparentConversation;
use App\Models\CoparentingActivity;
use App\Models\CoparentingActualTime;
use App\Models\CoparentingSchedule;
use App\Models\CoparentMessage;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoparentingController extends Controller
{
    /**
     * Get co-parenting dashboard data.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get children with co-parenting enabled from user's own tenant
        $ownChildren = FamilyMember::where('tenant_id', $tenant->id)
            ->minors()
            ->where('co_parenting_enabled', true)
            ->with(['coparents.user'])
            ->get();

        // Get children the user has co-parent access to (from other tenants)
        // Note: Don't filter by is_active here - let the user see their coparenting access even if deactivated
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with(['coparentChildren' => function($query) {
                $query->with(['coparents.user']);
            }, 'inviter'])
            ->get();

        // Combine children - own children + children from co-parent access
        $sharedChildren = collect();
        foreach ($coparentAccess as $collaborator) {
            // coparentChildren returns FamilyMember objects directly
            foreach ($collaborator->coparentChildren as $child) {
                $child->other_parent_name = $collaborator->inviter->name ?? 'Unknown';
                $child->is_shared = true;
                $sharedChildren->push($child);
            }
        }

        // Merge own children and shared children
        $allChildren = $ownChildren->merge($sharedChildren)->unique('id');

        // Transform children for mobile
        $children = $allChildren->map(function ($child) use ($user) {
            // Get coparents for this child (other co-parents, not the current user)
            $childCoparents = collect();

            // Add the current user first (as "You")
            $childCoparents->push([
                'id' => 0, // Special ID for current user
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'parent_role' => 'parent',
                'parent_role_label' => 'You',
                'is_you' => true,
            ]);

            // Add other coparents
            foreach ($child->coparents as $coparent) {
                $childCoparents->push([
                    'id' => $coparent->id,
                    'name' => $coparent->user->name ?? 'Unknown',
                    'email' => $coparent->user->email ?? null,
                    'avatar' => $coparent->user->avatar ?? null,
                    'parent_role' => $coparent->parent_role,
                    'parent_role_label' => Collaborator::PARENT_ROLES[$coparent->parent_role]['label'] ?? 'Parent',
                    'is_you' => false,
                ]);
            }

            return [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'full_name' => $child->first_name . ' ' . $child->last_name,
                'nickname' => $child->nickname,
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'age' => $child->date_of_birth ? $child->date_of_birth->age : null,
                'gender' => $child->gender,
                'avatar' => $child->avatar,
                'is_shared' => $child->is_shared ?? false,
                'other_parent_name' => $child->other_parent_name ?? null,
                'coparents' => $childCoparents,
            ];
        });

        // Get pending co-parent invites sent by this user
        $pendingInvites = CollaboratorInvite::where('tenant_id', $tenant->id)
            ->where('is_coparent_invite', true)
            ->where('status', 'pending')
            ->where('invited_by', $user->id)
            ->with('familyMembers')
            ->get()
            ->map(function ($invite) {
                return [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'first_name' => $invite->first_name,
                    'last_name' => $invite->last_name,
                    'parent_role' => $invite->parent_role,
                    'expires_at' => $invite->expires_at?->format('Y-m-d'),
                    'children' => $invite->familyMembers->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $m->first_name . ' ' . $m->last_name,
                    ]),
                ];
            });

        // Get active co-parents for this tenant
        $coparents = Collaborator::where('tenant_id', $tenant->id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->with(['user', 'coparentChildren'])
            ->get()
            ->map(function ($coparent) {
                return [
                    'id' => $coparent->id,
                    'name' => $coparent->user->name ?? 'Unknown',
                    'email' => $coparent->user->email ?? null,
                    'parent_role' => $coparent->parent_role,
                    'avatar' => $coparent->user->avatar ?? null,
                    'children_count' => $coparent->coparentChildren->count(),
                    // coparentChildren returns FamilyMember directly (BelongsToMany)
                    'children' => $coparent->coparentChildren->map(fn($child) => [
                        'id' => $child->id,
                        'name' => $child->first_name . ' ' . $child->last_name,
                    ]),
                ];
            });

        // Check if user is viewing as a co-parent (not tenant owner)
        $isCoparent = $coparentAccess->isNotEmpty();

        return $this->success([
            'children' => $children,
            'pending_invites' => $pendingInvites,
            'coparents' => $coparents,
            'is_coparent' => $isCoparent,
            'stats' => [
                'total_children' => $children->count(),
                'total_coparents' => $coparents->count(),
                'pending_invites' => $pendingInvites->count(),
            ],
        ]);
    }

    /**
     * Get list of co-parented children.
     */
    public function children(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get own children with co-parenting enabled
        $ownChildren = FamilyMember::where('tenant_id', $tenant->id)
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get children via co-parent access
        // coparentChildren returns FamilyMember directly (BelongsToMany relationship)
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->with(['coparentChildren'])
            ->get();

        $sharedChildren = collect();
        foreach ($coparentAccess as $collaborator) {
            foreach ($collaborator->coparentChildren as $child) {
                $sharedChildren->push($child);
            }
        }

        $allChildren = $ownChildren->merge($sharedChildren)->unique('id');

        $children = $allChildren->map(function ($child) {
            return [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'full_name' => $child->first_name . ' ' . $child->last_name,
                'nickname' => $child->nickname,
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'age' => $child->date_of_birth ? $child->date_of_birth->age : null,
                'gender' => $child->gender,
                'avatar' => $child->avatar,
                'blood_type' => $child->blood_type,
            ];
        });

        return $this->success([
            'children' => $children,
        ]);
    }

    /**
     * Get a specific child's details.
     */
    public function showChild(Request $request, FamilyMember $child): JsonResponse
    {
        $user = $request->user();

        // Check access
        if (!$this->userHasAccessToChild($user, $child)) {
            return $this->forbidden('You do not have access to this child');
        }

        // Load related medical information
        $child->load(['allergies', 'medicalConditions', 'medications']);

        // Format allergies as comma-separated string
        $allergiesText = $child->allergies->isNotEmpty()
            ? $child->allergies->pluck('allergen_name')->filter()->join(', ')
            : null;

        // Format medical conditions as comma-separated string
        $conditionsText = $child->medicalConditions->isNotEmpty()
            ? $child->medicalConditions->pluck('condition_name')->filter()->join(', ')
            : null;

        // Format medications as comma-separated string
        $medicationsText = $child->medications->isNotEmpty()
            ? $child->medications->pluck('medication_name')->filter()->join(', ')
            : null;

        return $this->success([
            'child' => [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'full_name' => $child->first_name . ' ' . $child->last_name,
                'nickname' => $child->nickname,
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'age' => $child->date_of_birth ? $child->date_of_birth->age : null,
                'gender' => $child->gender,
                'avatar' => $child->avatar,
                'blood_type' => $child->blood_type,
                'allergies' => $allergiesText,
                'medical_conditions' => $conditionsText,
                'medications' => $medicationsText,
                'school_name' => $child->school_name,
                'grade' => $child->grade,
                'notes' => $child->notes,
            ],
        ]);
    }

    /**
     * Get schedule/calendar events.
     */
    public function schedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $start = $request->get('start', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end', Carbon::now()->endOfMonth()->toDateString());

        // Get active schedule
        $schedule = CoparentingSchedule::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with('children')
            ->first();

        if (!$schedule) {
            return $this->success([
                'schedule' => null,
                'events' => [],
            ]);
        }

        // Generate events for date range
        $events = $schedule->generateEventsForRange(
            Carbon::parse($start),
            Carbon::parse($end)
        );

        return $this->success([
            'schedule' => [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'template_type' => $schedule->template_type,
                'begins_at' => $schedule->begins_at?->format('Y-m-d'),
                'ends_at' => $schedule->ends_at?->format('Y-m-d'),
                'primary_parent' => $schedule->primary_parent,
            ],
            'events' => $events,
        ]);
    }

    /**
     * Get shared activities.
     */
    public function activities(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $start = $request->get('start', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end', Carbon::now()->endOfMonth()->toDateString());

        // Get activities
        $activities = CoparentingActivity::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('starts_at', [$start, $end])
                    ->orWhere('is_recurring', true);
            })
            ->with('children')
            ->orderBy('starts_at')
            ->get();

        // Transform and expand recurring activities
        $events = collect();
        foreach ($activities as $activity) {
            if ($activity->is_recurring) {
                $occurrences = $activity->generateOccurrences(
                    Carbon::parse($start),
                    Carbon::parse($end)
                );
                foreach ($occurrences as $occurrence) {
                    $events->push([
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'start' => $occurrence['start'],
                        'end' => $occurrence['end'],
                        'is_all_day' => $activity->is_all_day,
                        'color' => $activity->color,
                        'is_recurring' => true,
                        'children' => $activity->children->map(fn($c) => [
                            'id' => $c->id,
                            'name' => $c->first_name . ' ' . $c->last_name,
                        ]),
                    ]);
                }
            } else {
                $events->push([
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'start' => $activity->starts_at?->toIso8601String(),
                    'end' => $activity->ends_at?->toIso8601String(),
                    'is_all_day' => $activity->is_all_day,
                    'color' => $activity->color,
                    'is_recurring' => false,
                    'children' => $activity->children->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->first_name . ' ' . $c->last_name,
                    ]),
                ]);
            }
        }

        // Get upcoming activities
        $upcoming = CoparentingActivity::where('tenant_id', $tenant->id)
            ->where('starts_at', '>=', Carbon::now())
            ->where('starts_at', '<=', Carbon::now()->addDays(7))
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'starts_at' => $activity->starts_at?->format('M d, Y H:i'),
                    'is_all_day' => $activity->is_all_day,
                    'color' => $activity->color,
                ];
            });

        return $this->success([
            'events' => $events->sortBy('start')->values(),
            'upcoming' => $upcoming,
        ]);
    }

    /**
     * Get list of conversations.
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = CoparentConversation::forUser($user->id)
            ->with(['child.familyMember', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->recent()
            ->get()
            ->map(function ($conversation) use ($user) {
                $lastMessage = $conversation->messages->first();
                return [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'child' => $conversation->child?->familyMember ? [
                        'id' => $conversation->child->familyMember->id,
                        'name' => $conversation->child->familyMember->first_name . ' ' . $conversation->child->familyMember->last_name,
                    ] : null,
                    'last_message' => $lastMessage ? [
                        'content' => \Illuminate\Support\Str::limit($lastMessage->content, 50),
                        'sender_name' => $lastMessage->sender?->name ?? 'Unknown',
                        'created_at' => $lastMessage->created_at?->diffForHumans(),
                    ] : null,
                    'unread_count' => $conversation->unreadCountFor($user->id),
                    'last_message_at' => $conversation->last_message_at?->diffForHumans(),
                ];
            });

        return $this->success([
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get a specific conversation with messages.
     */
    public function showConversation(Request $request, CoparentConversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is a participant
        if (!$conversation->isParticipant($user->id)) {
            return $this->forbidden('You do not have access to this conversation');
        }

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get()
            ->each(function ($message) use ($user, $request) {
                $message->reads()->create([
                    'user_id' => $user->id,
                    'read_at' => now(),
                    'ip_address' => $request->ip(),
                ]);
            });

        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'category' => $message->category,
                    'sender' => [
                        'id' => $message->sender?->id,
                        'name' => $message->sender?->name ?? 'Unknown',
                        'avatar' => $message->sender?->avatar,
                    ],
                    'is_own' => $message->sender_id === $user->id,
                    'was_edited' => $message->wasEdited(),
                    'attachments' => $message->attachments->map(fn($a) => [
                        'id' => $a->id,
                        'filename' => $a->original_filename,
                        'mime_type' => $a->mime_type,
                        'size' => $a->size,
                    ]),
                    'created_at' => $message->created_at?->format('M d, Y H:i'),
                    'created_at_diff' => $message->created_at?->diffForHumans(),
                ];
            });

        return $this->success([
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'child' => $conversation->child?->familyMember ? [
                    'id' => $conversation->child->familyMember->id,
                    'name' => $conversation->child->familyMember->first_name . ' ' . $conversation->child->familyMember->last_name,
                ] : null,
                'participants' => $conversation->getParticipants()->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'avatar' => $p->avatar,
                ]),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, CoparentConversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is a participant
        if (!$conversation->isParticipant($user->id)) {
            return $this->forbidden('You do not have access to this conversation');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'category' => 'nullable|in:general,schedule,medical,expense,emergency',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'general',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $conversation->touchLastMessage();

        return $this->success([
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'category' => $message->category,
                'sender' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ],
                'is_own' => true,
                'created_at' => $message->created_at?->format('M d, Y H:i'),
            ],
        ], 'Message sent successfully', 201);
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $validated = $request->validate([
            'child_id' => 'required|integer',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => 'nullable|in:general,schedule,medical,expense,emergency',
        ]);

        // Find the CoparentChild record
        $coparentChild = CoparentChild::where('family_member_id', $validated['child_id'])->first();

        if (!$coparentChild) {
            return $this->error('Child not found or not set up for co-parenting', 404);
        }

        // Create conversation
        $conversation = CoparentConversation::create([
            'tenant_id' => $tenant->id,
            'child_id' => $coparentChild->id,
            'subject' => $validated['subject'],
            'last_message_at' => now(),
        ]);

        // Create first message
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => $validated['message'],
            'category' => $validated['category'] ?? 'general',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
            ],
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
            ],
        ], 'Conversation created successfully', 201);
    }

    /**
     * Get actual time tracking data.
     */
    public function actualTime(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        // Get children with co-parenting enabled
        $children = FamilyMember::where('tenant_id', $tenant->id)
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get check-ins for the month
        $checkIns = CoparentingActualTime::where('tenant_id', $tenant->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($checkIn) {
                return [
                    'id' => $checkIn->id,
                    'date' => $checkIn->date?->format('Y-m-d'),
                    'parent_role' => $checkIn->parent_role,
                    'check_in_time' => $checkIn->check_in_time,
                    'check_out_time' => $checkIn->check_out_time,
                    'is_full_day' => $checkIn->is_full_day,
                    'notes' => $checkIn->notes,
                    'child_id' => $checkIn->family_member_id,
                ];
            });

        // Calculate stats
        $motherDays = $checkIns->where('parent_role', 'mother')->count();
        $fatherDays = $checkIns->where('parent_role', 'father')->count();
        $totalDays = $motherDays + $fatherDays;

        return $this->success([
            'check_ins' => $checkIns,
            'children' => $children->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->first_name . ' ' . $c->last_name,
            ]),
            'stats' => [
                'month' => $startDate->format('F Y'),
                'mother_days' => $motherDays,
                'father_days' => $fatherDays,
                'mother_percentage' => $totalDays > 0 ? round(($motherDays / $totalDays) * 100) : 0,
                'father_percentage' => $totalDays > 0 ? round(($fatherDays / $totalDays) * 100) : 0,
            ],
        ]);
    }

    /**
     * Check if user has access to a child.
     */
    private function userHasAccessToChild($user, FamilyMember $child): bool
    {
        // Owner has access to own children
        if ($child->tenant_id === $user->tenant_id && $child->co_parenting_enabled) {
            return true;
        }

        // Check co-parent access
        $hasCoparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->whereHas('coparentChildren', function ($q) use ($child) {
                $q->where('family_member_id', $child->id);
            })
            ->exists();

        return $hasCoparentAccess;
    }
}
