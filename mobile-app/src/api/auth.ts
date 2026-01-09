import apiClient from './client';
import { ApiResponse, AuthResponse, OtpRequestResponse, User, Tenant } from '../types';

export const authApi = {
  // Password login
  async login(email: string, password: string, deviceName: string): Promise<AuthResponse> {
    const response = await apiClient.post('/auth/login', {
      email,
      password,
      device_name: deviceName,
    });
    return response.data.data;
  },

  // Request OTP code
  async requestOtp(email: string): Promise<ApiResponse<OtpRequestResponse>> {
    const response = await apiClient.post('/auth/otp/request', { email });
    return response.data;
  },

  // Verify OTP and get token
  async verifyOtp(email: string, code: string, deviceName: string): Promise<ApiResponse<AuthResponse>> {
    const response = await apiClient.post('/auth/otp/verify', {
      email,
      code,
      device_name: deviceName,
    });
    return response.data;
  },

  // Resend OTP
  async resendOtp(email: string): Promise<ApiResponse<OtpRequestResponse>> {
    const response = await apiClient.post('/auth/otp/resend', { email });
    return response.data;
  },

  // Social login (Google/Apple)
  async socialLogin(
    provider: 'google' | 'apple',
    idToken: string,
    deviceName: string,
    additionalData?: { name?: string; email?: string }
  ): Promise<ApiResponse<AuthResponse>> {
    const response = await apiClient.post(`/auth/social/${provider}`, {
      id_token: idToken,
      device_name: deviceName,
      ...additionalData,
    });
    return response.data;
  },

  // Get current user
  async getUser(): Promise<ApiResponse<{ user: User; tenant: Tenant }>> {
    const response = await apiClient.get('/auth/user');
    return response.data;
  },

  // Logout
  async logout(): Promise<ApiResponse<null>> {
    const response = await apiClient.post('/auth/logout');
    return response.data;
  },

  // Refresh token
  async refreshToken(deviceName: string): Promise<ApiResponse<{ token: string; user: User }>> {
    const response = await apiClient.post('/auth/refresh', {
      device_name: deviceName,
    });
    return response.data;
  },
};

export default authApi;
