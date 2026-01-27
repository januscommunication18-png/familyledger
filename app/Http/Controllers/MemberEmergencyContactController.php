<?php

namespace App\Http\Controllers;

use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\MemberContact;
use App\Services\CollaboratorPermissionService;
use App\Services\CoparentEditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberEmergencyContactController extends Controller
{
    /**
     * Display the emergency contacts page for a family member.
     */
    public function show(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('emergency_contacts')) {
            abort(403);
        }

        // For linked members (Self), load contacts from all linked member records
        if ($member->linked_user_id) {
            $linkedMemberIds = FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->pluck('id')
                ->toArray();

            $contacts = MemberContact::whereIn('family_member_id', $linkedMemberIds)
                ->where('is_emergency_contact', true)
                ->orderBy('priority')
                ->get();

            $member->setRelation('contacts', $contacts);
        } else {
            $member->load(['contacts' => function ($query) {
                $query->where('is_emergency_contact', true)->orderBy('priority');
            }]);
        }

        return view('family-circle.member.emergency-contacts', [
            'circle' => $familyCircle,
            'member' => $member,
            'relationshipTypes' => MemberContact::RELATIONSHIP_TYPES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Store a new emergency contact.
     */
    public function store(Request $request, FamilyMember $member)
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
            'priority' => 'nullable|integer|min:1|max:10',
        ]);

        $validated['tenant_id'] = $member->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_emergency_contact'] = true;
        $validated['priority'] = $validated['priority'] ?? ($member->contacts()->where('is_emergency_contact', true)->max('priority') ?? 0) + 1;

        // If coparent, create pending edit
        if ($editService->needsApproval()) {
            $editService->handleCreate(MemberContact::class, $validated);

            return redirect()->route('family-circle.member.emergency-contacts', [
                $member->familyCircle,
                $member
            ])->with('info', 'Emergency contact submitted for owner approval.');
        }

        MemberContact::create($validated);

        return redirect()->route('family-circle.member.emergency-contacts', [
            $member->familyCircle,
            $member
        ])->with('success', 'Emergency contact added successfully');
    }

    /**
     * Update an emergency contact.
     */
    public function update(Request $request, FamilyMember $member, MemberContact $contact)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit('emergency_contacts') && !$editService->needsApproval()) {
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
            'priority' => 'nullable|integer|min:1|max:10',
        ]);

        // If coparent, create pending edits for changed fields
        if ($editService->needsApproval()) {
            $pendingCount = 0;
            foreach ($validated as $field => $newValue) {
                $oldValue = $contact->$field;
                $oldNormalized = is_null($oldValue) ? '' : (string) $oldValue;
                $newNormalized = is_null($newValue) ? '' : (string) $newValue;

                if ($oldNormalized !== $newNormalized) {
                    $editService->handleUpdate($contact, $field, $newValue);
                    $pendingCount++;
                }
            }

            if ($pendingCount === 0) {
                return redirect()->route('family-circle.member.emergency-contacts', [
                    $member->familyCircle,
                    $member
                ])->with('info', 'No changes detected.');
            }

            return redirect()->route('family-circle.member.emergency-contacts', [
                $member->familyCircle,
                $member
            ])->with('info', "{$pendingCount} edit(s) submitted for owner approval.");
        }

        $contact->update($validated);

        return redirect()->route('family-circle.member.emergency-contacts', [
            $member->familyCircle,
            $member
        ])->with('success', 'Emergency contact updated successfully');
    }

    /**
     * Delete an emergency contact.
     */
    public function destroy(FamilyMember $member, MemberContact $contact)
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

            return redirect()->route('family-circle.member.emergency-contacts', [
                $member->familyCircle,
                $member
            ])->with('info', 'Delete request submitted for owner approval.');
        }

        $contact->delete();

        return redirect()->route('family-circle.member.emergency-contacts', [
            $member->familyCircle,
            $member
        ])->with('success', 'Emergency contact removed successfully');
    }
}
