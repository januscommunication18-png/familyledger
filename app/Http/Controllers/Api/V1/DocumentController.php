<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\InsurancePolicy;
use App\Models\TaxReturn;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Get all documents (insurance, tax returns, legal).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $insurancePolicies = InsurancePolicy::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $taxReturns = TaxReturn::where('tenant_id', $tenant->id)
            ->orderBy('tax_year', 'desc')
            ->get();

        $legalDocuments = LegalDocument::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'insurance_policies' => $insurancePolicies,
            'tax_returns' => $taxReturns,
            'legal_documents' => $legalDocuments,
            'counts' => [
                'insurance' => $insurancePolicies->count(),
                'tax' => $taxReturns->count(),
                'legal' => $legalDocuments->count(),
            ],
        ]);
    }

    /**
     * Get insurance policies.
     */
    public function insurancePolicies(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $policies = InsurancePolicy::where('tenant_id', $tenant->id)
            ->with(['familyMember'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'policies' => $policies,
            'total' => $policies->count(),
        ]);
    }

    /**
     * Get a single insurance policy.
     */
    public function showInsurancePolicy(Request $request, InsurancePolicy $policy): JsonResponse
    {
        $user = $request->user();

        if ($policy->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $policy->load(['familyMember', 'policyholders', 'coveredMembers']);

        // Add image URLs
        $policyData = $policy->toArray();
        $policyData['card_front_image_url'] = $policy->card_front_image
            ? Storage::disk('do_spaces')->url($policy->card_front_image)
            : null;
        $policyData['card_back_image_url'] = $policy->card_back_image
            ? Storage::disk('do_spaces')->url($policy->card_back_image)
            : null;

        return $this->success([
            'insurance_policy' => $policyData,
        ]);
    }

    /**
     * Get tax returns.
     */
    public function taxReturns(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $returns = TaxReturn::where('tenant_id', $tenant->id)
            ->orderBy('tax_year', 'desc')
            ->get();

        return $this->success([
            'returns' => $returns,
            'total' => $returns->count(),
        ]);
    }

    /**
     * Get a single tax return.
     */
    public function showTaxReturn(Request $request, TaxReturn $taxReturn): JsonResponse
    {
        $user = $request->user();

        if ($taxReturn->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $returnData = $taxReturn->toArray();

        // Convert file paths to URLs
        $returnData['federal_returns_urls'] = $this->convertPathsToUrls($taxReturn->federal_returns);
        $returnData['state_returns_urls'] = $this->convertPathsToUrls($taxReturn->state_returns);
        $returnData['supporting_documents_urls'] = $this->convertPathsToUrls($taxReturn->supporting_documents);

        return $this->success([
            'tax_return' => $returnData,
        ]);
    }

    /**
     * Convert array of file paths to URLs.
     */
    private function convertPathsToUrls(?array $paths): array
    {
        if (!$paths) {
            return [];
        }

        return array_map(function ($path) {
            return [
                'path' => $path,
                'url' => Storage::disk('do_spaces')->url($path),
                'name' => basename($path),
            ];
        }, $paths);
    }

    /**
     * Store a new insurance policy.
     */
    public function storeInsurancePolicy(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'insurance_type' => 'required|string|in:health,dental,vision,life,auto,home,renters,umbrella,disability,long_term_care,pet,travel,other',
            'provider_name' => 'required|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'group_number' => 'nullable|string|max:100',
            'plan_name' => 'nullable|string|max:255',
            'premium_amount' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string|in:monthly,quarterly,semi_annual,annual,one_time',
            'effective_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,pending,expired,cancelled',
            'agent_name' => 'nullable|string|max:255',
            'agent_phone' => 'nullable|string|max:20',
            'agent_email' => 'nullable|email|max:255',
            'claims_phone' => 'nullable|string|max:20',
            'coverage_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'card_front_image' => 'nullable|string',
            'card_back_image' => 'nullable|string',
            'policyholders' => 'nullable|array',
            'policyholders.*' => 'integer|exists:family_members,id',
            'covered_members' => 'nullable|array',
            'covered_members.*' => 'integer|exists:family_members,id',
        ]);

        // Handle card images
        $cardFrontPath = null;
        $cardBackPath = null;

        if (!empty($validated['card_front_image'])) {
            $cardFrontPath = $this->saveBase64Image($validated['card_front_image'], 'insurance-cards', $user->tenant_id);
        }

        if (!empty($validated['card_back_image'])) {
            $cardBackPath = $this->saveBase64Image($validated['card_back_image'], 'insurance-cards', $user->tenant_id);
        }

        $policy = InsurancePolicy::create([
            'tenant_id' => $user->tenant_id,
            'insurance_type' => $validated['insurance_type'],
            'provider_name' => $validated['provider_name'],
            'policy_number' => $validated['policy_number'] ?? null,
            'group_number' => $validated['group_number'] ?? null,
            'plan_name' => $validated['plan_name'] ?? null,
            'premium_amount' => $validated['premium_amount'] ?? null,
            'payment_frequency' => $validated['payment_frequency'] ?? null,
            'effective_date' => $validated['effective_date'] ?? null,
            'expiration_date' => $validated['expiration_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'agent_name' => $validated['agent_name'] ?? null,
            'agent_phone' => $validated['agent_phone'] ?? null,
            'agent_email' => $validated['agent_email'] ?? null,
            'claims_phone' => $validated['claims_phone'] ?? null,
            'coverage_details' => $validated['coverage_details'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'card_front_image' => $cardFrontPath,
            'card_back_image' => $cardBackPath,
        ]);

        // Attach policyholders
        if (!empty($validated['policyholders'])) {
            foreach ($validated['policyholders'] as $memberId) {
                $policy->policyholders()->attach($memberId, ['member_type' => 'policyholder']);
            }
        }

        // Attach covered members
        if (!empty($validated['covered_members'])) {
            foreach ($validated['covered_members'] as $memberId) {
                $policy->coveredMembers()->attach($memberId, ['member_type' => 'covered']);
            }
        }

        return $this->success([
            'insurance_policy' => $policy,
        ], 'Insurance policy created successfully', 201);
    }

    /**
     * Save a base64 encoded image to storage.
     */
    private function saveBase64Image(string $base64Image, string $folder, string $tenantId): ?string
    {
        try {
            // Extract base64 data from data URI
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $extension = $matches[1];
                $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
            } else {
                // Assume it's raw base64 data
                $base64Data = $base64Image;
                $extension = 'jpg';
            }

            $imageData = base64_decode($base64Data);
            if ($imageData === false) {
                return null;
            }

            $filename = $folder . '/' . $tenantId . '/' . uniqid() . '.' . $extension;
            Storage::disk('do_spaces')->put($filename, $imageData, 'private');

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Failed to save base64 image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store a new tax return.
     */
    public function storeTaxReturn(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'filing_status' => 'nullable|string|in:single,married_filing_jointly,married_filing_separately,head_of_household,qualifying_widow',
            'status' => 'nullable|string|in:not_started,gathering_docs,in_progress,review,filed,amended',
            'tax_jurisdiction' => 'nullable|string|in:federal,state,both',
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
        ]);

        $taxReturn = TaxReturn::create([
            'tenant_id' => $user->tenant_id,
            'tax_year' => $validated['tax_year'],
            'filing_status' => $validated['filing_status'] ?? null,
            'status' => $validated['status'] ?? 'not_started',
            'tax_jurisdiction' => $validated['tax_jurisdiction'] ?? 'federal',
            'state_jurisdiction' => $validated['state_jurisdiction'] ?? null,
            'cpa_name' => $validated['cpa_name'] ?? null,
            'cpa_phone' => $validated['cpa_phone'] ?? null,
            'cpa_email' => $validated['cpa_email'] ?? null,
            'cpa_firm' => $validated['cpa_firm'] ?? null,
            'filing_date' => $validated['filing_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'refund_amount' => $validated['refund_amount'] ?? null,
            'amount_owed' => $validated['amount_owed'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success([
            'tax_return' => $taxReturn,
        ], 'Tax return created successfully', 201);
    }
}
