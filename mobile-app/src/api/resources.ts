import apiClient from './client';
import { ResourcesResponse, FamilyResource, ResourceDetailResponse } from '../types/resources';

// API returns wrapped response { success: true, data: T }
interface ApiResponse<T> {
  success: boolean;
  data: T;
}

const resourcesApi = {
  getResources: (params?: { type?: string }) =>
    apiClient.get<ApiResponse<ResourcesResponse>>('/resources', { params }),

  getResource: (id: number) => apiClient.get<ApiResponse<ResourceDetailResponse>>(`/resources/${id}`),

  getResourcesByType: (type: string) =>
    apiClient.get<ApiResponse<{ resources: FamilyResource[]; total: number }>>(`/resources/type/${type}`),

  createResource: (data: FormData) => apiClient.post('/resources', data, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  updateResource: (id: number, data: Partial<FamilyResource>) =>
    apiClient.put(`/resources/${id}`, data),
  deleteResource: (id: number) => apiClient.delete(`/resources/${id}`),
};

export default resourcesApi;
