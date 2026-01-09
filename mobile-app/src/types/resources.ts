export type ResourceType = 'emergency' | 'evacuation_plan' | 'fire_extinguisher' | 'rental_agreement' | 'home_warranty' | 'other';

export interface FamilyResource {
  id: number;
  name: string;
  document_type: ResourceType;
  document_type_name: string;
  custom_document_type?: string;
  description?: string;
  original_location?: string;
  status: 'active' | 'expired' | 'archived';
  status_name: string;
  digital_copy_date?: string;
  digital_copy_date_raw?: string;
  expiration_date?: string;
  expiration_date_raw?: string;
  is_expired?: boolean;
  notes?: string;
  files?: ResourceFile[];
  created_by?: {
    id: number;
    name: string;
  };
  created_at?: string;
  updated_at?: string;
}

export interface ResourceFile {
  id: number;
  name: string;
  file_path?: string;
  mime_type?: string;
  file_size?: number;
  formatted_size?: string;
  is_image?: boolean;
  is_pdf?: boolean;
  download_url?: string;
  view_url?: string;
  created_at?: string;
}

export interface ResourceCounts {
  total?: number;
  emergency: number;
  evacuation: number;
  fire: number;
  rental: number;
  warranty: number;
  other: number;
}

export interface ResourcesResponse {
  resources: FamilyResource[];
  counts: ResourceCounts;
  total: number;
}

export interface ResourceDetailResponse {
  resource: FamilyResource;
  files: ResourceFile[];
  stats: {
    total_files: number;
    images: number;
    documents: number;
  };
}
