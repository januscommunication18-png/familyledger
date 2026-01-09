import apiClient from './client';
import { RemindersResponse, Reminder } from '../types';

const remindersApi = {
  getReminders: (params?: { status?: string; category?: string }) =>
    apiClient.get<RemindersResponse>('/reminders', { params }),

  getReminder: (id: number) => apiClient.get<Reminder>(`/reminders/${id}`),
  createReminder: (data: Partial<Reminder>) => apiClient.post('/reminders', data),
  updateReminder: (id: number, data: Partial<Reminder>) => apiClient.put(`/reminders/${id}`, data),
  deleteReminder: (id: number) => apiClient.delete(`/reminders/${id}`),
  completeReminder: (id: number) => apiClient.post(`/reminders/${id}/complete`),
  snoozeReminder: (id: number, until: string) =>
    apiClient.post(`/reminders/${id}/snooze`, { until }),
};

export default remindersApi;
