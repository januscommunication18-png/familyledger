import { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useMutation } from '@tanstack/react-query';
import { onboardingApi } from '../../../src/api';

const GOALS = [
  { id: 'track_family', label: 'Track Family Members', icon: '👨‍👩‍👧‍👦' },
  { id: 'manage_assets', label: 'Manage Assets', icon: '💎' },
  { id: 'store_documents', label: 'Store Documents', icon: '📄' },
  { id: 'family_tree', label: 'Build Family Tree', icon: '🌳' },
  { id: 'share_info', label: 'Share Information', icon: '🔗' },
  { id: 'plan_finances', label: 'Plan Finances', icon: '💰' },
];

export default function OnboardingStep1Screen() {
  const router = useRouter();
  const [selectedGoals, setSelectedGoals] = useState<string[]>([]);

  const mutation = useMutation({
    mutationFn: async (goals: string[]) => {
      return onboardingApi.saveStep(1, { goals });
    },
    onSuccess: () => {
      router.push('/(app)/onboarding/step2');
    },
  });

  const toggleGoal = (goalId: string) => {
    setSelectedGoals(prev =>
      prev.includes(goalId)
        ? prev.filter(g => g !== goalId)
        : [...prev, goalId]
    );
  };

  const handleContinue = () => {
    if (selectedGoals.length > 0) {
      mutation.mutate(selectedGoals);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Progress */}
      <View style={styles.progressContainer}>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: '20%' }]} />
        </View>
        <Text style={styles.progressText}>Step 1 of 5</Text>
      </View>

      <ScrollView style={styles.content}>
        <Text style={styles.title}>What are your goals?</Text>
        <Text style={styles.subtitle}>
          Select all that apply. This helps us customize your experience.
        </Text>

        <View style={styles.goalsGrid}>
          {GOALS.map(goal => (
            <TouchableOpacity
              key={goal.id}
              style={[
                styles.goalCard,
                selectedGoals.includes(goal.id) && styles.goalCardSelected,
              ]}
              onPress={() => toggleGoal(goal.id)}
            >
              <Text style={styles.goalIcon}>{goal.icon}</Text>
              <Text style={[
                styles.goalLabel,
                selectedGoals.includes(goal.id) && styles.goalLabelSelected,
              ]}>
                {goal.label}
              </Text>
              {selectedGoals.includes(goal.id) && (
                <View style={styles.checkmark}>
                  <Text style={styles.checkmarkText}>✓</Text>
                </View>
              )}
            </TouchableOpacity>
          ))}
        </View>
      </ScrollView>

      <View style={styles.footer}>
        <TouchableOpacity
          style={[
            styles.continueButton,
            selectedGoals.length === 0 && styles.continueButtonDisabled,
          ]}
          onPress={handleContinue}
          disabled={selectedGoals.length === 0 || mutation.isPending}
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
  goalsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  goalCard: {
    width: '47%',
    backgroundColor: '#f9fafb',
    borderRadius: 16,
    padding: 20,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  goalCardSelected: {
    backgroundColor: '#eef2ff',
    borderColor: '#6366f1',
  },
  goalIcon: {
    fontSize: 32,
    marginBottom: 12,
  },
  goalLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#374151',
    textAlign: 'center',
  },
  goalLabelSelected: {
    color: '#6366f1',
  },
  checkmark: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: '#6366f1',
    justifyContent: 'center',
    alignItems: 'center',
  },
  checkmarkText: {
    fontSize: 14,
    color: '#ffffff',
    fontWeight: 'bold',
  },
  footer: {
    padding: 24,
    paddingBottom: 32,
  },
  continueButton: {
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
