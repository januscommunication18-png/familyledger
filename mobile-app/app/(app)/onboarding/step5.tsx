import { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Switch } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { onboardingApi } from '../../../src/api';
import { useAuthStore } from '../../../src/store/authStore';

const SECURITY_OPTIONS = [
  {
    id: 'biometric',
    label: 'Biometric Authentication',
    description: 'Use Face ID or Touch ID to unlock',
    icon: '🔐',
  },
  {
    id: 'pin',
    label: 'PIN Code',
    description: 'Set a 6-digit PIN for extra security',
    icon: '🔢',
  },
  {
    id: 'two_factor',
    label: 'Two-Factor Authentication',
    description: 'Require code from authenticator app',
    icon: '📲',
  },
  {
    id: 'session_timeout',
    label: 'Auto-Lock',
    description: 'Lock app after 5 minutes of inactivity',
    icon: '⏱️',
  },
];

export default function OnboardingStep5Screen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { setOnboardingComplete } = useAuthStore();
  const [enabledOptions, setEnabledOptions] = useState<Record<string, boolean>>({
    biometric: true,
    session_timeout: true,
  });

  const mutation = useMutation({
    mutationFn: async (options: Record<string, boolean>) => {
      await onboardingApi.saveStep(5, { security_options: options });
      return onboardingApi.complete();
    },
    onSuccess: () => {
      setOnboardingComplete(true);
      queryClient.invalidateQueries();
      router.replace('/(app)/(tabs)');
    },
  });

  const toggleOption = (optionId: string) => {
    setEnabledOptions(prev => ({
      ...prev,
      [optionId]: !prev[optionId],
    }));
  };

  const handleComplete = () => {
    mutation.mutate(enabledOptions);
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Progress */}
      <View style={styles.progressContainer}>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: '100%' }]} />
        </View>
        <Text style={styles.progressText}>Step 5 of 5</Text>
      </View>

      <ScrollView style={styles.content}>
        <Text style={styles.title}>Security options</Text>
        <Text style={styles.subtitle}>
          Protect your family data with these security features
        </Text>

        <View style={styles.optionsList}>
          {SECURITY_OPTIONS.map(option => (
            <View key={option.id} style={styles.optionCard}>
              <Text style={styles.optionIcon}>{option.icon}</Text>
              <View style={styles.optionInfo}>
                <Text style={styles.optionLabel}>{option.label}</Text>
                <Text style={styles.optionDescription}>{option.description}</Text>
              </View>
              <Switch
                value={enabledOptions[option.id] || false}
                onValueChange={() => toggleOption(option.id)}
                trackColor={{ false: '#e5e7eb', true: '#a5b4fc' }}
                thumbColor={enabledOptions[option.id] ? '#6366f1' : '#9ca3af'}
              />
            </View>
          ))}
        </View>

        <View style={styles.note}>
          <Text style={styles.noteText}>
            You can always change these settings later in the app settings.
          </Text>
        </View>
      </ScrollView>

      <View style={styles.footer}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Back</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={styles.completeButton}
          onPress={handleComplete}
          disabled={mutation.isPending}
        >
          {mutation.isPending ? (
            <ActivityIndicator color="#ffffff" />
          ) : (
            <Text style={styles.completeButtonText}>Complete Setup</Text>
          )}
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  progressContainer: {
    padding: 24,
    paddingBottom: 0,
  },
  progressBar: {
    height: 4,
    backgroundColor: '#e5e7eb',
    borderRadius: 2,
    marginBottom: 8,
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#6366f1',
    borderRadius: 2,
  },
  progressText: {
    fontSize: 12,
    color: '#6b7280',
  },
  content: {
    flex: 1,
    padding: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1f2937',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#6b7280',
    marginBottom: 32,
  },
  optionsList: {
    gap: 12,
  },
  optionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f9fafb',
    borderRadius: 16,
    padding: 16,
  },
  optionIcon: {
    fontSize: 28,
    marginRight: 16,
  },
  optionInfo: {
    flex: 1,
  },
  optionLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  optionDescription: {
    fontSize: 13,
    color: '#6b7280',
    marginTop: 2,
  },
  note: {
    marginTop: 24,
    padding: 16,
    backgroundColor: '#fef3c7',
    borderRadius: 12,
  },
  noteText: {
    fontSize: 14,
    color: '#92400e',
    textAlign: 'center',
  },
  footer: {
    padding: 24,
    paddingBottom: 32,
    flexDirection: 'row',
    gap: 12,
  },
  backButton: {
    flex: 1,
    backgroundColor: '#f3f4f6',
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  backButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  completeButton: {
    flex: 2,
    backgroundColor: '#059669',
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  completeButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ffffff',
  },
});
