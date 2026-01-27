<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Collaborator;
use App\Models\CoparentChild;
use App\Models\FamilyMember;
use App\Models\PendingCoparentEdit;
use App\Services\CoparentEditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoparentAssetController extends Controller
{
    /**
     * Display assets for children the coparent has access to.
     */
    public function index(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();
        $isOwner = true;

        // Get children with co-parenting enabled from user's own tenant
        $ownChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        // Get children the user has co-parent access to (from other tenants)
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->with(['coparentChildren'])
            ->get();

        $sharedChildren = collect();
        $childrenWithAssetAccess = collect();

        foreach ($coparentAccess as $collaborator) {
            $isOwner = false; // User is viewing as coparent

            foreach ($collaborator->coparentChildren as $child) {
                // Get the pivot to check permissions
                $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
                    ->where('family_member_id', $child->id)
                    ->first();

                if ($pivot && $pivot->canView('assets')) {
                    $child->can_edit_assets = $pivot->canEdit('assets');
                    $child->other_parent_name = $collaborator->inviter->name ?? 'Unknown';
                    $child->collaborator_id = $collaborator->id;
                    $child->tenant_id_for_assets = $collaborator->tenant_id;
                    $sharedChildren->push($child);
                    $childrenWithAssetAccess->push($child);
                }
            }
        }

        // For owners, add their own children
        foreach ($ownChildren as $child) {
            $child->can_edit_assets = true;
            $childrenWithAssetAccess->push($child);
        }

        // Get assets owned by these children
        $childIds = $childrenWithAssetAccess->pluck('id')->toArray();

        $assets = Asset::whereHas('owners', function ($query) use ($childIds) {
            $query->whereIn('family_member_id', $childIds);
        })
            ->with(['owners.familyMember', 'documents'])
            ->get();

        // Check if user can request new assets (has edit permission for at least one child)
        $canRequestAssets = $childrenWithAssetAccess->contains(function ($child) {
            return $child->can_edit_assets ?? false;
        });

        // Get pending asset requests by this user
        $pendingRequests = PendingCoparentEdit::where('requested_by', $user->id)
            ->where('editable_type', 'App\\Models\\Asset')
            ->where('is_create', true)
            ->pending()
            ->with('familyMember')
            ->get();

        return view('pages.coparenting.assets.index', compact(
            'assets',
            'childrenWithAssetAccess',
            'canRequestAssets',
            'pendingRequests',
            'isOwner',
            'sharedChildren'
        ));
    }

    /**
     * Show form to request adding a new asset.
     */
    public function create(): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Get children the user has co-parent access to with asset edit permission
        $coparentAccess = Collaborator::where('user_id', $user->id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->with(['coparentChildren', 'inviter'])
            ->get();

        $childrenWithEditAccess = collect();

        foreach ($coparentAccess as $collaborator) {
            foreach ($collaborator->coparentChildren as $child) {
                $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
                    ->where('family_member_id', $child->id)
                    ->first();

                if ($pivot && $pivot->canEdit('assets')) {
                    $child->other_parent_name = $collaborator->inviter->name ?? 'Unknown';
                    $child->tenant_id_for_assets = $collaborator->tenant_id;
                    $childrenWithEditAccess->push($child);
                }
            }
        }

        // Also add own children with co-parenting enabled
        $ownChildren = FamilyMember::forCurrentTenant()
            ->minors()
            ->where('co_parenting_enabled', true)
            ->get();

        foreach ($ownChildren as $child) {
            $child->tenant_id_for_assets = $user->tenant_id;
            $childrenWithEditAccess->push($child);
        }

        if ($childrenWithEditAccess->isEmpty()) {
            return redirect()->route('coparenting.assets.index')
                ->with('error', 'You do not have permission to add assets for any children.');
        }

        return view('pages.coparenting.assets.create', [
            'children' => $childrenWithEditAccess,
            'categories' => Asset::CATEGORIES,
            'propertyTypes' => Asset::PROPERTY_TYPES,
            'vehicleTypes' => Asset::VEHICLE_TYPES,
            'valuableTypes' => Asset::VALUABLE_TYPES,
            'ownershipTypes' => Asset::OWNERSHIP_TYPES,
            'vehicleOwnership' => Asset::VEHICLE_OWNERSHIP,
            'conditions' => Asset::CONDITIONS,
            'collectableCategories' => Asset::COLLECTABLE_CATEGORIES,
            'roomLocations' => Asset::ROOM_LOCATIONS,
        ]);
    }

    /**
     * Store a request to add a new asset.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'family_member_id' => 'required|exists:family_members,id',
            'name' => 'required|string|max:255',
            'asset_category' => 'required|in:property,vehicle,valuable,inventory',
            'asset_type' => 'nullable|string|max:50',
            'ownership_type' => 'nullable|in:individual,joint,trust_company',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'acquisition_date' => 'nullable|date',
            'purchase_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            // Location fields
            'location_address' => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:100',
            'location_state' => 'nullable|string|max:100',
            'location_zip' => 'nullable|string|max:20',
            'location_country' => 'nullable|string|max:100',
            'storage_location' => 'nullable|string|max:255',
            // Vehicle fields
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:2100',
            'vin_registration' => 'nullable|string|max:50',
            'vehicle_ownership' => 'nullable|in:owned,leased,financed',
            'license_plate' => 'nullable|string|max:20',
            'mileage' => 'nullable|integer|min:0',
            // Collectable fields
            'collectable_category' => 'nullable|string|max:50',
            'appraised_by' => 'nullable|string|max:255',
            'appraisal_date' => 'nullable|date',
            'appraisal_value' => 'nullable|numeric|min:0',
            'condition' => 'nullable|in:mint,excellent,good,fair,poor',
            'provenance' => 'nullable|string|max:1000',
            // Inventory fields
            'serial_number' => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'room_location' => 'nullable|string|max:50',
            // Insurance fields
            'is_insured' => 'nullable|boolean',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_renewal_date' => 'nullable|date',
            // Request notes
            'request_notes' => 'nullable|string|max:500',
        ]);

        $familyMember = FamilyMember::findOrFail($validated['family_member_id']);

        // Verify the user has access to this child with asset edit permission
        $hasPermission = $this->verifyAssetEditPermission($user, $familyMember);

        if (!$hasPermission) {
            return back()->with('error', 'You do not have permission to add assets for this child.');
        }

        // Check if user is owner of this child's tenant
        $isOwner = $familyMember->tenant_id === $user->tenant_id;

        if ($isOwner) {
            // Owner can create directly
            $assetData = $this->prepareAssetData($validated, $familyMember);
            $owners = $assetData['_owners'];
            unset($assetData['_owners']);

            $asset = Asset::create($assetData);

            // Create owners
            foreach ($owners as $ownerData) {
                $asset->owners()->create($ownerData);
            }

            return redirect()->route('coparenting.assets.index')
                ->with('success', 'Asset added successfully.');
        }

        // Coparent - create pending edit request
        $assetData = $this->prepareAssetData($validated, $familyMember);

        $service = CoparentEditService::forMember($familyMember, $user);
        $result = $service->handleCreate(
            Asset::class,
            $assetData,
            $validated['request_notes'] ?? null
        );

        if ($result['pending']) {
            return redirect()->route('coparenting.assets.index')
                ->with('success', 'Your request to add this asset has been submitted for approval.');
        }

        return redirect()->route('coparenting.assets.index')
            ->with('error', $result['message'] ?? 'Failed to submit asset request.');
    }

    /**
     * Show a specific asset (if user has permission).
     */
    public function show(Asset $asset): View
    {
        session(['coparenting_mode' => true]);

        $user = auth()->user();

        // Verify user has permission to view this asset
        $hasAccess = $this->verifyAssetViewPermission($user, $asset);

        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this asset.');
        }

        $asset->load(['owners.familyMember', 'documents']);

        return view('pages.coparenting.assets.show', compact('asset'));
    }

    /**
     * Verify if user has asset edit permission for a family member.
     */
    protected function verifyAssetEditPermission($user, FamilyMember $member): bool
    {
        // If user owns this member's tenant, they have permission
        if ($member->tenant_id === $user->tenant_id) {
            return true;
        }

        // Check coparent access
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('tenant_id', $member->tenant_id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->first();

        if (!$collaborator) {
            return false;
        }

        $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
            ->where('family_member_id', $member->id)
            ->first();

        return $pivot && $pivot->canEdit('assets');
    }

    /**
     * Verify if user has asset view permission for an asset.
     */
    protected function verifyAssetViewPermission($user, Asset $asset): bool
    {
        // Get family member owners of this asset
        $ownerIds = $asset->owners()->whereNotNull('family_member_id')->pluck('family_member_id');

        // If user owns the asset's tenant, they have permission
        if ($asset->tenant_id === $user->tenant_id) {
            return true;
        }

        // Check if user has coparent access to any of the family member owners
        foreach ($ownerIds as $memberId) {
            $member = FamilyMember::find($memberId);
            if (!$member) {
                continue;
            }

            $collaborator = Collaborator::where('user_id', $user->id)
                ->where('tenant_id', $member->tenant_id)
                ->where('coparenting_enabled', true)
                ->where('is_active', true)
                ->first();

            if (!$collaborator) {
                continue;
            }

            $pivot = CoparentChild::where('collaborator_id', $collaborator->id)
                ->where('family_member_id', $member->id)
                ->first();

            if ($pivot && $pivot->canView('assets')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare asset data for creation, including owners.
     */
    protected function prepareAssetData(array $validated, FamilyMember $familyMember): array
    {
        $requestNotes = $validated['request_notes'] ?? null;
        unset($validated['request_notes'], $validated['family_member_id']);

        // Build the asset data
        $assetData = array_merge($validated, [
            'tenant_id' => $familyMember->tenant_id,
            'status' => 'active',
            'currency' => $validated['currency'] ?? 'USD',
            'is_insured' => $validated['is_insured'] ?? false,
        ]);

        // Add owner information
        $assetData['_owners'] = [
            [
                'family_member_id' => $familyMember->id,
                'ownership_percentage' => 100,
                'is_primary_owner' => true,
            ]
        ];

        return $assetData;
    }
}
