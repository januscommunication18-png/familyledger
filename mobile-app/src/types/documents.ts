export interface InsurancePolicy {
  id: number;
  policy_type: 'health' | 'life' | 'auto' | 'home' | 'other';
  provider: string;
  policy_number: string;
  coverage_amount?: number;
  premium?: number;
  premium_frequency?: 'monthly' | 'quarterly' | 'annually';
  start_date?: string;
  end_date?: string;
  beneficiaries?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface TaxReturn {
  id: number;
  tax_year: number;
  filing_status: 'single' | 'married_filing_jointly' | 'married_filing_separately' | 'head_of_household';
  gross_income?: number;
  adjusted_gross_income?: number;
  total_tax?: number;
  refund_amount?: number;
  amount_owed?: number;
  filing_date?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface DocumentsResponse {
  insurance_policies: InsurancePolicy[];
  tax_returns: TaxReturn[];
}
