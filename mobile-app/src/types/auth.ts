// Authentication types

export interface User {
  id: number;
  name: string;
  first_name: string | null;
  last_name: string | null;
  email: string;
  phone: string | null;
  role: UserRole;
  role_name: string;
  avatar: string | null;
  auth_provider: AuthProvider;
  email_verified: boolean;
  mfa_enabled: boolean;
  created_at: string;
}

export interface Tenant {
  id: string;
  name: string;
  slug: string;
  country: string | null;
  timezone: string | null;
  subscription_tier: string | null;
  onboarding_completed: boolean;
  onboarding_step: number | null;
  goals: string[] | null;
  created_at: string;
}

export type UserRole = 'parent' | 'coparent' | 'guardian' | 'advisor' | 'viewer';
export type AuthProvider = 'email' | 'google' | 'apple' | 'facebook';

export interface AuthResponse {
  token: string;
  token_type: string;
  is_new_user: boolean;
  requires_onboarding: boolean;
  user: User;
  tenant: Tenant;
}

export interface OtpRequestResponse {
  expires_in: number;
}
