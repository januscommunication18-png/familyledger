<?php

namespace App\Http\Controllers;

use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\MemberContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberEmergencyContactController extends Controller
{
    /**
     * Display the emergency contacts page for a family member.
     */
    public function show(FamilyCircle $familyCircle, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
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
        ]);
    }

    /**
     * Store a new emergency contact.
     */
    public function store(Request $request, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
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

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_emergency_contact'] = true;
        $validated['priority'] = $validated['priority'] ?? ($member->contacts()->where('is_emergency_contact', true)->max('priority') ?? 0) + 1;

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
        if ($member->tenant_id !== Auth::user()->tenant_id) {
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
        if ($member->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $contact->delete();

        return redirect()->route('family-circle.member.emergency-contacts', [
            $member->familyCircle,
            $member
        ])->with('success', 'Emergency contact removed successfully');
    }
}
