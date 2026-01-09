import apiClient from './client';

const budgetsApi = {
  getBudgets: () => apiClient.get('/budgets'),
  getBudget: (id: number) => apiClient.get(`/budgets/${id}`),
};

export default budgetsApi;
