import apiClient from './client';
import { JournalResponse, JournalEntry } from '../types';

const journalApi = {
  getEntries: (params?: { type?: string; mood?: string; search?: string; tag?: number }) =>
    apiClient.get<JournalResponse>('/journal', { params }),

  getEntry: (id: number) => apiClient.get<JournalEntry>(`/journal/${id}`),
  createEntry: (data: Partial<JournalEntry>) => apiClient.post('/journal', data),
  updateEntry: (id: number, data: Partial<JournalEntry>) => apiClient.put(`/journal/${id}`, data),
  deleteEntry: (id: number) => apiClient.delete(`/journal/${id}`),
  togglePin: (id: number) => apiClient.post(`/journal/${id}/toggle-pin`),
};

export default journalApi;
