<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\CollaboratorInvite;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FamilyCircleController extends Controller
{
    /**
     * Display a listing of family circles.
     */
    public function index()
    {
        $user = Auth::user();

        $circles = FamilyCircle::forCurrentTenant()
            ->withCount('members')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get collaborations where this user is a collaborator (shared with them)
        $collaborations = Collaborator::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['familyMembers', 'inviter'])
            ->get()
            ->map(function ($collaboration) {
                return [
                    'id' => $collaboration->id,
                    'tenant_id' => $collaboration->tenant_id,
                    'owner_name' => $collaboration->inviter->name ?? 'Unknown',
                    'role' => $collaboration->role,
                    'role_info' => $collaboration->role_info,
                    'relationship' => $collaboration->relationship_info['label'] ?? 'Collaborator',
                    'family_members' => $collaboration->familyMembers,
                    'joined_at' => $collaboration->created_at,
                ];
            });

        // Get pending invites for this user's email
        $pendingInvites = CollaboratorInvite::where('email', strtolower($user->email))
            ->pending()
            ->with(['inviter', 'familyMembers'])
            ->get();

        return view('family-circle.index', [
            'circles' => $circles,
            'collaborations' => $collaborations,
            'pendingInvites' => $pendingInvites,
        ]);
    }

    /**
     * Store a newly created family circle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'include_me' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // Ensure user has a tenant
        if (!$user->tenant_id) {
            // Create a default tenant for the user
            $tenant = \App\Models\Tenant::create([
                'name' => $user->name . "'s Family",
                'is_active' => true,
            ]);
            $user->tenant_id = $tenant->id;
            $user->save();
        }

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => $user->id,
            'tenant_id' => $user->tenant_id,
        ];

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('family-ledger/circles/covers', 'do_spaces');
            $data['cover_image'] = $path;
        }

        $circle = FamilyCircle::create($data);

        // Add creator as a family member if "Include Me" is checked
        if ($request->boolean('include_me')) {
            // Parse user's name into first and last name
            $nameParts = explode(' ', $user->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            FamilyMember::create([
                'tenant_id' => $user->tenant_id,
                'family_circle_id' => $circle->id,
                'created_by' => $user->id,
                'linked_user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'relationship' => FamilyMember::RELATIONSHIP_SELF,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Family circle created successfully',
                'circle' => $circle,
                'redirect' => route('family-circle.show', $circle),
            ]);
        }

        return redirect()->route('family-circle.show', $circle)
            ->with('success', 'Family circle created successfully');
    }

    /**
     * Display the specified family circle.
     */
    public function show(FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $familyCircle->load(['members' => function ($query) {
            $query->orderBy('relationship')->orderBy('first_name');
        }]);

        return view('family-circle.show', [
            'circle' => $familyCircle,
        ]);
    }

    /**
     * Update the specified family circle.
     */
    public function update(Request $request, FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($familyCircle->cover_image) {
                Storage::disk('do_spaces')->delete($familyCircle->cover_image);
            }
            $path = $request->file('cover_image')->store('family-ledger/circles/covers', 'do_spaces');
            $data['cover_image'] = $path;
        }

        $familyCircle->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Family circle updated successfully',
                'circle' => $familyCircle,
            ]);
        }

        return redirect()->route('family-circle.show', $familyCircle)
            ->with('success', 'Family circle updated successfully');
    }

    /**
     * Display the owner's profile in the same format as family members.
     */
    public function showOwner(FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $user = Auth::user();

        // Load owner's related data through tenant
        $tenantId = $user->tenant_id;

        // Get insurance policies for this tenant
        $insurancePolicies = \App\Models\InsurancePolicy::where('tenant_id', $tenantId)->get();

        // Get tax returns for this tenant
        $taxReturns = \App\Models\TaxReturn::where('tenant_id', $tenantId)->get();

        // Get assets for this tenant
        $assets = \App\Models\Asset::where('tenant_id', $tenantId)->get();

        return view('family-circle.owner.show', [
            'circle' => $familyCircle,
            'owner' => $user,
            'insurancePolicies' => $insurancePolicies,
            'taxReturns' => $taxReturns,
            'assets' => $assets,
        ]);
    }

    /**
     * Remove the specified family circle.
     */
    public function destroy(FamilyCircle $familyCircle)
    {
        // Ensure the user can access this circle
        if ($familyCircle->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete cover image if exists
        if ($familyCircle->cover_image) {
            Storage::disk('do_spaces')->delete($familyCircle->cover_image);
        }

        $familyCircle->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Family circle deleted successfully',
            ]);
        }

        return redirect()->route('family-circle.index')
            ->with('success', 'Family circle deleted successfully');
    }
}
