import apiClient from './client';
import { GoalsResponse, Goal, Task } from '../types';

const goalsApi = {
  getGoalsAndTasks: (params?: { tab?: string }) =>
    apiClient.get<GoalsResponse>('/goals-todo', { params }),

  // Goals
  getGoal: (id: number) => apiClient.get<Goal>(`/goals-todo/goals/${id}`),
  createGoal: (data: Partial<Goal>) => apiClient.post('/goals-todo/goals', data),
  updateGoal: (id: number, data: Partial<Goal>) => apiClient.put(`/goals-todo/goals/${id}`, data),
  deleteGoal: (id: number) => apiClient.delete(`/goals-todo/goals/${id}`),
  updateGoalProgress: (id: number, progress: number) =>
    apiClient.post(`/goals-todo/goals/${id}/progress`, { progress }),

  // Tasks
  getTask: (id: number) => apiClient.get<Task>(`/goals-todo/tasks/${id}`),
  createTask: (data: Partial<Task>) => apiClient.post('/goals-todo/tasks', data),
  updateTask: (id: number, data: Partial<Task>) => apiClient.put(`/goals-todo/tasks/${id}`, data),
  deleteTask: (id: number) => apiClient.delete(`/goals-todo/tasks/${id}`),
  toggleTask: (id: number) => apiClient.post(`/goals-todo/tasks/${id}/toggle`),
  snoozeTask: (id: number, until: string) =>
    apiClient.post(`/goals-todo/tasks/${id}/snooze`, { until }),
};

export default goalsApi;
