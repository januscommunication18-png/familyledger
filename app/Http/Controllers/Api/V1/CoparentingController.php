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
        try {
            $user = $request->user();

            // Check access and determine if owner or co-parent
            $isOwner = $child->tenant_id === $user->tenant_id;
            $collaborator = null;
            $coparentChild = null;

            if (!$isOwner) {
                // Check co-parent access
                $collaborator = Collaborator::where('user_id', $user->id)
                    ->where('coparenting_enabled', true)
                    ->whereHas('coparentChildren', function ($q) use ($child) {
                        $q->where('family_member_id', $child->id);
                    })
                    ->first();

                if (!$collaborator) {
                    return $this->forbidden('You do not have access to this child');
                }

                // Get the pivot with permissions
                $coparentChild = CoparentChild::where('collaborator_id', $collaborator->id)
                    ->where('family_member_id', $child->id)
                    ->first();
            }

            // Helper to check permission level
            $canView = function ($category) use ($isOwner, $coparentChild) {
                if ($isOwner) return true;
                if (!$coparentChild) return false;
                return $coparentChild->canView($category);
            };

            // Helper to check edit permission
            $canEdit = function ($category) use ($isOwner, $coparentChild) {
                if ($isOwner) return true;
                if (!$coparentChild) return false;
                return $coparentChild->canEdit($category);
            };

            // Helper to get permission level string ('none', 'view', 'edit')
            $getPermissionLevel = function ($category) use ($isOwner, $coparentChild) {
                if ($isOwner) return 'edit'; // Owner always has full edit access
                if (!$coparentChild) return 'none';
                return $coparentChild->getPermission($category);
            };

            // Load all related data
            $child->load([
                'allergies',
                'medicalConditions',
                'medications',
                'healthcareProviders',
                'contacts', // For emergency contacts
                'schoolInfo',
                'medicalInfo',
                'coparents.user',
                'insurancePolicies',
                'assets',
                'documents', // For drivers_license, passport, etc. accessors
            ]);

            // Build response based on permissions
            $childData = [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'full_name' => $child->first_name . ' ' . $child->last_name,
                'initials' => strtoupper(substr($child->first_name ?? '', 0, 1) . substr($child->last_name ?? '', 0, 1)),
                'profile_image_url' => $child->profile_image_url,
                'is_owner' => $isOwner,
                'is_coparent' => !$isOwner,
            ];

            // Basic Info (always visible for minimal context)
            $childData['date_of_birth'] = $child->date_of_birth?->format('Y-m-d');
            $childData['age'] = $child->date_of_birth ? $child->date_of_birth->age : null;
            $childData['gender'] = $child->gender;

            // Permission-based data - now returns level ('none', 'view', 'edit') instead of boolean
            $permissions = [];

            // Basic Info permission
            if ($canView('basic_info')) {
                $permissions['basic_info'] = $getPermissionLevel('basic_info');
                $childData['nickname'] = $child->nickname;
                $childData['blood_type'] = $child->medicalInfo?->blood_type ?? $child->blood_type;
                $childData['immigration_status'] = $child->immigration_status_name;
                $childData['relationship'] = $child->relationship_name;
            } else {
                $permissions['basic_info'] = 'none';
            }

            // Medical Records permission
            if ($canView('medical_records')) {
                $permissions['medical_records'] = $getPermissionLevel('medical_records');
                $childData['medical_info'] = $child->medicalInfo ? [
                    'blood_type' => $child->medicalInfo->blood_type,
                    'primary_doctor' => $child->medicalInfo->primary_doctor,
                    'doctor_phone' => $child->medicalInfo->doctor_phone,
                    'insurance_provider' => $child->medicalInfo->insurance_provider,
                    'insurance_policy_number' => $child->medicalInfo->insurance_policy_number,
                ] : null;
                $childData['allergies'] = $child->allergies->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->allergen_name,
                    'severity' => $a->severity,
                    'reaction' => $a->reaction,
                ])->toArray();
                $childData['medical_conditions'] = $child->medicalConditions->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'diagnosed_date' => $c->diagnosed_date?->format('Y-m-d'),
                    'notes' => $c->notes,
                ])->toArray();
                $childData['medications'] = $child->medications->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'dosage' => $m->dosage,
                    'frequency' => $m->frequency,
                    'prescribing_doctor' => $m->prescribing_doctor ?? null,
                ])->toArray();
            } else {
                $permissions['medical_records'] = 'none';
            }

            // Healthcare Providers permission
            if ($canView('healthcare_providers')) {
                $permissions['healthcare_providers'] = $getPermissionLevel('healthcare_providers');
                $childData['healthcare_providers'] = $child->healthcareProviders->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'specialty' => $p->specialty,
                    'phone' => $p->phone,
                    'address' => $p->address,
                ])->toArray();
            } else {
                $permissions['healthcare_providers'] = 'none';
            }

            // Emergency Contacts permission
            if ($canView('emergency_contacts')) {
                $permissions['emergency_contacts'] = $getPermissionLevel('emergency_contacts');
                $childData['emergency_contacts'] = $child->emergencyContacts->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'relationship' => $c->relationship,
                    'phone' => $c->phone,
                    'email' => $c->email,
                    'is_primary' => $c->priority === 1,
                ])->toArray();
            } else {
                $permissions['emergency_contacts'] = 'none';
            }

            // School Info permission
            if ($canView('school_info')) {
                $permissions['school_info'] = $getPermissionLevel('school_info');
                $childData['school_info'] = $child->schoolInfo ? [
                    'school_name' => $child->schoolInfo->school_name,
                    'grade' => $child->schoolInfo->grade_level,
                    'teacher_name' => $child->schoolInfo->teacher_name,
                    'school_phone' => $child->schoolInfo->school_phone,
                    'school_address' => $child->schoolInfo->school_address,
                    'bus_number' => $child->schoolInfo->bus_number ?? null,
                ] : null;
            } else {
                $permissions['school_info'] = 'none';
            }

            // Documents permission
            if ($canView('documents')) {
                $permissions['documents'] = $getPermissionLevel('documents');
                $childData['documents'] = [
                    'drivers_license' => $child->drivers_license ? [
                        'document_number' => $child->drivers_license->document_number,
                        'expiry_date' => $child->drivers_license->expiry_date?->format('Y-m-d'),
                        'issue_date' => $child->drivers_license->issue_date?->format('Y-m-d'),
                        'issuing_state' => $child->drivers_license->state_of_issue,
                    ] : null,
                    'passport' => $child->passport ? [
                        'document_number' => $child->passport->document_number,
                        'expiry_date' => $child->passport->expiry_date?->format('Y-m-d'),
                        'issue_date' => $child->passport->issue_date?->format('Y-m-d'),
                        'issuing_country' => $child->passport->country_of_issue,
                    ] : null,
                    'social_security' => $child->social_security ? [
                        'last_four' => substr($child->social_security->document_number ?? '', -4),
                        'has_document' => true,
                    ] : null,
                    'birth_certificate' => $child->birth_certificate ? [
                        'document_number' => $child->birth_certificate->document_number,
                        'issue_date' => $child->birth_certificate->issue_date?->format('Y-m-d'),
                        'has_document' => true,
                    ] : null,
                ];
            } else {
                $permissions['documents'] = 'none';
            }

            // Insurance permission
            if ($canView('insurance')) {
                $permissions['insurance'] = $getPermissionLevel('insurance');
                $childData['insurance_policies'] = $child->insurancePolicies->map(fn($p) => [
                    'id' => $p->id,
                    'provider_name' => $p->provider_name,
                    'policy_number' => $p->policy_number,
                    'insurance_type' => $p->insurance_type,
                    'coverage_amount' => $p->premium_amount,
                    'expiry_date' => $p->expiration_date?->format('Y-m-d'),
                ])->toArray();
            } else {
                $permissions['insurance'] = 'none';
            }

            // Assets permission
            if ($canView('assets')) {
                $permissions['assets'] = $getPermissionLevel('assets');
                $childData['assets'] = $child->assets->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'category' => $a->asset_category,
                    'current_value' => $a->current_value,
                ])->toArray();
            } else {
                $permissions['assets'] = 'none';
            }

            // Co-parents (always visible to show who has access)
            $childData['coparents'] = $child->coparents->map(fn($cp) => [
                'id' => $cp->id,
                'name' => $cp->user->name ?? 'Unknown',
                'email' => $cp->user->email ?? null,
                'avatar_url' => $cp->user->avatar ?? null,
                'parent_role' => $cp->parent_role,
                'parent_role_label' => Collaborator::PARENT_ROLES[$cp->parent_role]['label'] ?? 'Parent',
            ])->toArray();

            // Quick stats
            $childData['stats'] = [
                'coparents_count' => $child->coparents->count(),
                'emergency_contacts_count' => $canView('emergency_contacts') ? $child->emergencyContacts->count() : null,
                'documents_count' => $canView('documents') ? (
                    ($child->drivers_license ? 1 : 0) +
                    ($child->passport ? 1 : 0) +
                    ($child->social_security ? 1 : 0) +
                    ($child->birth_certificate ? 1 : 0)
                ) : null,
                'allergies_count' => $canView('medical_records') ? $child->allergies->count() : null,
                'medications_count' => $canView('medical_records') ? $child->medications->count() : null,
            ];

            // Add a convenience flag to check if user can edit anything
            $canEditAny = $isOwner || ($coparentChild && count($coparentChild->getGrantedPermissions()) > 0
                && collect($coparentChild->getGrantedPermissions())->contains('edit'));

            return $this->success([
                'child' => $childData,
                'permissions' => $permissions,
                'can_edit' => $canEditAny, // Convenience flag for frontend
            ]);

        } catch (\Exception $e) {
            \Log::error('showChild error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return $this->error('Error loading child: ' . $e->getMessage(), 500);
        }
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
