import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'user_data';
const TENANT_KEY = 'tenant_data';

// Check if we're on web
const isWeb = Platform.OS === 'web';

// Web storage fallback using localStorage
const webStorage = {
  async setItemAsync(key: string, value: string): Promise<void> {
    localStorage.setItem(key, value);
  },
  async getItemAsync(key: string): Promise<string | null> {
    return localStorage.getItem(key);
  },
  async deleteItemAsync(key: string): Promise<void> {
    localStorage.removeItem(key);
  },
};

// Use localStorage on web, SecureStore on native
const storage = isWeb ? webStorage : SecureStore;

export const secureStorage = {
  // Token management
  async setToken(token: string): Promise<void> {
    await storage.setItemAsync(TOKEN_KEY, token);
  },

  async getToken(): Promise<string | null> {
    return await storage.getItemAsync(TOKEN_KEY);
  },

  async removeToken(): Promise<void> {
    await storage.deleteItemAsync(TOKEN_KEY);
  },

  // User data
  async setUser(user: object): Promise<void> {
    await storage.setItemAsync(USER_KEY, JSON.stringify(user));
  },

  async getUser<T>(): Promise<T | null> {
    const data = await storage.getItemAsync(USER_KEY);
    return data ? JSON.parse(data) : null;
  },

  async removeUser(): Promise<void> {
    await storage.deleteItemAsync(USER_KEY);
  },

  // Tenant data
  async setTenant(tenant: object): Promise<void> {
    await storage.setItemAsync(TENANT_KEY, JSON.stringify(tenant));
  },

  async getTenant<T>(): Promise<T | null> {
    const data = await storage.getItemAsync(TENANT_KEY);
    return data ? JSON.parse(data) : null;
  },

  async removeTenant(): Promise<void> {
    await storage.deleteItemAsync(TENANT_KEY);
  },

  // Clear all auth data
  async clearAll(): Promise<void> {
    await Promise.all([
      storage.deleteItemAsync(TOKEN_KEY),
      storage.deleteItemAsync(USER_KEY),
      storage.deleteItemAsync(TENANT_KEY),
    ]);
  },
};

export default secureStorage;