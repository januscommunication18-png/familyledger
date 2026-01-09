import { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Switch } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useMutation } from '@tanstack/react-query';
import { onboardingApi } from '../../../src/api';

const FEATURES = [
  {
    id: 'quick_add_members',
    label: 'Quick Add Members',
    description: 'Add family members quickly with basic info',
    icon: '👥',
  },
  {
    id: 'import_contacts',
    label: 'Import Contacts',
    description: 'Import family members from your contacts',
    icon: '📱',
  },
  {
    id: 'asset_tracking',
    label: 'Asset Tracking',
    description: 'Track family assets and valuables',
    icon: '💎',
  },
  {
    id: 'document_storage',
    label: 'Document Storage',
    description: 'Store important family documents',
    icon: '📄',
  },
  {
    id: 'notifications',
    label: 'Notifications',
    description: 'Get reminders for birthdays and events',
    icon: '🔔',
  },
];

export default function OnboardingStep4Screen() {
  const router = useRouter();
  const [enabledFeatures, setEnabledFeatures] = useState<Record<string, boolean>>({
    quick_add_members: true,
    asset_tracking: true,
    notifications: true,
  });

  const mutation = useMutation({
    mutationFn: async (features: Record<string, boolean>) => {
      return onboardingApi.saveStep(4, { features });
    },
    onSuccess: () => {
      router.push('/(app)/onboarding/step5');
    },
  });

  const toggleFeature = (featureId: string) => {
    setEnabledFeatures(prev => ({
      ...prev,
      [featureId]: !prev[featureId],
    }));
  };

  const handleContinue = () => {
    mutation.mutate(enabledFeatures);
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Progress */}
      <View style={styles.progressContainer}>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: '80%' }]} />
        </View>
        <Text style={styles.progressText}>Step 4 of 5</Text>
      </View>

      <ScrollView style={styles.content}>
        <Text style={styles.title}>Quick setup features</Text>
        <Text style={styles.subtitle}>
          Choose which features to enable. You can change these later.
        </Text>

        <View style={styles.featuresList}>
          {FEATURES.map(feature => (
            <View key={feature.id} style={styles.featureCard}>
              <Text style={styles.featureIcon}>{feature.icon}</Text>
              <View style={styles.featureInfo}>
                <Text style={styles.featureLabel}>{feature.label}</Text>
                <Text style={styles.featureDescription}>{feature.description}</Text>
              </View>
              <Switch
                value={enabledFeatures[feature.id] || false}
                onValueChange={() => toggleFeature(feature.id)}
                trackColor={{ false: '#e5e7eb', true: '#a5b4fc' }}
                thumbColor={enabledFeatures[feature.id] ? '#6366f1' : '#9ca3af'}
              />
            </View>
          ))}
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
          style={styles.continueButton}
          onPress={handleContinue}
          disabled={mutation.isPending}
        >
          {mutation.isPending ? (
            <ActivityIndicator color="#ffffff" />
          ) : (
            <Text style={styles.continueButtonText}>Continue</Text>
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
  featuresList: {
    gap: 12,
  },
  featureCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f9fafb',
    borderRadius: 16,
    padding: 16,
  },
  featureIcon: {
    fontSize: 28,
    marginRight: 16,
  },
  featureInfo: {
    flex: 1,
  },
  featureLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  featureDescription: {
    fontSize: 13,
    color: '#6b7280',
    marginTop: 2,
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
  continueButton: {
    flex: 2,
    backgroundColor: '#6366f1',
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  continueButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ffffff',
  },
});
