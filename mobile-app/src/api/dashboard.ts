import apiClient from './client';
import { ApiResponse, User, Tenant } from '../types';

export interface DashboardStats {
  family_circles: number;
  family_members: number;
  assets: number;
  total_asset_value: number;
  formatted_asset_value: string;
}

export interface QuickAction {
  id: string;
  title: string;
  icon: string;
  route: string;
}

export interface DashboardData {
  user: User;
  tenant: Tenant;
  stats: DashboardStats;
  quick_actions: QuickAction[];
}

export interface DetailedStats {
  members_by_relationship: Record<string, number>;
  assets_by_category: Record<string, { count: number; total_value: number }>;
  totals: {
    family_circles: number;
    family_members: number;
    assets: number;
    total_asset_value: number;
  };
}

export const dashboardApi = {
  // Get dashboard overview
  async getDashboard(): Promise<ApiResponse<DashboardData>> {
    const response = await apiClient.get('/dashboard');
    return response.data;
  },

  // Get detailed statistics
  async getStats(): Promise<ApiResponse<DetailedStats>> {
    const response = await apiClient.get('/dashboard/stats');
    return response.data;
  },
};

export default dashboardApi;
