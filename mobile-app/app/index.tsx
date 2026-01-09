import { Redirect } from 'expo-router';
import { useAuthStore } from '../src/store/authStore';

export default function Index() {
  const { isAuthenticated, tenant } = useAuthStore();

  if (!isAuthenticated) {
    return <Redirect href="/(auth)/login" />;
  }

  // Check if onboarding is required
  if (tenant && !tenant.onboarding_completed) {
    return <Redirect href="/(app)/onboarding/step1" />;
  }

  return <Redirect href="/(app)/(tabs)" />;
}
