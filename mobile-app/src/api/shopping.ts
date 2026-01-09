import apiClient from './client';
import { ShoppingListsResponse, ShoppingListDetailResponse, ShoppingList, ShoppingItem } from '../types';

// API returns wrapped response { success: true, data: T }
interface ApiResponse<T> {
  success: boolean;
  data: T;
}

const shoppingApi = {
  getLists: () => apiClient.get<ApiResponse<ShoppingListsResponse>>('/shopping'),

  getList: (id: number) => apiClient.get<ApiResponse<ShoppingListDetailResponse>>(`/shopping/${id}`),
  createList: (data: { name: string; store?: string; color?: string }) =>
    apiClient.post('/shopping', data),
  updateList: (id: number, data: Partial<ShoppingList>) => apiClient.put(`/shopping/${id}`, data),
  deleteList: (id: number) => apiClient.delete(`/shopping/${id}`),

  // Items
  addItem: (listId: number, data: Partial<ShoppingItem>) =>
    apiClient.post(`/shopping/${listId}/items`, data),
  updateItem: (listId: number, itemId: number, data: Partial<ShoppingItem>) =>
    apiClient.put(`/shopping/${listId}/items/${itemId}`, data),
  deleteItem: (listId: number, itemId: number) =>
    apiClient.delete(`/shopping/${listId}/items/${itemId}`),
  toggleItem: (listId: number, itemId: number) =>
    apiClient.post(`/shopping/${listId}/items/${itemId}/toggle`),
  clearChecked: (listId: number) =>
    apiClient.post(`/shopping/${listId}/clear-checked`),
};

export default shoppingApi;
