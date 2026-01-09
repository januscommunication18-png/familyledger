import apiClient from './client';
import { ApiResponse, FamilyCircle, FamilyMember } from '../types';

export const familyApi = {
  // Get all family circles
  async getCircles(): Promise<ApiResponse<{ family_circles: FamilyCircle[]; total: number }>> {
    const response = await apiClient.get('/family-circles');
    return response.data;
  },

  // Get a specific family circle with members
  async getCircle(id: number): Promise<ApiResponse<{ family_circle: FamilyCircle }>> {
    const response = await apiClient.get(`/family-circles/${id}`);
    return response.data;
  },

  // Get all members in a family circle
  async getMembers(circleId: number): Promise<ApiResponse<{ members: FamilyMember[]; total: number }>> {
    const response = await apiClient.get(`/family-circles/${circleId}/members`);
    return response.data;
  },

  // Get a specific member
  async getMember(circleId: number, memberId: number): Promise<ApiResponse<{ member: FamilyMember }>> {
    const response = await apiClient.get(`/family-circles/${circleId}/members/${memberId}`);
    return response.data;
  },
};

export default familyApi;
