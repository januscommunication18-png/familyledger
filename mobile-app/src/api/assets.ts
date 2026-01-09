import apiClient from './client';
import { ApiResponse, Asset, AssetCategory, AssetsByCategory } from '../types';

export const assetsApi = {
  // Get all assets
  async getAssets(): Promise<ApiResponse<{
    assets: Asset[];
    total: number;
    total_value: number;
    by_category: AssetsByCategory;
  }>> {
    const response = await apiClient.get('/assets');
    return response.data;
  },

  // Get assets by category
  async getAssetsByCategory(category: AssetCategory): Promise<ApiResponse<{
    category: AssetCategory;
    assets: Asset[];
    total: number;
    total_value: number;
  }>> {
    const response = await apiClient.get(`/assets/category/${category}`);
    return response.data;
  },

  // Get a specific asset
  async getAsset(id: number): Promise<ApiResponse<{ asset: Asset }>> {
    const response = await apiClient.get(`/assets/${id}`);
    return response.data;
  },
};

export default assetsApi;
