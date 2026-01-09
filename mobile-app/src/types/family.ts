// Family Circle and Member types

export interface FamilyCircle {
  id: number;
  name: string;
  description: string | null;
  cover_image_url: string | null;
  members_count: number;
  created_at: string;
  updated_at: string;
  members?: FamilyMember[];
}

export interface FamilyMember {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string | null;
  phone: string | null;
  date_of_birth: string | null;
  age: number | null;
  relationship: RelationshipType;
  relationship_name: string;
  is_minor: boolean;
  profile_image_url: string | null;
  immigration_status: string | null;
  immigration_status_name: string | null;
  co_parenting_enabled: boolean;
  created_at: string;
  updated_at: string;
  documents_count?: number;
}

export type RelationshipType =
  | 'self'
  | 'spouse'
  | 'partner'
  | 'child'
  | 'stepchild'
  | 'parent'
  | 'sibling'
  | 'grandparent'
  | 'guardian'
  | 'caregiver'
  | 'relative'
  | 'other';
