<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\InsurancePolicy;
use App\Models\TaxReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display the documents index with tabs.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'insurance');

        $insurancePolicies = InsurancePolicy::where('tenant_id', $user->tenant_id)
            ->with(['policyholders', 'coveredMembers'])
            ->orderBy('created_at', 'desc')
            ->get();

        $taxReturns = TaxReturn::where('tenant_id', $user->tenant_id)
            ->with('taxpayers')
            ->orderBy('tax_year', 'desc')
            ->get();

        $familyMembers = $this->getUniqueFamilyMembers($user->tenant_id);

        return view('pages.documents.index', [
            'tab' => $tab,
            'insurancePolicies' => $insurancePolicies,
            'taxReturns' => $taxReturns,
            'familyMembers' => $familyMembers,
            'insuranceTypes' => InsurancePolicy::INSURANCE_TYPES,
            'insuranceStatuses' => InsurancePolicy::STATUSES,
            'paymentFrequencies' => InsurancePolicy::PAYMENT_FREQUENCIES,
            'filingStatuses' => TaxReturn::FILING_STATUSES,
            'taxStatuses' => TaxReturn::STATUSES,
            'jurisdictions' => TaxReturn::JURISDICTIONS,
            'usStates' => TaxReturn::US_STATES,
        ]);
    }

    /**
     * Show insurance create/edit form.
     */
    public function createInsurance()
    {
        $user = Auth::user();
        $familyMembers = $this->getUniqueFamilyMembers($user->tenant_id);

        return view('pages.documents.insurance-form', [
            'insurance' => null,
            'familyMembers' => $familyMembers,
            'insuranceTypes' => InsurancePolicy::INSURANCE_TYPES,
            'statuses' => InsurancePolicy::STATUSES,
            'paymentFrequencies' => InsurancePolicy::PAYMENT_FREQUENCIES,
        ]);
    }

    /**
     * Get unique family members (deduplicate linked members across circles).
     * Includes the owner's family_member record (if exists) with is_owner flag.
     */
    private function getUniqueFamilyMembers($tenantId)
    {
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;

        $members = FamilyMember::where('tenant_id', $tenantId)
            ->orderBy('first_name')
            ->get();

        // Find owner's family_member record (first one linked to current user)
        $ownerFamilyMember = $members->first(function ($member) use ($currentUserId) {
            return $member->linked_user_id == $currentUserId && $member->linked_user_id !== null;
        });

        // Deduplicate: keep first occurrence of each linked_user_id
        $seen = [];
        $filteredMembers = $members->filter(function ($member) use (&$seen, $currentUserId, $ownerFamilyMember) {
            // Skip owner's duplicate family_member records (keep only the first one)
            if ($member->linked_user_id == $currentUserId && $member->linked_user_id !== null) {
                if ($ownerFamilyMember && $member->id == $ownerFamilyMember->id) {
                    // Mark this as owner and include it
                    $member->is_owner = true;
                    return true;
                }
                return false; // Skip duplicate owner records
            }

            // Deduplicate other linked members
            if ($member->linked_user_id) {
                if (isset($seen[$member->linked_user_id])) {
                    return false;
                }
                $seen[$member->linked_user_id] = true;
            }
            return true;
        })->values();

        // If owner has a family_member record, it's already in the list with is_owner flag
        // If not, create a pseudo-member that can't be saved (with is_owner flag for display)
        if ($ownerFamilyMember) {
            // Move owner to the beginning of the list
            $ownerRecord = $filteredMembers->first(fn($m) => $m->id == $ownerFamilyMember->id);
            $filteredMembers = $filteredMembers->filter(fn($m) => $m->id != $ownerFamilyMember->id);
            return collect([$ownerRecord])->concat($filteredMembers);
        }

        // No family_member record for owner - they need to add themselves to a family circle first
        // We can still show them in the dropdown but warn in the UI
        $nameParts = explode(' ', $currentUser->name ?? '', 2);
        $ownerAsMember = (object) [
            'id' => null, // Cannot be saved - owner needs to add themselves to a family circle
            'first_name' => $nameParts[0] ?? $currentUser->email,
            'last_name' => $nameParts[1] ?? '',
            'full_name' => $currentUser->name ?? $currentUser->email,
            'is_owner' => true,
            'no_family_member_record' => true, // Flag to show warning in UI
        ];

        return collect([$ownerAsMember])->concat($filteredMembers);
    }

    /**
     * Store a new insurance policy.
     */
    public function storeInsurance(Request $request)
    {
        $validated = $request->validate([
            'insurance_type' => 'required|string|in:' . implode(',', array_keys(InsurancePolicy::INSURANCE_TYPES)),
            'provider_name' => 'required|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'group_number' => 'nullable|string|max:100',
            'plan_name' => 'nullable|string|max:255',
            'premium_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string|in:' . implode(',', array_keys(InsurancePolicy::PAYMENT_FREQUENCIES)),
            'effective_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'status' => 'nullable|string|in:' . implode(',', array_keys(InsurancePolicy::STATUSES)),
            'policyholders' => 'nullable|array',
            'policyholders.*' => 'string',
            'agent_name' => 'nullable|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'agent_email' => 'nullable|email|max:255',
            'claims_phone' => 'nullable|string|max:20',
            'coverage_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'card_front_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'card_back_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'covered_members' => 'nullable|array',
            'covered_members.*' => 'string',
        ]);

        $data = collect($validated)->except(['card_front_image', 'card_back_image', 'covered_members', 'policyholders'])->toArray();
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['status'] = $data['status'] ?? 'active';

        // Handle file uploads
        if ($request->hasFile('card_front_image')) {
            $data['card_front_image'] = $request->file('card_front_image')->store(
                'documents/insurance/cards',
                'private'
            );
        }

        if ($request->hasFile('card_back_image')) {
            $data['card_back_image'] = $request->file('card_back_image')->store(
                'documents/insurance/cards',
                'private'
            );
        }

        $insurance = InsurancePolicy::create($data);

        // Attach policyholders (filter out empty/null values and duplicates)
        if (!empty($validated['policyholders'])) {
            $policyholderIds = collect($validated['policyholders'])
                ->filter(fn($id) => !empty($id) && is_numeric($id))
                ->unique()
                ->values()
                ->toArray();
            if (!empty($policyholderIds)) {
                $syncData = [];
                foreach ($policyholderIds as $id) {
                    $syncData[$id] = ['member_type' => 'policyholder'];
                }
                $insurance->policyholders()->syncWithoutDetaching($syncData);
            }
        }

        // Attach covered members (filter out empty/null values, duplicates, and those already added as policyholders)
        if (!empty($validated['covered_members'])) {
            $coveredIds = collect($validated['covered_members'])
                ->filter(fn($id) => !empty($id) && is_numeric($id))
                ->unique()
                ->values()
                ->toArray();
            // Remove any IDs that were already added as policyholders (since unique constraint doesn't allow same member twice)
            $existingMemberIds = $insurance->policyholders()->pluck('family_members.id')->toArray();
            $coveredIds = array_diff($coveredIds, $existingMemberIds);
            if (!empty($coveredIds)) {
                $syncData = [];
                foreach ($coveredIds as $id) {
                    $syncData[$id] = ['member_type' => 'covered'];
                }
                $insurance->coveredMembers()->syncWithoutDetaching($syncData);
            }
        }

        return redirect()->route('documents.index', ['tab' => 'insurance'])
            ->with('success', 'Insurance policy added successfully');
    }

    /**
     * Show insurance details.
     */
    public function showInsurance(InsurancePolicy $insurance)
    {
        if ($insurance->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $insurance->load(['policyholders', 'coveredMembers']);

        return view('pages.documents.insurance-show', [
            'insurance' => $insurance,
            'insuranceTypes' => InsurancePolicy::INSURANCE_TYPES,
            'statuses' => InsurancePolicy::STATUSES,
            'paymentFrequencies' => InsurancePolicy::PAYMENT_FREQUENCIES,
        ]);
    }

    /**
     * Show insurance edit form.
     */
    public function editInsurance(InsurancePolicy $insurance)
    {
        if ($insurance->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $familyMembers = $this->getUniqueFamilyMembers(Auth::user()->tenant_id);

        return view('pages.documents.insurance-form', [
            'insurance' => $insurance,
            'familyMembers' => $familyMembers,
            'insuranceTypes' => InsurancePolicy::INSURANCE_TYPES,
            'statuses' => InsurancePolicy::STATUSES,
            'paymentFrequencies' => InsurancePolicy::PAYMENT_FREQUENCIES,
        ]);
    }

    /**
     * Update an insurance policy.
     */
    public function updateInsurance(Request $request, InsurancePolicy $insurance)
    {
        if ($insurance->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'insurance_type' => 'required|string|in:' . implode(',', array_keys(InsurancePolicy::INSURANCE_TYPES)),
            'provider_name' => 'required|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'group_number' => 'nullable|string|max:100',
            'plan_name' => 'nullable|string|max:255',
            'premium_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string|in:' . implode(',', array_keys(InsurancePolicy::PAYMENT_FREQUENCIES)),
            'effective_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'status' => 'nullable|string|in:' . implode(',', array_keys(InsurancePolicy::STATUSES)),
            'policyholders' => 'nullable|array',
            'policyholders.*' => 'string',
            'agent_name' => 'nullable|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'agent_email' => 'nullable|email|max:255',
            'claims_phone' => 'nullable|string|max:20',
            'coverage_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'card_front_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'card_back_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'covered_members' => 'nullable|array',
            'covered_members.*' => 'string',
        ]);

        $data = collect($validated)->except(['card_front_image', 'card_back_image', 'covered_members', 'policyholders'])->toArray();

        // Handle file uploads
        if ($request->hasFile('card_front_image')) {
            if ($insurance->card_front_image) {
                Storage::disk('private')->delete($insurance->card_front_image);
            }
            $data['card_front_image'] = $request->file('card_front_image')->store(
                'documents/insurance/cards',
                'private'
            );
        }

        if ($request->hasFile('card_back_image')) {
            if ($insurance->card_back_image) {
                Storage::disk('private')->delete($insurance->card_back_image);
            }
            $data['card_back_image'] = $request->file('card_back_image')->store(
                'documents/insurance/cards',
                'private'
            );
        }

        $insurance->update($data);

        // Sync policyholders (filter out empty/null values and duplicates)
        $policyholderIds = isset($validated['policyholders'])
            ? collect($validated['policyholders'])->filter(fn($id) => !empty($id) && is_numeric($id))->unique()->values()->toArray()
            : [];

        // Sync covered members (filter out empty/null values and duplicates)
        $coveredIds = isset($validated['covered_members'])
            ? collect($validated['covered_members'])->filter(fn($id) => !empty($id) && is_numeric($id))->unique()->values()->toArray()
            : [];

        // Remove any covered IDs that are also policyholders (unique constraint prevents same member twice)
        $coveredIds = array_diff($coveredIds, $policyholderIds);

        // Detach all existing members first
        \DB::table('insurance_policy_members')->where('insurance_policy_id', $insurance->id)->delete();

        // Re-attach policyholders
        if (!empty($policyholderIds)) {
            $syncData = [];
            foreach ($policyholderIds as $id) {
                $syncData[$id] = ['member_type' => 'policyholder'];
            }
            $insurance->policyholders()->syncWithoutDetaching($syncData);
        }

        // Re-attach covered members
        if (!empty($coveredIds)) {
            $syncData = [];
            foreach ($coveredIds as $id) {
                $syncData[$id] = ['member_type' => 'covered'];
            }
            $insurance->coveredMembers()->syncWithoutDetaching($syncData);
        }

        return redirect()->route('documents.index', ['tab' => 'insurance'])
            ->with('success', 'Insurance policy updated successfully');
    }

    /**
     * Delete an insurance policy.
     */
    public function destroyInsurance(InsurancePolicy $insurance)
    {
        if ($insurance->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete uploaded files
        if ($insurance->card_front_image) {
            Storage::disk('private')->delete($insurance->card_front_image);
        }
        if ($insurance->card_back_image) {
            Storage::disk('private')->delete($insurance->card_back_image);
        }

        $insurance->delete();

        return redirect()->route('documents.index', ['tab' => 'insurance'])
            ->with('success', 'Insurance policy deleted successfully');
    }

    /**
     * Show tax return create/edit form.
     */
    public function createTaxReturn()
    {
        $user = Auth::user();
        $familyMembers = $this->getUniqueFamilyMembers($user->tenant_id);

        return view('pages.documents.tax-return-form', [
            'taxReturn' => null,
            'familyMembers' => $familyMembers,
            'filingStatuses' => TaxReturn::FILING_STATUSES,
            'statuses' => TaxReturn::STATUSES,
            'jurisdictions' => TaxReturn::JURISDICTIONS,
            'usStates' => TaxReturn::US_STATES,
        ]);
    }

    /**
     * Store a new tax return.
     */
    public function storeTaxReturn(Request $request)
    {
        $validated = $request->validate([
            'tax_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'taxpayers' => 'nullable|array',
            'taxpayers.*' => 'string',
            'filing_status' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::FILING_STATUSES)),
            'status' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::STATUSES)),
            'tax_jurisdiction' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::JURISDICTIONS)),
            'state_jurisdiction' => 'nullable|string|max:2',
            'cpa_name' => 'nullable|string|max:255',
            'cpa_phone' => 'nullable|string|max:20',
            'cpa_email' => 'nullable|email|max:255',
            'cpa_firm' => 'nullable|string|max:255',
            'filing_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'refund_amount' => 'nullable|numeric|min:0',
            'amount_owed' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'federal_returns' => 'nullable|array',
            'federal_returns.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'state_returns' => 'nullable|array',
            'state_returns.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = collect($validated)->except(['federal_returns', 'state_returns', 'supporting_documents', 'taxpayers'])->toArray();
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['status'] = $data['status'] ?? 'not_started';
        $data['tax_jurisdiction'] = $data['tax_jurisdiction'] ?? 'federal';

        // Handle file uploads
        if ($request->hasFile('federal_returns')) {
            $paths = [];
            foreach ($request->file('federal_returns') as $file) {
                $paths[] = $file->store('documents/tax-returns/federal', 'private');
            }
            $data['federal_returns'] = $paths;
        }

        if ($request->hasFile('state_returns')) {
            $paths = [];
            foreach ($request->file('state_returns') as $file) {
                $paths[] = $file->store('documents/tax-returns/state', 'private');
            }
            $data['state_returns'] = $paths;
        }

        if ($request->hasFile('supporting_documents')) {
            $paths = [];
            foreach ($request->file('supporting_documents') as $file) {
                $paths[] = $file->store('documents/tax-returns/supporting', 'private');
            }
            $data['supporting_documents'] = $paths;
        }

        $taxReturn = TaxReturn::create($data);

        // Attach taxpayers (filter out empty/null values)
        if (!empty($validated['taxpayers'])) {
            $taxpayerIds = collect($validated['taxpayers'])
                ->filter(fn($id) => !empty($id) && is_numeric($id))
                ->toArray();
            if (!empty($taxpayerIds)) {
                $taxReturn->taxpayers()->attach($taxpayerIds);
            }
        }

        return redirect()->route('documents.index', ['tab' => 'tax-returns'])
            ->with('success', 'Tax return added successfully');
    }

    /**
     * Show tax return details.
     */
    public function showTaxReturn(TaxReturn $taxReturn)
    {
        if ($taxReturn->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $taxReturn->load('taxpayers');

        return view('pages.documents.tax-return-show', [
            'taxReturn' => $taxReturn,
            'filingStatuses' => TaxReturn::FILING_STATUSES,
            'statuses' => TaxReturn::STATUSES,
            'jurisdictions' => TaxReturn::JURISDICTIONS,
            'usStates' => TaxReturn::US_STATES,
        ]);
    }

    /**
     * Show tax return edit form.
     */
    public function editTaxReturn(TaxReturn $taxReturn)
    {
        if ($taxReturn->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $familyMembers = $this->getUniqueFamilyMembers(Auth::user()->tenant_id);

        return view('pages.documents.tax-return-form', [
            'taxReturn' => $taxReturn,
            'familyMembers' => $familyMembers,
            'filingStatuses' => TaxReturn::FILING_STATUSES,
            'statuses' => TaxReturn::STATUSES,
            'jurisdictions' => TaxReturn::JURISDICTIONS,
            'usStates' => TaxReturn::US_STATES,
        ]);
    }

    /**
     * Update a tax return.
     */
    public function updateTaxReturn(Request $request, TaxReturn $taxReturn)
    {
        if ($taxReturn->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'tax_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'taxpayers' => 'nullable|array',
            'taxpayers.*' => 'string',
            'filing_status' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::FILING_STATUSES)),
            'status' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::STATUSES)),
            'tax_jurisdiction' => 'nullable|string|in:' . implode(',', array_keys(TaxReturn::JURISDICTIONS)),
            'state_jurisdiction' => 'nullable|string|max:2',
            'cpa_name' => 'nullable|string|max:255',
            'cpa_phone' => 'nullable|string|max:20',
            'cpa_email' => 'nullable|email|max:255',
            'cpa_firm' => 'nullable|string|max:255',
            'filing_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'refund_amount' => 'nullable|numeric|min:0',
            'amount_owed' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'federal_returns' => 'nullable|array',
            'federal_returns.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'state_returns' => 'nullable|array',
            'state_returns.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = collect($validated)->except(['federal_returns', 'state_returns', 'supporting_documents', 'taxpayers'])->toArray();

        // Handle file uploads (append to existing)
        if ($request->hasFile('federal_returns')) {
            $paths = $taxReturn->federal_returns ?? [];
            foreach ($request->file('federal_returns') as $file) {
                $paths[] = $file->store('documents/tax-returns/federal', 'private');
            }
            $data['federal_returns'] = $paths;
        }

        if ($request->hasFile('state_returns')) {
            $paths = $taxReturn->state_returns ?? [];
            foreach ($request->file('state_returns') as $file) {
                $paths[] = $file->store('documents/tax-returns/state', 'private');
            }
            $data['state_returns'] = $paths;
        }

        if ($request->hasFile('supporting_documents')) {
            $paths = $taxReturn->supporting_documents ?? [];
            foreach ($request->file('supporting_documents') as $file) {
                $paths[] = $file->store('documents/tax-returns/supporting', 'private');
            }
            $data['supporting_documents'] = $paths;
        }

        $taxReturn->update($data);

        // Sync taxpayers (filter out empty/null values)
        $taxpayerIds = isset($validated['taxpayers'])
            ? collect($validated['taxpayers'])->filter(fn($id) => !empty($id) && is_numeric($id))->toArray()
            : [];
        $taxReturn->taxpayers()->sync($taxpayerIds);

        return redirect()->route('documents.index', ['tab' => 'tax-returns'])
            ->with('success', 'Tax return updated successfully');
    }

    /**
     * Delete a tax return.
     */
    public function destroyTaxReturn(TaxReturn $taxReturn)
    {
        if ($taxReturn->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete uploaded files
        foreach ($taxReturn->federal_returns ?? [] as $path) {
            Storage::disk('private')->delete($path);
        }
        foreach ($taxReturn->state_returns ?? [] as $path) {
            Storage::disk('private')->delete($path);
        }
        foreach ($taxReturn->supporting_documents ?? [] as $path) {
            Storage::disk('private')->delete($path);
        }

        $taxReturn->delete();

        return redirect()->route('documents.index', ['tab' => 'tax-returns'])
            ->with('success', 'Tax return deleted successfully');
    }

    /**
     * Serve insurance card image.
     */
    public function insuranceCardImage(InsurancePolicy $insurance, string $type)
    {
        if ($insurance->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $path = $type === 'front' ? $insurance->card_front_image : $insurance->card_back_image;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }

    /**
     * Download a tax return file.
     */
    public function downloadTaxReturnFile(TaxReturn $taxReturn, string $type, int $index)
    {
        if ($taxReturn->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $files = match ($type) {
            'federal' => $taxReturn->federal_returns,
            'state' => $taxReturn->state_returns,
            'supporting' => $taxReturn->supporting_documents,
            default => null,
        };

        if (!$files || !isset($files[$index])) {
            abort(404);
        }

        $path = $files[$index];

        if (!Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->download($path, basename($path));
    }
}
