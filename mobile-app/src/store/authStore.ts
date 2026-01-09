import { create } from 'zustand';
import { User, Tenant } from '../types';
import { secureStorage } from '../utils/storage';
import { authApi } from '../api';

interface AuthState {
  user: User | null;
  tenant: Tenant | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  isInitialized: boolean;
  isOnboardingComplete: boolean;

  // Actions
  initialize: () => Promise<void>;
  setAuth: (token: string, user: User, tenant: Tenant, onboardingComplete?: boolean) => Promise<void>;
  logout: () => Promise<void>;
  updateUser: (user: Partial<User>) => void;
  updateTenant: (tenant: Partial<Tenant>) => void;
  setOnboardingComplete: (complete: boolean) => void;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  tenant: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,
  isInitialized: false,
  isOnboardingComplete: false,

  // Initialize auth state from storage
  initialize: async () => {
    try {
      const token = await secureStorage.getToken();
      const user = await secureStorage.getUser<User>();
      const tenant = await secureStorage.getTenant<Tenant>();

      if (token && user && tenant) {
        // Verify token is still valid
        try {
          const response = await authApi.getUser();
          set({
            token,
            user: response.data.user,
            tenant: response.data.tenant,
            isAuthenticated: true,
            isLoading: false,
            isInitialized: true,
          });
        } catch (error) {
          // Token is invalid, clear storage
          await secureStorage.clearAll();
          set({
            token: null,
            user: null,
            tenant: null,
            isAuthenticated: false,
            isLoading: false,
            isInitialized: true,
          });
        }
      } else {
        set({
          isLoading: false,
          isInitialized: true,
        });
      }
    } catch (error) {
      console.error('Error initializing auth:', error);
      set({
        isLoading: false,
        isInitialized: true,
      });
    }
  },

  // Set auth data after login
  setAuth: async (token: string, user: User, tenant: Tenant, onboardingComplete = false) => {
    await secureStorage.setToken(token);
    await secureStorage.setUser(user);
    await secureStorage.setTenant(tenant);

    set({
      token,
      user,
      tenant,
      isAuthenticated: true,
      isLoading: false,
      isOnboardingComplete: onboardingComplete,
    });
  },

  // Logout and clear all data
  logout: async () => {
    try {
      await authApi.logout();
    } catch (error) {
      // Ignore logout API errors
    }

    await secureStorage.clearAll();

    set({
      token: null,
      user: null,
      tenant: null,
      isAuthenticated: false,
    });
  },

  // Update user data
  updateUser: (userData: Partial<User>) => {
    const currentUser = get().user;
    if (currentUser) {
      const updatedUser = { ...currentUser, ...userData };
      set({ user: updatedUser });
      secureStorage.setUser(updatedUser);
    }
  },

  // Update tenant data
  updateTenant: (tenantData: Partial<Tenant>) => {
    const currentTenant = get().tenant;
    if (currentTenant) {
      const updatedTenant = { ...currentTenant, ...tenantData };
      set({ tenant: updatedTenant });
      secureStorage.setTenant(updatedTenant);
    }
  },

  // Set onboarding complete status
  setOnboardingComplete: (complete: boolean) => {
    set({ isOnboardingComplete: complete });
  },
}));

export default useAuthStore;
