// Asset types

export interface Asset {
  id: number;
  name: string;
  asset_category: AssetCategory;
  asset_type: string;
  description: string | null;
  notes: string | null;

  // Valuation
  acquisition_date: string | null;
  purchase_value: number | null;
  current_value: number | null;
  currency: string;
  formatted_current_value: string | null;

  // Location
  location_address: string | null;
  location_city: string | null;
  location_state: string | null;
  location_zip: string | null;
  location_country: string | null;
  storage_location: string | null;
  room_location: string | null;

  // Status
  status: AssetStatus;
  status_color: string;
  ownership_type: string | null;

  // Insurance
  is_insured: boolean;
  insurance_provider: string | null;
  insurance_policy_number: string | null;
  insurance_renewal_date: string | null;

  // Vehicle-specific
  vehicle_make?: string;
  vehicle_model?: string;
  vehicle_year?: number;
  vin_registration?: string;
  license_plate?: string;
  mileage?: number;

  // Collectable-specific
  collectable_category?: string;
  condition?: string;
  appraised_by?: string;
  appraisal_date?: string;
  appraisal_value?: number;

  // Inventory-specific
  serial_number?: string;
  warranty_expiry?: string;

  // Timestamps
  created_at: string;
  updated_at: string;

  // Relations
  owners?: AssetOwner[];
  documents_count?: number;
}

export interface AssetOwner {
  id: number;
  family_member_id: number | null;
  owner_name: string;
  owner_email: string | null;
  owner_phone: string | null;
  ownership_percentage: number;
  formatted_ownership_percentage: string;
  is_primary_owner: boolean;
  is_family_member: boolean;
  is_external_owner: boolean;
}

export type AssetCategory = 'property' | 'vehicle' | 'valuable' | 'inventory';
export type AssetStatus = 'active' | 'sold' | 'disposed' | 'transferred';

export interface AssetsByCategory {
  [key: string]: {
    count: number;
    total_value: number;
  };
}
