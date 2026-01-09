export type PetSpecies = 'dog' | 'cat' | 'bird' | 'fish' | 'reptile' | 'small_mammal' | 'other';

export interface Pet {
  id: number;
  name: string;
  species: PetSpecies;
  species_label: string;
  species_emoji: string;
  breed?: string;
  date_of_birth?: string;
  age?: string;
  gender?: 'male' | 'female' | 'unknown';
  color?: string;
  weight?: number;
  microchip_id?: string;
  photo_url?: string;
  is_passed_away: boolean;
  passed_away_date?: string;
  vet_name?: string;
  vet_phone?: string;
  notes?: string;
  caregivers?: { id: number; first_name: string }[];
  overdue_vaccinations?: Vaccination[];
  upcoming_vaccinations?: Vaccination[];
  active_medications?: Medication[];
  created_at: string;
  updated_at: string;
}

export interface Vaccination {
  id: number;
  name: string;
  date_given: string;
  next_due_date?: string;
  administered_by?: string;
  notes?: string;
}

export interface Medication {
  id: number;
  name: string;
  dosage?: string;
  frequency?: string;
  start_date?: string;
  end_date?: string;
  notes?: string;
}

export interface PetsResponse {
  pets: Pet[];
  total_pets: number;
  upcoming_vaccinations: number;
  overdue_vaccinations: number;
}
