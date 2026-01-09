import apiClient from './client';
import { ExpensesResponse, Expense, ExpenseCategory } from '../types';

const expensesApi = {
  getExpenses: (params?: { status?: string; category_id?: number }) =>
    apiClient.get<ExpensesResponse>('/expenses', { params }),

  getExpense: (id: number) => apiClient.get<Expense>(`/expenses/${id}`),
  createExpense: (data: Partial<Expense>) => apiClient.post('/expenses', data),
  updateExpense: (id: number, data: Partial<Expense>) => apiClient.put(`/expenses/${id}`, data),
  deleteExpense: (id: number) => apiClient.delete(`/expenses/${id}`),
  settleExpense: (id: number) => apiClient.post(`/expenses/${id}/settle`),

  // Categories
  getCategories: () => apiClient.get<{ categories: ExpenseCategory[] }>('/expenses/categories'),
  createCategory: (data: Partial<ExpenseCategory>) => apiClient.post('/expenses/categories', data),
  deleteCategory: (id: number) => apiClient.delete(`/expenses/categories/${id}`),
};

export default expensesApi;
