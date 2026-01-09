<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\InsurancePolicy;
use App\Models\TaxReturn;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return $this->success([
            'policy' => $policy->load('familyMember'),
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

        return $this->success([
            'tax_return' => $taxReturn,
        ]);
    }
}
