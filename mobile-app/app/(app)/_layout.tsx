import { Stack } from 'expo-router';
import { useAuthStore } from '../../src/store/authStore';
import { Redirect } from 'expo-router';

export default function AppLayout() {
  const { isAuthenticated } = useAuthStore();

  // Redirect to login if not authenticated
  if (!isAuthenticated) {
    return <Redirect href="/(auth)/login" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" />
      <Stack.Screen name="onboarding" />
      <Stack.Screen name="family-circle" />
      <Stack.Screen name="asset" />
    </Stack>
  );
}
