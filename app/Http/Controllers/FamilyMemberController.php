<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Services\CollaboratorPermissionService;
use App\Models\MemberContact;
use App\Models\MemberDocument;
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

        // Get existing family members for autocomplete suggestions
        $existingMembers = FamilyMember::where('tenant_id', Auth::user()->tenant_id)
            ->select('first_name', 'last_name')
            ->get()
            ->map(fn($m) => $m->first_name . ' ' . $m->last_name)
            ->toArray();

        // Also add the account owner's name
        $owner = Auth::user();
        array_unshift($existingMembers, $owner->name);

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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        return view('family-circle.member.edit', [
            'circle' => $familyCircle,
            'member' => $member,
            'relationships' => FamilyMember::RELATIONSHIPS,
            'immigrationStatuses' => FamilyMember::IMMIGRATION_STATUSES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Update the specified family member.
     */
    public function update(Request $request, FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service - require full access to update member profile
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasFullAccess()) {
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

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

        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if ($member->profile_image) {
                Storage::disk('do_spaces')->delete($member->profile_image);
            }
            $path = $request->file('profile_image')->store('family-ledger/members/profiles', 'do_spaces');
            $data['profile_image'] = $path;
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Family member updated successfully',
                'member' => $member,
            ]);
        }

        return redirect()->route('family-circle.member.show', [$familyCircle, $member])
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
        // Use centralized permission service - require full access for school info
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasFullAccess()) {
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

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

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
        // Use centralized permission service - require full access to create contacts
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('emergency_contacts')) {
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

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_emergency_contact'] = $request->boolean('is_emergency_contact');
        $validated['priority'] = $validated['priority'] ?? 0;

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
        // Use centralized permission service - require full access to delete contacts
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('emergency_contacts')) {
            abort(403);
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

        $field = $request->input('field');

        // Check permission based on field being updated
        if ($field === 'immigration_status') {
            if (!$permissionService->canEdit('immigration_status')) {
                abort(403);
            }
        } elseif (!$permissionService->hasFullAccess()) {
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

        $member->update([$field => $value ?: null]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Updated successfully',
                'value' => $value,
            ]);
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

        if (!$permissionService->canEdit('medical')) {
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

        // Create or update medical info
        MemberMedicalInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            [
                'tenant_id' => Auth::user()->tenant_id,
                $field => $value ?: null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Updated successfully',
                'value' => $value,
            ]);
        }

        return back()->with('success', 'Updated successfully');
    }
}
