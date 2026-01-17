<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDocument;
use App\Models\AssetOwner;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Display the assets index with tabs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'property');

        $assets = Asset::where('tenant_id', $user->tenant_id)
            ->with(['owners.familyMember', 'documents'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group assets by category
        $propertyAssets = $assets->where('asset_category', 'property');
        $vehicleAssets = $assets->where('asset_category', 'vehicle');
        $valuableAssets = $assets->where('asset_category', 'valuable');
        $inventoryAssets = $assets->where('asset_category', 'inventory');

        // Calculate totals
        $totals = [
            'property' => $propertyAssets->sum('current_value'),
            'vehicle' => $vehicleAssets->sum('current_value'),
            'valuable' => $valuableAssets->sum('current_value'),
            'inventory' => $inventoryAssets->sum('current_value'),
            'overall' => $assets->sum('current_value'),
        ];

        $counts = [
            'property' => $propertyAssets->count(),
            'vehicle' => $vehicleAssets->count(),
            'valuable' => $valuableAssets->count(),
            'inventory' => $inventoryAssets->count(),
        ];

        return view('pages.assets.index', [
            'tab' => $tab,
            'propertyAssets' => $propertyAssets,
            'vehicleAssets' => $vehicleAssets,
            'valuableAssets' => $valuableAssets,
            'inventoryAssets' => $inventoryAssets,
            'totals' => $totals,
            'counts' => $counts,
            'categories' => Asset::CATEGORIES,
            'statuses' => Asset::STATUSES,
        ]);
    }

    /**
     * Show asset create form.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $familyMembers = $this->getUniqueFamilyMembers($user->tenant_id);
        $category = $request->get('category', 'property');

        // Get family circles with their members for joint ownership
        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->with(['members' => function ($query) use ($user) {
                $query->where('linked_user_id', '!=', $user->id)
                    ->orWhereNull('linked_user_id');
            }])
            ->orderBy('name')
            ->get();

        return view('pages.assets.form', [
            'asset' => null,
            'familyMembers' => $familyMembers,
            'familyCircles' => $familyCircles,
            'category' => $category,
            'categories' => Asset::CATEGORIES,
            'propertyTypes' => Asset::PROPERTY_TYPES,
            'vehicleTypes' => Asset::VEHICLE_TYPES,
            'valuableTypes' => Asset::VALUABLE_TYPES,
            'collectableCategories' => Asset::COLLECTABLE_CATEGORIES,
            'ownershipTypes' => Asset::OWNERSHIP_TYPES,
            'vehicleOwnership' => Asset::VEHICLE_OWNERSHIP,
            'conditions' => Asset::CONDITIONS,
            'statuses' => Asset::STATUSES,
            'documentTypes' => Asset::DOCUMENT_TYPES,
            'roomLocations' => Asset::ROOM_LOCATIONS,
        ]);
    }

    /**
     * Store a new asset.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category' => 'required|string|in:' . implode(',', array_keys(Asset::CATEGORIES)),
            'asset_type' => 'required|string|max:100',
            'ownership_type' => 'nullable|string|in:' . implode(',', array_keys(Asset::OWNERSHIP_TYPES)),
            'status' => 'nullable|string|in:' . implode(',', array_keys(Asset::STATUSES)),
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'purchase_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            // Location
            'location_address' => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:100',
            'location_state' => 'nullable|string|max:100',
            'location_zip' => 'nullable|string|max:20',
            'location_country' => 'nullable|string|max:100',
            'storage_location' => 'nullable|string|max:255',
            // Vehicle fields
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 2),
            'vin_registration' => 'nullable|string|max:50',
            'vehicle_ownership' => 'nullable|string|in:' . implode(',', array_keys(Asset::VEHICLE_OWNERSHIP)),
            'license_plate' => 'nullable|string|max:20',
            'mileage' => 'nullable|integer|min:0',
            // Collectable fields
            'collectable_category' => 'nullable|string|in:' . implode(',', array_keys(Asset::COLLECTABLE_CATEGORIES)),
            'appraised_by' => 'nullable|string|max:255',
            'appraisal_date' => 'nullable|date',
            'appraisal_value' => 'nullable|numeric|min:0',
            'condition' => 'nullable|string|in:' . implode(',', array_keys(Asset::CONDITIONS)),
            'provenance' => 'nullable|string',
            // Inventory fields
            'serial_number' => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'room_location' => 'nullable|string|in:' . implode(',', array_keys(Asset::ROOM_LOCATIONS)),
            // Insurance
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_renewal_date' => 'nullable|date',
            'insurance_reminder' => 'nullable|boolean',
            'is_insured' => 'nullable|boolean',
            // Owners - Family members
            'family_owners' => 'nullable|array',
            'family_owners.*.selected' => 'nullable|in:1',
            'family_owners.*.percentage' => 'nullable|numeric|min:0|max:100',
            // Owners - External
            'external_owners' => 'nullable|array',
            'external_owners.*.first_name' => 'nullable|string|max:100',
            'external_owners.*.last_name' => 'nullable|string|max:100',
            'external_owners.*.email' => 'nullable|email|max:255',
            'external_owners.*.phone' => 'nullable|string|max:50',
            'external_owners.*.percentage' => 'nullable|numeric|min:0|max:100',
            // Documents
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|in:' . implode(',', array_keys(Asset::DOCUMENT_TYPES)),
        ]);

        $data = collect($validated)->except(['family_owners', 'external_owners', 'documents', 'document_types'])->toArray();
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['status'] = $data['status'] ?? 'active';
        $data['ownership_type'] = $data['ownership_type'] ?? 'individual';
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['is_insured'] = $request->boolean('is_insured');

        $asset = Asset::create($data);

        // Handle owners (only for joint ownership)
        if ($data['ownership_type'] === 'joint') {
            $this->syncOwners($asset, $validated['family_owners'] ?? [], $validated['external_owners'] ?? []);
        }

        // Handle document uploads
        if ($request->hasFile('documents')) {
            $documentTypes = $validated['document_types'] ?? [];
            foreach ($request->file('documents') as $index => $file) {
                $path = $file->store(
                    'documents/assets/' . Auth::user()->tenant_id . '/' . $asset->id,
                    'private'
                );

                AssetDocument::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'asset_id' => $asset->id,
                    'document_type' => $documentTypes[$index] ?? 'other',
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('assets.index', ['tab' => $asset->asset_category])
            ->with('success', 'Asset added successfully');
    }

    /**
     * Show asset details.
     */
    public function show(Asset $asset)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $asset->load(['owners.familyMember', 'documents']);

        return view('pages.assets.show', [
            'asset' => $asset,
            'categories' => Asset::CATEGORIES,
            'statuses' => Asset::STATUSES,
            'documentTypes' => Asset::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Show asset edit form.
     */
    public function edit(Asset $asset)
    {
        $user = Auth::user();

        if ($asset->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $asset->load(['owners.familyMember', 'documents']);
        $familyMembers = $this->getUniqueFamilyMembers($user->tenant_id);

        // Get family circles with their members for joint ownership
        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->with(['members' => function ($query) use ($user) {
                $query->where('linked_user_id', '!=', $user->id)
                    ->orWhereNull('linked_user_id');
            }])
            ->orderBy('name')
            ->get();

        return view('pages.assets.form', [
            'asset' => $asset,
            'familyMembers' => $familyMembers,
            'familyCircles' => $familyCircles,
            'category' => $asset->asset_category,
            'categories' => Asset::CATEGORIES,
            'propertyTypes' => Asset::PROPERTY_TYPES,
            'vehicleTypes' => Asset::VEHICLE_TYPES,
            'valuableTypes' => Asset::VALUABLE_TYPES,
            'collectableCategories' => Asset::COLLECTABLE_CATEGORIES,
            'ownershipTypes' => Asset::OWNERSHIP_TYPES,
            'vehicleOwnership' => Asset::VEHICLE_OWNERSHIP,
            'conditions' => Asset::CONDITIONS,
            'statuses' => Asset::STATUSES,
            'documentTypes' => Asset::DOCUMENT_TYPES,
            'roomLocations' => Asset::ROOM_LOCATIONS,
        ]);
    }

    /**
     * Update an asset.
     */
    public function update(Request $request, Asset $asset)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category' => 'required|string|in:' . implode(',', array_keys(Asset::CATEGORIES)),
            'asset_type' => 'required|string|max:100',
            'ownership_type' => 'nullable|string|in:' . implode(',', array_keys(Asset::OWNERSHIP_TYPES)),
            'status' => 'nullable|string|in:' . implode(',', array_keys(Asset::STATUSES)),
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'purchase_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            // Location
            'location_address' => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:100',
            'location_state' => 'nullable|string|max:100',
            'location_zip' => 'nullable|string|max:20',
            'location_country' => 'nullable|string|max:100',
            'storage_location' => 'nullable|string|max:255',
            // Vehicle fields
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 2),
            'vin_registration' => 'nullable|string|max:50',
            'vehicle_ownership' => 'nullable|string|in:' . implode(',', array_keys(Asset::VEHICLE_OWNERSHIP)),
            'license_plate' => 'nullable|string|max:20',
            'mileage' => 'nullable|integer|min:0',
            // Collectable fields
            'collectable_category' => 'nullable|string|in:' . implode(',', array_keys(Asset::COLLECTABLE_CATEGORIES)),
            'appraised_by' => 'nullable|string|max:255',
            'appraisal_date' => 'nullable|date',
            'appraisal_value' => 'nullable|numeric|min:0',
            'condition' => 'nullable|string|in:' . implode(',', array_keys(Asset::CONDITIONS)),
            'provenance' => 'nullable|string',
            // Inventory fields
            'serial_number' => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'room_location' => 'nullable|string|in:' . implode(',', array_keys(Asset::ROOM_LOCATIONS)),
            // Insurance
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_renewal_date' => 'nullable|date',
            'insurance_reminder' => 'nullable|boolean',
            'is_insured' => 'nullable|boolean',
            // Owners - Family members
            'family_owners' => 'nullable|array',
            'family_owners.*.selected' => 'nullable|in:1',
            'family_owners.*.percentage' => 'nullable|numeric|min:0|max:100',
            // Owners - External
            'external_owners' => 'nullable|array',
            'external_owners.*.first_name' => 'nullable|string|max:100',
            'external_owners.*.last_name' => 'nullable|string|max:100',
            'external_owners.*.email' => 'nullable|email|max:255',
            'external_owners.*.phone' => 'nullable|string|max:50',
            'external_owners.*.percentage' => 'nullable|numeric|min:0|max:100',
            // Documents
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|in:' . implode(',', array_keys(Asset::DOCUMENT_TYPES)),
        ]);

        $data = collect($validated)->except(['family_owners', 'external_owners', 'documents', 'document_types'])->toArray();
        $data['is_insured'] = $request->boolean('is_insured');

        $asset->update($data);

        // Handle owners (only for joint ownership, otherwise clear owners)
        if ($data['ownership_type'] === 'joint') {
            $this->syncOwners($asset, $validated['family_owners'] ?? [], $validated['external_owners'] ?? []);
        } else {
            $asset->owners()->delete();
        }

        // Handle document uploads (append to existing)
        if ($request->hasFile('documents')) {
            $documentTypes = $validated['document_types'] ?? [];
            foreach ($request->file('documents') as $index => $file) {
                $path = $file->store(
                    'documents/assets/' . Auth::user()->tenant_id . '/' . $asset->id,
                    'private'
                );

                AssetDocument::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'asset_id' => $asset->id,
                    'document_type' => $documentTypes[$index] ?? 'other',
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('assets.index', ['tab' => $asset->asset_category])
            ->with('success', 'Asset updated successfully');
    }

    /**
     * Delete an asset.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete uploaded documents
        foreach ($asset->documents as $document) {
            if ($document->file_path) {
                Storage::disk('private')->delete($document->file_path);
            }
        }

        $category = $asset->asset_category;
        $asset->delete();

        return redirect()->route('assets.index', ['tab' => $category])
            ->with('success', 'Asset deleted successfully');
    }

    /**
     * Upload a document to an asset.
     */
    public function uploadDocument(Request $request, Asset $asset)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type' => 'required|string|in:' . implode(',', array_keys(Asset::DOCUMENT_TYPES)),
            'tags' => 'nullable|string',
        ]);

        $file = $request->file('document');
        $path = $file->store(
            'documents/assets/' . Auth::user()->tenant_id . '/' . $asset->id,
            'private'
        );

        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = json_decode($validated['tags'], true);
        }

        AssetDocument::create([
            'tenant_id' => Auth::user()->tenant_id,
            'asset_id' => $asset->id,
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'tags' => $tags,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }

    /**
     * Delete a document from an asset.
     */
    public function deleteDocument(Asset $asset, AssetDocument $document)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id || $document->asset_id !== $asset->id) {
            abort(403);
        }

        if ($document->file_path) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully');
    }

    /**
     * Download a document.
     */
    public function downloadDocument(Asset $asset, AssetDocument $document)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id || $document->asset_id !== $asset->id) {
            abort(403);
        }

        if (!$document->file_path || !Storage::disk('private')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('private')->download($document->file_path, $document->original_filename);
    }

    /**
     * Serve document file (for inline viewing).
     */
    public function viewDocument(Asset $asset, AssetDocument $document)
    {
        if ($asset->tenant_id !== Auth::user()->tenant_id || $document->asset_id !== $asset->id) {
            abort(403);
        }

        if (!$document->file_path || !Storage::disk('private')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('private')->response($document->file_path);
    }

    /**
     * Sync owners for an asset.
     */
    private function syncOwners(Asset $asset, array $familyOwners, array $externalOwners)
    {
        // Delete existing owners
        $asset->owners()->delete();

        $isPrimary = true;
        $currentUser = Auth::user();

        // Handle family member owners
        foreach ($familyOwners as $memberId => $ownerData) {
            // Skip if not selected
            if (empty($ownerData['selected'])) {
                continue;
            }

            // Handle the 'owner' pseudo-member (current logged-in user)
            if ($memberId === 'owner') {
                AssetOwner::create([
                    'tenant_id' => $currentUser->tenant_id,
                    'asset_id' => $asset->id,
                    'family_member_id' => null,
                    'external_owner_name' => $currentUser->name ?? $currentUser->email,
                    'external_owner_email' => $currentUser->email,
                    'ownership_percentage' => $ownerData['percentage'] ?? null,
                    'is_primary_owner' => $isPrimary,
                ]);

                $isPrimary = false;
                continue;
            }

            // Skip non-numeric member IDs
            if (!is_numeric($memberId)) {
                continue;
            }

            AssetOwner::create([
                'tenant_id' => $currentUser->tenant_id,
                'asset_id' => $asset->id,
                'family_member_id' => $memberId,
                'ownership_percentage' => $ownerData['percentage'] ?? null,
                'is_primary_owner' => $isPrimary,
            ]);

            $isPrimary = false;
        }

        // Handle external owners
        foreach ($externalOwners as $ownerData) {
            // Skip if no name provided
            if (empty($ownerData['first_name']) && empty($ownerData['last_name'])) {
                continue;
            }

            $fullName = trim(($ownerData['first_name'] ?? '') . ' ' . ($ownerData['last_name'] ?? ''));

            AssetOwner::create([
                'tenant_id' => $currentUser->tenant_id,
                'asset_id' => $asset->id,
                'family_member_id' => null,
                'external_owner_name' => $fullName ?: null,
                'external_owner_email' => $ownerData['email'] ?? null,
                'external_owner_phone' => $ownerData['phone'] ?? null,
                'ownership_percentage' => $ownerData['percentage'] ?? null,
                'is_primary_owner' => $isPrimary,
            ]);

            $isPrimary = false;
        }
    }

    /**
     * Get unique family members (deduplicate linked members across circles).
     */
    private function getUniqueFamilyMembers($tenantId)
    {
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;

        $members = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name')
            ->get();

        // Deduplicate: keep first occurrence of each linked_user_id
        // Exclude members linked to the current user (owner)
        $seen = [];
        $filteredMembers = $members->filter(function ($member) use (&$seen, $currentUserId) {
            if ($member->linked_user_id == $currentUserId && $member->linked_user_id !== null) {
                return false;
            }

            if ($member->linked_user_id) {
                if (isset($seen[$member->linked_user_id])) {
                    return false;
                }
                $seen[$member->linked_user_id] = true;
            }
            return true;
        })->values();

        // Create a pseudo-member object for the owner from users table
        $nameParts = explode(' ', $currentUser->name ?? '', 2);
        $ownerAsMember = (object) [
            'id' => 'owner',
            'first_name' => $nameParts[0] ?? $currentUser->email,
            'last_name' => $nameParts[1] ?? '',
            'full_name' => $currentUser->name ?? $currentUser->email,
            'email' => $currentUser->email,
            'is_owner' => true,
        ];

        return collect([$ownerAsMember])->concat($filteredMembers);
    }
}
