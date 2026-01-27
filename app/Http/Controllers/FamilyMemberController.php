<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Services\CollaboratorPermissionService;
use App\Services\CoparentEditService;
use App\Models\MemberContact;
use App\Models\MemberDocument;
use App\Models\MemberEducationDocument;
use App\Models\MemberMedicalInfo;
use App\Models\MemberSchoolInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FamilyMemberController extends Controller
{
    /**
     * Show the form for creating a new family member.
     */
    public function create(FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Get existing family members for autocomplete suggestions (including owner)
        $owner = Auth::user();
        $existingMembers = FamilyMember::where('tenant_id', Auth::user()->tenant_id)
            ->select('first_name', 'last_name')
            ->get()
            ->map(fn($m) => trim($m->first_name . ' ' . $m->last_name))
            ->prepend($owner->name)
            ->unique()
            ->values()
            ->toArray();

        return view('family-circle.member.create', [
            'circle' => $familyCircle,
            'relationships' => FamilyMember::RELATIONSHIPS,
            'immigrationStatuses' => FamilyMember::IMMIGRATION_STATUSES,
            'existingMembers' => $existingMembers,
        ]);
    }

    /**
     * Store a newly created family member.
     */
    public function store(Request $request, FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Convert MM/DD/YYYY to Y-m-d format for validation
        if ($request->has('date_of_birth') && $request->date_of_birth) {
            $dateParts = explode('/', $request->date_of_birth);
            if (count($dateParts) === 3) {
                $request->merge([
                    'date_of_birth' => $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1]
                ]);
            }
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'date_of_birth' => 'required|date|before:today',
            'relationship' => 'required|string|in:' . implode(',', array_keys(FamilyMember::RELATIONSHIPS)),
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'is_minor' => 'boolean',
            'co_parenting_enabled' => 'boolean',
            'immigration_status' => 'nullable|string|in:' . implode(',', array_keys(FamilyMember::IMMIGRATION_STATUSES)),
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'profile_image.mimes' => 'Please upload a valid image (JPG, PNG, GIF, or WebP).',
        ]);

        $data = [
            'family_circle_id' => $familyCircle->id,
            'created_by' => Auth::id(),
            'tenant_id' => Auth::user()->tenant_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone_country_code' => $validated['phone_country_code'] ?? null,
            'date_of_birth' => $validated['date_of_birth'],
            'relationship' => $validated['relationship'],
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'is_minor' => $request->boolean('is_minor'),
            'co_parenting_enabled' => $request->boolean('co_parenting_enabled'),
            'immigration_status' => $validated['immigration_status'] ?? null,
        ];

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('family-ledger/members/profiles', 'do_spaces');
            $data['profile_image'] = $path;
        }

        $member = FamilyMember::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Family member added successfully',
                'member' => $member,
            ]);
        }

        return redirect()->route('family-circle.show', $familyCircle)
            ->with('success', 'Family member added successfully');
    }

    /**
     * Display the specified family member.
     */
    public function show(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasAccess()) {
            abort(403);
        }

        $access = $permissionService->forView();

        // For linked members (Self), load data from all linked member records
        if ($member->linked_user_id) {
            $linkedMemberIds = FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->pluck('id')
                ->toArray();

            // Load aggregated data from all linked members
            $member->setRelation('documents', \App\Models\MemberDocument::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('contacts', \App\Models\MemberContact::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('allergies', \App\Models\MemberAllergy::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('healthcareProviders', \App\Models\MemberHealthcareProvider::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('medications', \App\Models\MemberMedication::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('medicalConditions', \App\Models\MemberMedicalCondition::whereIn('family_member_id', $linkedMemberIds)->get());

            // Load medical info from any linked member that has it
            $medicalInfo = \App\Models\MemberMedicalInfo::whereIn('family_member_id', $linkedMemberIds)->first();
            $member->setRelation('medicalInfo', $medicalInfo);

            // Load school info from any linked member that has it
            $schoolInfo = \App\Models\MemberSchoolInfo::whereIn('family_member_id', $linkedMemberIds)->first();
            $member->setRelation('schoolInfo', $schoolInfo);

            // Load audit logs from all linked members
            $member->setRelation('auditLogs', \App\Models\MemberAuditLog::whereIn('family_member_id', $linkedMemberIds)
                ->with('user')
                ->latest()
                ->limit(20)
                ->get());

            // Load insurance policies from all linked members
            $insurancePolicies = \App\Models\InsurancePolicy::whereHas('coveredMembers', function ($query) use ($linkedMemberIds) {
                $query->whereIn('family_member_id', $linkedMemberIds);
            })->orWhereHas('policyholders', function ($query) use ($linkedMemberIds) {
                $query->whereIn('family_member_id', $linkedMemberIds);
            })->get();
            $member->setRelation('insurancePolicies', $insurancePolicies);

            // Load tax returns from all linked members
            $taxReturns = \App\Models\TaxReturn::whereHas('taxpayers', function ($query) use ($linkedMemberIds) {
                $query->whereIn('family_member_id', $linkedMemberIds);
            })->get();
            $member->setRelation('taxReturns', $taxReturns);

            // Load assets from all linked members
            $assets = \App\Models\Asset::whereHas('familyMemberOwners', function ($query) use ($linkedMemberIds) {
                $query->whereIn('family_member_id', $linkedMemberIds);
            })->get();
            $member->setRelation('assets', $assets);
        } else {
            $member->load([
                'documents',
                'medicalInfo',
                'schoolInfo',
                'contacts',
                'allergies',
                'healthcareProviders',
                'medications',
                'medicalConditions',
                'auditLogs' => function ($query) {
                    $query->with('user')->latest()->limit(20);
                },
                'insurancePolicies',
                'taxReturns',
                'assets',
            ]);
        }

        return view('family-circle.member.show', [
            'circle' => $familyCircle,
            'member' => $member,
            'access' => $access,
        ]);
    }

    /**
     * Show the form for editing the specified family member.
     */
    public function edit(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service - require full access to edit member profile
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasFullAccess()) {
            abort(403);
        }

        // Get all family circles for the current user's tenant with member counts
        $allCircles = FamilyCircle::where('tenant_id', Auth::user()->tenant_id)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('family-circle.member.edit', [
            'circle' => $familyCircle,
            'member' => $member,
            'relationships' => FamilyMember::RELATIONSHIPS,
            'immigrationStatuses' => FamilyMember::IMMIGRATION_STATUSES,
            'access' => $permissionService->forView(),
            'allCircles' => $allCircles,
        ]);
    }

    /**
     * Update the specified family member.
     */
    public function update(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow access if owner OR coparent with edit permission
        if (!$permissionService->hasFullAccess() && !$editService->needsApproval()) {
            abort(403);
        }

        // Convert MM/DD/YYYY to Y-m-d format for validation
        if ($request->has('date_of_birth') && $request->date_of_birth) {
            $dateParts = explode('/', $request->date_of_birth);
            if (count($dateParts) === 3) {
                $request->merge([
                    'date_of_birth' => $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1]
                ]);
            }
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'date_of_birth' => 'required|date|before:today',
            'relationship' => 'required|string|in:' . implode(',', array_keys(FamilyMember::RELATIONSHIPS)),
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'is_minor' => 'boolean',
            'co_parenting_enabled' => 'boolean',
            'immigration_status' => 'nullable|string|in:' . implode(',', array_keys(FamilyMember::IMMIGRATION_STATUSES)),
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'family_circle_id' => 'nullable|exists:family_circles,id',
        ], [
            'profile_image.mimes' => 'Please upload a valid image (JPG, PNG, GIF, or WebP).',
        ]);

        // Check if moving to a different circle (only owners can do this)
        $newCircleId = $validated['family_circle_id'] ?? null;
        $newCircle = null;
        if ($newCircleId && $newCircleId != $familyCircle->id) {
            // Coparents cannot move members to different circles
            if ($editService->needsApproval()) {
                return back()->withErrors(['family_circle_id' => 'You cannot move members to different circles.']);
            }
            // Verify user owns the new circle
            $newCircle = FamilyCircle::where('id', $newCircleId)
                ->where('tenant_id', Auth::user()->tenant_id)
                ->first();

            if (!$newCircle) {
                return back()->withErrors(['family_circle_id' => 'Invalid family circle selected.']);
            }
        }

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone_country_code' => $validated['phone_country_code'] ?? null,
            'date_of_birth' => $validated['date_of_birth'],
            'relationship' => $validated['relationship'],
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'is_minor' => $request->boolean('is_minor'),
            'co_parenting_enabled' => $request->boolean('co_parenting_enabled'),
            'immigration_status' => $validated['immigration_status'] ?? null,
        ];

        // If moving to a new circle, update the family_circle_id
        if ($newCircle) {
            $data['family_circle_id'] = $newCircle->id;
        }

        // Handle profile image
        $newProfileImage = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('family-ledger/members/profiles', 'do_spaces');
            $newProfileImage = $path;
            $data['profile_image'] = $path;
        }

        // If coparent, create pending edits for each changed field
        if ($editService->needsApproval()) {
            $pendingCount = 0;
            $fieldsToCheck = [
                'first_name', 'last_name', 'email', 'phone', 'phone_country_code',
                'date_of_birth', 'relationship', 'father_name', 'mother_name',
                'immigration_status'
            ];

            foreach ($fieldsToCheck as $field) {
                $oldValue = $member->$field;
                $newValue = $data[$field] ?? null;

                // Normalize for comparison
                $oldNormalized = is_null($oldValue) ? '' : (string) $oldValue;
                $newNormalized = is_null($newValue) ? '' : (string) $newValue;

                if ($oldNormalized !== $newNormalized) {
                    $editService->handleUpdate($member, $field, $newValue);
                    $pendingCount++;
                }
            }

            // Handle profile image separately
            if ($newProfileImage) {
                $editService->handleUpdate($member, 'profile_image', $newProfileImage);
                $pendingCount++;
            }

            if ($pendingCount === 0) {
                return back()->with('info', 'No changes detected.');
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => "{$pendingCount} edit(s) submitted for approval",
                ]);
            }

            return redirect()->route('family-circle.member.show', [$familyCircle, $member])
                ->with('info', "{$pendingCount} edit(s) submitted for owner approval.");
        }

        // Owner can update directly
        if ($newProfileImage && $member->profile_image) {
            // Delete old profile image
            Storage::disk('do_spaces')->delete($member->profile_image);
        }

        $member->update($data);

        // Sync data to all other "Self" members with same linked_user_id
        if ($member->linked_user_id) {
            $syncData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'phone_country_code' => $data['phone_country_code'],
                'date_of_birth' => $data['date_of_birth'],
            ];

            // If profile image was updated, sync it too
            if (isset($data['profile_image'])) {
                $syncData['profile_image'] = $data['profile_image'];
            }

            FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->where('id', '!=', $member->id)
                ->update($syncData);
        }

        // Determine which circle to redirect to
        $redirectCircle = $newCircle ?? $familyCircle;

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Family member updated successfully',
                'member' => $member,
            ]);
        }

        return redirect()->route('family-circle.member.show', [$redirectCircle, $member])
            ->with('success', 'Family member updated successfully');
    }

    /**
     * Remove the specified family member.
     */
    public function destroy(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service - require full access to delete member
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasFullAccess()) {
            abort(403);
        }

        // Delete profile image if exists
        if ($member->profile_image) {
            Storage::disk('do_spaces')->delete($member->profile_image);
        }

        // Delete document images
        foreach ($member->documents as $doc) {
            if ($doc->front_image) {
                Storage::disk('do_spaces')->delete($doc->front_image);
            }
            if ($doc->back_image) {
                Storage::disk('do_spaces')->delete($doc->back_image);
            }
        }

        $member->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Family member removed successfully',
            ]);
        }

        return redirect()->route('family-circle.show', $familyCircle)
            ->with('success', 'Family member removed successfully');
    }

    /**
     * Store medical info for a family member.
     */
    public function storeMedicalInfo(Request $request, FamilyMember $member)
    {
        // Use centralized permission service - require edit access for medical
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'medications' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'blood_type' => 'nullable|string|in:' . implode(',', array_keys(MemberMedicalInfo::BLOOD_TYPES)),
            'primary_physician' => 'nullable|string|max:255',
            'physician_phone' => 'nullable|string|max:20',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_group_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberMedicalInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Medical info saved successfully',
            ]);
        }

        return back()->with('success', 'Medical info saved successfully');
    }

    /**
     * Store school info for a family member.
     */
    public function storeSchoolInfo(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if has full access OR is coparent needing approval
        if (!$permissionService->hasFullAccess() && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|in:' . implode(',', array_keys(MemberSchoolInfo::GRADE_LEVELS)),
            'student_id' => 'nullable|string|max:100',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
            'bus_number' => 'nullable|string|max:50',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $member->tenant_id;
        $validated['family_member_id'] = $member->id;

        // If coparent, create pending edit
        if ($editService->needsApproval()) {
            $existingSchoolInfo = MemberSchoolInfo::where('family_member_id', $member->id)->first();

            if (!$existingSchoolInfo) {
                // New record - create pending
                $editService->handleCreate(MemberSchoolInfo::class, $validated);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'pending' => true,
                        'message' => 'School info submitted for owner approval',
                    ], 202);
                }
                return back()->with('info', 'School info submitted for owner approval.');
            }

            // Update existing - create pending edits for changed fields
            $pendingCount = 0;
            $fieldsToCheck = ['school_name', 'grade_level', 'student_id', 'school_address', 'school_phone', 'school_email', 'teacher_name', 'teacher_email', 'counselor_name', 'counselor_email', 'bus_number', 'bus_pickup_time', 'bus_dropoff_time', 'notes'];

            foreach ($fieldsToCheck as $field) {
                $oldValue = $existingSchoolInfo->$field;
                $newValue = $validated[$field] ?? null;

                $oldNormalized = is_null($oldValue) ? '' : (string) $oldValue;
                $newNormalized = is_null($newValue) ? '' : (string) $newValue;

                if ($oldNormalized !== $newNormalized) {
                    $editService->handleUpdate($existingSchoolInfo, $field, $newValue);
                    $pendingCount++;
                }
            }

            if ($pendingCount === 0) {
                return back()->with('info', 'No changes detected.');
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => "{$pendingCount} edit(s) submitted for owner approval",
                ], 202);
            }
            return back()->with('info', "{$pendingCount} edit(s) submitted for owner approval.");
        }

        // Owner can update directly
        MemberSchoolInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'School info saved successfully',
            ]);
        }

        return back()->with('success', 'School info saved successfully');
    }

    /**
     * Store a contact for a family member.
     */
    public function storeContact(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can create OR is coparent needing approval
        if (!$permissionService->canCreate('emergency_contacts') && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'relationship' => 'nullable|string|in:' . implode(',', array_keys(MemberContact::RELATIONSHIP_TYPES)),
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_emergency_contact' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:10',
        ]);

        $validated['tenant_id'] = $member->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_emergency_contact'] = $request->boolean('is_emergency_contact');
        $validated['priority'] = $validated['priority'] ?? 0;

        // If coparent, create pending edit
        if ($editService->needsApproval()) {
            $editService->handleCreate(MemberContact::class, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => 'Contact submitted for owner approval',
                ], 202);
            }
            return back()->with('info', 'Contact submitted for owner approval.');
        }

        $contact = MemberContact::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Contact added successfully',
                'contact' => $contact,
            ]);
        }

        return back()->with('success', 'Contact added successfully');
    }

    /**
     * Delete a contact.
     */
    public function destroyContact(FamilyMember $member, MemberContact $contact)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can delete OR is coparent needing approval
        if (!$permissionService->canDelete('emergency_contacts') && !$editService->needsApproval()) {
            abort(403);
        }

        // If coparent, create pending delete
        if ($editService->needsApproval()) {
            $editService->handleDelete($contact);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => 'Delete request submitted for owner approval',
                ], 202);
            }
            return back()->with('info', 'Delete request submitted for owner approval.');
        }

        $contact->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Contact removed successfully',
            ]);
        }

        return back()->with('success', 'Contact removed successfully');
    }

    /**
     * Update a single field on a family member (inline editing).
     */
    public function updateField(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        $field = $request->input('field');

        // Check permission based on field being updated
        if ($field === 'immigration_status') {
            if (!$permissionService->canEdit('immigration_status')) {
                abort(403);
            }
        } elseif (!$permissionService->hasFullAccess() && !$editService->needsApproval()) {
            abort(403);
        }

        $value = $request->input('value');

        // Only allow specific fields to be updated inline
        $allowedFields = ['immigration_status'];

        if (!in_array($field, $allowedFields)) {
            return back()->with('error', 'Invalid field');
        }

        // Validate based on field type
        if ($field === 'immigration_status') {
            if ($value && !array_key_exists($value, FamilyMember::IMMIGRATION_STATUSES)) {
                return back()->with('error', 'Invalid immigration status');
            }
        }

        // Use CoparentEditService to handle the update (creates pending edit if coparent)
        $result = $editService->handleUpdate($member, $field, $value ?: null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $result['success'],
                'pending' => $result['pending'] ?? false,
                'message' => $result['message'],
                'value' => $value,
            ]);
        }

        if ($result['pending'] ?? false) {
            return back()->with('info', $result['message']);
        }

        return back()->with('success', 'Updated successfully');
    }

    /**
     * Update a single field on medical info (inline editing).
     */
    public function updateMedicalField(Request $request, FamilyMember $member)
    {
        // Use centralized permission service - require edit access for medical
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        if (!$permissionService->canEdit('medical') && !$editService->needsApproval()) {
            abort(403);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        // Only allow specific fields to be updated inline
        $allowedFields = ['blood_type'];

        if (!in_array($field, $allowedFields)) {
            return back()->with('error', 'Invalid field');
        }

        // Validate based on field type
        if ($field === 'blood_type') {
            if ($value && !array_key_exists($value, MemberMedicalInfo::BLOOD_TYPES)) {
                return back()->with('error', 'Invalid blood type');
            }
        }

        // Get or create medical info record
        $medicalInfo = MemberMedicalInfo::firstOrCreate(
            ['family_member_id' => $member->id],
            ['tenant_id' => $member->tenant_id]
        );

        // Use CoparentEditService to handle the update (creates pending edit if coparent)
        $result = $editService->handleUpdate($medicalInfo, $field, $value ?: null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $result['success'],
                'pending' => $result['pending'] ?? false,
                'message' => $result['message'],
                'value' => $value,
            ]);
        }

        if ($result['pending'] ?? false) {
            return back()->with('info', $result['message']);
        }

        return back()->with('success', 'Updated successfully');
    }

    /**
     * Display the education info page for a family member.
     */
    public function educationInfo(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('school')) {
            abort(403);
        }

        // For linked members (Self), load data from all linked member records
        if ($member->linked_user_id) {
            $linkedMemberIds = FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->pluck('id')
                ->toArray();

            $member->setRelation('schoolRecords', MemberSchoolInfo::whereIn('family_member_id', $linkedMemberIds)->orderBy('is_current', 'desc')->orderBy('school_year', 'desc')->orderBy('created_at', 'desc')->get());
            $member->setRelation('educationDocuments', MemberEducationDocument::whereIn('family_member_id', $linkedMemberIds)->latest()->get());
        } else {
            $member->load(['schoolRecords', 'educationDocuments']);
        }

        return view('family-circle.member.education-info', [
            'circle' => $familyCircle,
            'member' => $member,
            'gradeLevels' => MemberSchoolInfo::GRADE_LEVELS,
            'documentTypes' => MemberEducationDocument::DOCUMENT_TYPES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show create school record form.
     */
    public function createSchoolRecord(FamilyCircle $familyCircle, FamilyMember $member)
    {
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('school')) {
            abort(403);
        }

        return view('family-circle.member.education.form', [
            'circle' => $familyCircle,
            'member' => $member,
            'schoolRecord' => null,
            'gradeLevels' => MemberSchoolInfo::GRADE_LEVELS,
            'documentTypes' => MemberEducationDocument::DOCUMENT_TYPES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show a single school record.
     */
    public function showSchoolRecord(FamilyCircle $familyCircle, FamilyMember $member, MemberSchoolInfo $schoolRecord)
    {
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('school')) {
            abort(403);
        }

        if ($schoolRecord->family_member_id !== $member->id) {
            abort(404);
        }

        // Load documents for this school record
        $schoolRecord->load('documents');

        return view('family-circle.member.education.show', [
            'circle' => $familyCircle,
            'member' => $member,
            'schoolRecord' => $schoolRecord,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show edit school record form.
     */
    public function editSchoolRecord(FamilyCircle $familyCircle, FamilyMember $member, MemberSchoolInfo $schoolRecord)
    {
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('school')) {
            abort(403);
        }

        if ($schoolRecord->family_member_id !== $member->id) {
            abort(404);
        }

        // Load documents for this school record
        $schoolRecord->load('documents');

        return view('family-circle.member.education.form', [
            'circle' => $familyCircle,
            'member' => $member,
            'schoolRecord' => $schoolRecord,
            'gradeLevels' => MemberSchoolInfo::GRADE_LEVELS,
            'documentTypes' => MemberEducationDocument::DOCUMENT_TYPES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Store a new school record.
     */
    public function storeSchoolRecord(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit('school') && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|in:' . implode(',', array_keys(MemberSchoolInfo::GRADE_LEVELS)),
            'school_year' => 'nullable|string|max:20',
            'is_current' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'student_id' => 'nullable|string|max:100',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
            'bus_number' => 'nullable|string|max:50',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            // Document upload fields
            'document_type' => 'nullable|string|in:' . implode(',', array_keys(MemberEducationDocument::DOCUMENT_TYPES)),
            'document_title' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $validated['tenant_id'] = $member->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_current'] = $request->has('is_current');

        // If coparent, create pending edit for new record
        if ($editService->needsApproval()) {
            // Handle document upload if provided - store it but include path in create_data
            $documentData = null;
            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $tenantId = $member->tenant_id;
                $path = "tenants/{$tenantId}/members/{$member->id}/education";
                $filename = time() . '_' . $file->getClientOriginalName();
                $storedPath = Storage::disk('do_spaces')->putFileAs($path, $file, $filename);

                $documentData = [
                    'tenant_id' => $tenantId,
                    'family_member_id' => $member->id,
                    'uploaded_by' => Auth::id(),
                    'document_type' => $validated['document_type'] ?? 'other',
                    'title' => $validated['document_title'] ?? $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'school_year' => $validated['school_year'] ?? null,
                    'grade_level' => $validated['grade_level'] ?? null,
                ];
            }

            // Include document data in the create request
            $createData = $validated;
            if ($documentData) {
                $createData['_pending_document'] = $documentData;
            }

            $editService->handleCreate(MemberSchoolInfo::class, $createData);

            return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
                ->with('info', 'School record submitted for owner approval.');
        }

        // Owner can create directly
        // If this is set as current, unset other current records
        if ($validated['is_current']) {
            MemberSchoolInfo::where('family_member_id', $member->id)
                ->update(['is_current' => false]);
        }

        $schoolRecord = MemberSchoolInfo::create($validated);

        // Handle document upload if provided
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $tenantId = Auth::user()->tenant_id;
            $path = "tenants/{$tenantId}/members/{$member->id}/education";
            $filename = time() . '_' . $file->getClientOriginalName();

            // Upload to DigitalOcean Spaces
            $storedPath = Storage::disk('do_spaces')->putFileAs($path, $file, $filename);

            MemberEducationDocument::create([
                'tenant_id' => $tenantId,
                'family_member_id' => $member->id,
                'school_record_id' => $schoolRecord->id,
                'uploaded_by' => Auth::id(),
                'document_type' => $validated['document_type'] ?? 'other',
                'title' => $validated['document_title'] ?? $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'school_year' => $validated['school_year'] ?? null,
                'grade_level' => $validated['grade_level'] ?? null,
            ]);
        }

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'School record added successfully');
    }

    /**
     * Update a school record.
     */
    public function updateSchoolRecord(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberSchoolInfo $schoolRecord)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('school')) {
            abort(403);
        }

        // Verify school record belongs to this member
        if ($schoolRecord->family_member_id !== $member->id) {
            abort(404);
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'grade_level' => 'nullable|string|in:' . implode(',', array_keys(MemberSchoolInfo::GRADE_LEVELS)),
            'school_year' => 'nullable|string|max:20',
            'is_current' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'student_id' => 'nullable|string|max:100',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
            'bus_number' => 'nullable|string|max:50',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            // Document upload fields
            'document_type' => 'nullable|string|in:' . implode(',', array_keys(MemberEducationDocument::DOCUMENT_TYPES)),
            'document_title' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $validated['is_current'] = $request->has('is_current');

        // If this is set as current, unset other current records
        if ($validated['is_current']) {
            MemberSchoolInfo::where('family_member_id', $member->id)
                ->where('id', '!=', $schoolRecord->id)
                ->update(['is_current' => false]);
        }

        $schoolRecord->update($validated);

        // Handle document upload if provided
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $tenantId = Auth::user()->tenant_id;
            $path = "tenants/{$tenantId}/members/{$member->id}/education";
            $filename = time() . '_' . $file->getClientOriginalName();

            // Upload to DigitalOcean Spaces
            $storedPath = Storage::disk('do_spaces')->putFileAs($path, $file, $filename);

            MemberEducationDocument::create([
                'tenant_id' => $tenantId,
                'family_member_id' => $member->id,
                'school_record_id' => $schoolRecord->id,
                'uploaded_by' => Auth::id(),
                'document_type' => $validated['document_type'] ?? 'other',
                'title' => $validated['document_title'] ?? $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'school_year' => $validated['school_year'] ?? null,
                'grade_level' => $validated['grade_level'] ?? null,
            ]);
        }

        return redirect()->route('family-circle.member.education.school.show', [$familyCircle, $member, $schoolRecord])
            ->with('success', 'School record updated successfully');
    }

    /**
     * Delete a school record.
     */
    public function destroySchoolRecord(FamilyCircle $familyCircle, FamilyMember $member, MemberSchoolInfo $schoolRecord)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit('school') && !$editService->needsApproval()) {
            abort(403);
        }

        // Verify school record belongs to this member
        if ($schoolRecord->family_member_id !== $member->id) {
            abort(404);
        }

        // If coparent, create pending delete
        if ($editService->needsApproval()) {
            $editService->handleDelete($schoolRecord);

            return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
                ->with('info', 'Delete request submitted for owner approval.');
        }

        $schoolRecord->delete();

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'School record deleted successfully');
    }

    /**
     * Update teacher and counselor contacts (deprecated - kept for backward compatibility).
     */
    public function updateEducationContacts(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('school')) {
            abort(403);
        }

        $validated = $request->validate([
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberSchoolInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            $validated
        );

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'Teacher and counselor information updated successfully');
    }

    /**
     * Update bus information.
     */
    public function updateEducationBus(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('school')) {
            abort(403);
        }

        $validated = $request->validate([
            'bus_number' => 'nullable|string|max:50',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberSchoolInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            $validated
        );

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'Bus information updated successfully');
    }

    /**
     * Store an education document.
     */
    public function storeEducationDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit('school') && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(MemberEducationDocument::DOCUMENT_TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'school_year' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
        ]);

        // Upload file to DigitalOcean Spaces
        $file = $request->file('file');
        $tenantId = $member->tenant_id;
        $fileName = $file->getClientOriginalName();
        $path = "tenants/{$tenantId}/education-documents/{$member->id}/" . uniqid() . '_' . $fileName;

        Storage::disk('do_spaces')->put($path, file_get_contents($file), 'private');

        $data = [
            'tenant_id' => $tenantId,
            'family_member_id' => $member->id,
            'uploaded_by' => Auth::id(),
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'school_year' => $validated['school_year'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];

        // If coparent, create pending edit for new document
        if ($editService->needsApproval()) {
            $editService->handleCreate(MemberEducationDocument::class, $data);

            return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
                ->with('info', 'Education document submitted for owner approval.');
        }

        // Owner can create directly
        MemberEducationDocument::create($data);

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'Education document uploaded successfully');
    }

    /**
     * Download an education document.
     */
    public function downloadEducationDocument(FamilyCircle $familyCircle, FamilyMember $member, MemberEducationDocument $document)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('school')) {
            abort(403);
        }

        // Verify document belongs to this member
        if ($document->family_member_id !== $member->id) {
            abort(404);
        }

        // Generate temporary URL for download
        $url = Storage::disk('do_spaces')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(5)
        );

        return redirect($url);
    }

    /**
     * Delete an education document.
     */
    public function destroyEducationDocument(FamilyCircle $familyCircle, FamilyMember $member, MemberEducationDocument $document)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit('school') && !$editService->needsApproval()) {
            abort(403);
        }

        // Verify document belongs to this member
        if ($document->family_member_id !== $member->id) {
            abort(404);
        }

        // If coparent, create pending delete
        if ($editService->needsApproval()) {
            $editService->handleDelete($document);

            if ($document->school_record_id) {
                return redirect()->back()->with('info', 'Delete request submitted for owner approval.');
            }
            return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
                ->with('info', 'Delete request submitted for owner approval.');
        }

        // Delete file from DigitalOcean Spaces
        Storage::disk('do_spaces')->delete($document->file_path);

        // Store school record id before deleting
        $schoolRecordId = $document->school_record_id;

        // Delete record
        $document->delete();

        // Redirect back to where they came from
        if ($schoolRecordId) {
            return redirect()->back()->with('success', 'Document deleted successfully');
        }

        return redirect()->route('family-circle.member.education-info', [$familyCircle, $member])
            ->with('success', 'Document deleted successfully');
    }
}
