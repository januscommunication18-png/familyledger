import apiClient from './client';
import { PeopleResponse, Person, PersonDetailResponse } from '../types/people';

// API returns wrapped response { success: true, data: T }
interface ApiResponse<T> {
  success: boolean;
  data: T;
}

const peopleApi = {
  getPeople: (params?: { search?: string; relationship?: string; tag?: string }) =>
    apiClient.get<ApiResponse<PeopleResponse>>('/people', { params }),

  getPerson: (id: number) => apiClient.get<ApiResponse<PersonDetailResponse>>(`/people/${id}`),

  searchPeople: (query: string) =>
    apiClient.get<ApiResponse<{ people: Person[]; total: number }>>('/people/search', { params: { q: query } }),

  getByRelationship: (relationship: string) =>
    apiClient.get<ApiResponse<{ people: Person[]; total: number }>>(`/people/relationship/${relationship}`),

  createPerson: (data: Partial<Person>) => apiClient.post('/people', data),
  updatePerson: (id: number, data: Partial<Person>) => apiClient.put(`/people/${id}`, data),
  deletePerson: (id: number) => apiClient.delete(`/people/${id}`),
};

export default peopleApi;
