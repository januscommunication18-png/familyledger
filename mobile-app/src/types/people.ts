export type RelationshipType = 'family' | 'friend' | 'work' | 'acquaintance' | 'neighbor' | 'doctor' | 'service_provider' | 'other';

export interface Person {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  nickname?: string;
  relationship: RelationshipType;
  relationship_name: string;
  custom_relationship?: string;
  company?: string;
  job_title?: string;
  birthday?: string;
  birthday_raw?: string;
  age?: number;
  profile_image_url?: string;
  primary_email?: { email: string };
  primary_phone?: { phone: string; formatted_phone: string };
  tags?: string[];
  notes?: string;
  met_at?: string;
  met_location?: string;
  created_at?: string;
  updated_at?: string;
}

export interface PersonEmail {
  id: number;
  email: string;
  label: string;
  is_primary: boolean;
}

export interface PersonPhone {
  id: number;
  phone: string;
  formatted_phone: string;
  label: string;
  is_primary: boolean;
}

export interface PersonAddress {
  id: number;
  label: string;
  street?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  full_address: string;
  is_primary: boolean;
}

export interface PersonImportantDate {
  id: number;
  label: string;
  date: string;
  date_raw: string;
  is_annual: boolean;
}

export interface PersonLink {
  id: number;
  label: string;
  url: string;
}

export interface PersonAttachment {
  id: number;
  name: string;
  file_type?: string;
  mime_type?: string;
  file_size?: number;
  formatted_size?: string;
  is_image: boolean;
}

export interface PeopleResponse {
  people: Person[];
  total: number;
  by_relationship?: Record<string, number>;
}

export interface PersonDetailResponse {
  person: Person;
  emails: PersonEmail[];
  phones: PersonPhone[];
  addresses: PersonAddress[];
  important_dates: PersonImportantDate[];
  links: PersonLink[];
  attachments: PersonAttachment[];
  stats: {
    emails: number;
    phones: number;
    addresses: number;
    important_dates: number;
    attachments: number;
  };
}
