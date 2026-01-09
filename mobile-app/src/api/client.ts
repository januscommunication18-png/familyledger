import axios, { AxiosInstance, InternalAxiosRequestConfig, AxiosError } from 'axios';
import { secureStorage } from '../utils/storage';
import config from '../utils/constants';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: config.API_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - add auth token
apiClient.interceptors.request.use(
  async (requestConfig: InternalAxiosRequestConfig) => {
    const token = await secureStorage.getToken();
    if (token) {
      requestConfig.headers.Authorization = `Bearer ${token}`;
    }
    return requestConfig;
  },
  (error) => Promise.reject(error)
);

// Response interceptor - handle errors
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    if (error.response?.status === 401) {
      // Clear auth state on unauthorized
      await secureStorage.clearAll();
      // The auth store will handle navigation
    }
    return Promise.reject(error);
  }
);

export default apiClient;
