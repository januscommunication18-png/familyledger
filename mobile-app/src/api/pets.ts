import apiClient from './client';
import { PetsResponse, Pet, Vaccination, Medication } from '../types';

const petsApi = {
  getPets: (params?: { species?: string; include_passed_away?: boolean }) =>
    apiClient.get<PetsResponse>('/pets', { params }),

  getPet: (id: number) => apiClient.get<Pet>(`/pets/${id}`),
  createPet: (data: Partial<Pet>) => apiClient.post('/pets', data),
  updatePet: (id: number, data: Partial<Pet>) => apiClient.put(`/pets/${id}`, data),
  deletePet: (id: number) => apiClient.delete(`/pets/${id}`),

  // Vaccinations
  addVaccination: (petId: number, data: Partial<Vaccination>) =>
    apiClient.post(`/pets/${petId}/vaccinations`, data),
  updateVaccination: (petId: number, vaccinationId: number, data: Partial<Vaccination>) =>
    apiClient.put(`/pets/${petId}/vaccinations/${vaccinationId}`, data),
  deleteVaccination: (petId: number, vaccinationId: number) =>
    apiClient.delete(`/pets/${petId}/vaccinations/${vaccinationId}`),

  // Medications
  addMedication: (petId: number, data: Partial<Medication>) =>
    apiClient.post(`/pets/${petId}/medications`, data),
  updateMedication: (petId: number, medicationId: number, data: Partial<Medication>) =>
    apiClient.put(`/pets/${petId}/medications/${medicationId}`, data),
  deleteMedication: (petId: number, medicationId: number) =>
    apiClient.delete(`/pets/${petId}/medications/${medicationId}`),
};

export default petsApi;
