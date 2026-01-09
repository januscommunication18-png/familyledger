import { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useMutation } from '@tanstack/react-query';
import { onboardingApi } from '../../../src/api';

const ROLES = [
  { id: 'parent', label: 'Parent', description: 'Primary family manager with full access', icon: '👨‍👩‍👧' },
  { id: 'co_parent', label: 'Co-Parent', description: 'Shared management responsibilities', icon: '👫' },
  { id: 'grandparent', label: 'Grandparent', description: 'View and contribute family info', icon: '👴' },
  { id: 'guardian', label: 'Guardian', description: 'Legal guardian with management access', icon: '🛡️' },
  { id: 'caregiver', label: 'Caregiver', description: 'Care provider with limited access', icon: '🤝' },
  { id: 'other', label: 'Other', description: 'Custom role in the family', icon: '👤' },
];

export default function OnboardingStep3Screen() {
  const router = useRouter();
  const [selectedRole, setSelectedRole] = useState<string | null>(null);

  const mutation = useMutation({
    mutationFn: async (role: string) => {
      return onboardingApi.saveStep(3, { role });
    },
    onSuccess: () => {
      router.push('/(app)/onboarding/step4');
    },
  });

  const handleContinue = () => {
    if (selectedRole) {
      mutation.mutate(selectedRole);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Progress */}
      <View style={styles.progressContainer}>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: '60%' }]} />
        </View>
        <Text style={styles.progressText}>Step 3 of 5</Text>
      </View>

      <ScrollView style={styles.content}>
        <Text style={styles.title}>Select your role</Text>
        <Text style={styles.subtitle}>
          Choose your role in the family circle
        </Text>

        <View style={styles.rolesList}>
          {ROLES.map(role => (
            <TouchableOpacity
              key={role.id}
              style={[
                styles.roleCard,
                selectedRole === role.id && styles.roleCardSelected,
              ]}
              onPress={() => setSelectedRole(role.id)}
            >
              <Text style={styles.roleIcon}>{role.icon}</Text>
              <View style={styles.roleInfo}>
                <Text style={[
                  styles.roleLabel,
                  selectedRole === role.id && styles.roleLabelSelected,
                ]}>
                  {role.label}
                </Text>
                <Text style={styles.roleDescription}>{role.description}</Text>
              </View>
              <View style={[
                styles.radioOuter,
                selectedRole === role.id && styles.radioOuterSelected,
              ]}>
                {selectedRole === role.id && <View style={styles.radioInner} />}
              </View>
            </TouchableOpacity>
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
          style={[
            styles.continueButton,
            !selectedRole && styles.continueButtonDisabled,
          ]}
          onPress={handleContinue}
          disabled={!selectedRole || mutation.isPending}
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
  rolesList: {
    gap: 12,
  },
  roleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f9fafb',
    borderRadius: 16,
    padding: 16,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  roleCardSelected: {
    backgroundColor: '#eef2ff',
    borderColor: '#6366f1',
  },
  roleIcon: {
    fontSize: 32,
    marginRight: 16,
  },
  roleInfo: {
    flex: 1,
  },
  roleLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#374151',
  },
  roleLabelSelected: {
    color: '#6366f1',
  },
  roleDescription: {
    fontSize: 13,
    color: '#6b7280',
    marginTop: 2,
  },
  radioOuter: {
    width: 24,
    height: 24,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: '#d1d5db',
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioOuterSelected: {
    borderColor: '#6366f1',
  },
  radioInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#6366f1',
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
  continueButtonDisabled: {
    backgroundColor: '#c7d2fe',
  },
  continueButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ffffff',
  },
});
