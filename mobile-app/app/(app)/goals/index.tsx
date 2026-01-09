import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { goalsApi } from '../../../src/api';
import { Goal, Task } from '../../../src/types';

type TabType = 'goals' | 'todos';

const PRIORITY_COLORS = {
  low: { bg: '#dbeafe', text: '#1d4ed8' },
  medium: { bg: '#fef3c7', text: '#b45309' },
  high: { bg: '#fee2e2', text: '#dc2626' },
};

export default function GoalsScreen() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<TabType>('goals');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['goals-todo'],
    queryFn: async () => {
      const response = await goalsApi.getGoalsAndTasks();
      return response.data.data;
    },
  });

  const goals = data?.goals || [];
  const tasks = data?.tasks || [];
  const activeGoals = goals.filter(g => g.status === 'active');
  const openTasks = tasks.filter(t => t.status === 'pending');

  const renderGoalCard = (goal: Goal) => (
    <TouchableOpacity key={goal.id} style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={styles.goalIcon}>
          <Text style={styles.goalIconText}>🎯</Text>
        </View>
        <View style={styles.cardTitleContainer}>
          <Text style={styles.cardTitle} numberOfLines={1}>{goal.title}</Text>
          {goal.target_date && (
            <Text style={styles.cardDate}>Target: {goal.target_date}</Text>
          )}
        </View>
        <View style={[styles.priorityBadge, { backgroundColor: PRIORITY_COLORS[goal.priority].bg }]}>
          <Text style={[styles.priorityText, { color: PRIORITY_COLORS[goal.priority].text }]}>
            {goal.priority}
          </Text>
        </View>
      </View>
      {goal.description && (
        <Text style={styles.cardDescription} numberOfLines={2}>{goal.description}</Text>
      )}
      <View style={styles.progressContainer}>
        <View style={styles.progressBar}>
          <View style={[styles.progressFill, { width: `${goal.progress}%` }]} />
        </View>
        <Text style={styles.progressText}>{goal.progress}%</Text>
      </View>
    </TouchableOpacity>
  );

  const renderTaskCard = (task: Task) => (
    <TouchableOpacity key={task.id} style={styles.taskCard}>
      <TouchableOpacity style={[styles.checkbox, task.status === 'completed' && styles.checkboxChecked]}>
        {task.status === 'completed' && <Text style={styles.checkmark}>✓</Text>}
      </TouchableOpacity>
      <View style={styles.taskContent}>
        <Text style={[styles.taskTitle, task.status === 'completed' && styles.taskTitleCompleted]}>
          {task.title}
        </Text>
        <View style={styles.taskMeta}>
          {task.due_date && (
            <Text style={styles.taskDate}>📅 {task.due_date}</Text>
          )}
          {task.is_recurring && (
            <View style={styles.recurringBadge}>
              <Text style={styles.recurringText}>🔄 Recurring</Text>
            </View>
          )}
        </View>
      </View>
      <View style={[styles.priorityDot, { backgroundColor: PRIORITY_COLORS[task.priority].text }]} />
    </TouchableOpacity>
  );

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#6366f1" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={['#f59e0b', '#f97316']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Goals & To-Do</Text>
        <Text style={styles.headerSubtitle}>Track goals and manage family tasks</Text>
      </LinearGradient>

      {/* Tabs */}
      <View style={styles.tabsContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'goals' && styles.tabActive]}
          onPress={() => setActiveTab('goals')}
        >
          <Text style={styles.tabIcon}>🎯</Text>
          <Text style={[styles.tabText, activeTab === 'goals' && styles.tabTextActive]}>Goals</Text>
          {activeGoals.length > 0 && (
            <View style={[styles.tabBadge, activeTab === 'goals' && styles.tabBadgeActive]}>
              <Text style={styles.tabBadgeText}>{activeGoals.length}</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'todos' && styles.tabActive]}
          onPress={() => setActiveTab('todos')}
        >
          <Text style={styles.tabIcon}>✅</Text>
          <Text style={[styles.tabText, activeTab === 'todos' && styles.tabTextActive]}>To-Do</Text>
          {openTasks.length > 0 && (
            <View style={[styles.tabBadge, activeTab === 'todos' && styles.tabBadgeActive]}>
              <Text style={styles.tabBadgeText}>{openTasks.length}</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {activeTab === 'goals' ? (
          goals.length > 0 ? (
            goals.map(renderGoalCard)
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🎯</Text>
              <Text style={styles.emptyTitle}>No Goals Yet</Text>
              <Text style={styles.emptyText}>Set goals to track your family's progress</Text>
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#f59e0b', '#f97316']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Create Goal</Text>
                </LinearGradient>
              </TouchableOpacity>
            </View>
          )
        ) : (
          tasks.length > 0 ? (
            tasks.map(renderTaskCard)
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>✅</Text>
              <Text style={styles.emptyTitle}>No Tasks</Text>
              <Text style={styles.emptyText}>Add tasks to stay organized</Text>
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#f59e0b', '#f97316']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Add Task</Text>
                </LinearGradient>
              </TouchableOpacity>
            </View>
          )
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#f59e0b', '#f97316']} style={styles.fabGradient}>
          <Text style={styles.fabText}>+</Text>
        </LinearGradient>
      </TouchableOpacity>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 24,
  },
  backButton: {
    marginBottom: 8,
  },
  backButtonText: {
    fontSize: 32,
    color: '#ffffff',
    fontWeight: '300',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 4,
  },
  tabsContainer: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: -12,
    borderRadius: 16,
    padding: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  tab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 12,
    gap: 6,
  },
  tabActive: {
    backgroundColor: '#f59e0b',
  },
  tabIcon: {
    fontSize: 16,
  },
  tabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
  },
  tabTextActive: {
    color: '#ffffff',
  },
  tabBadge: {
    backgroundColor: '#e2e8f0',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
  },
  tabBadgeActive: {
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  tabBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#1e293b',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  goalIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: '#fef3c7',
    justifyContent: 'center',
    alignItems: 'center',
  },
  goalIconText: {
    fontSize: 20,
  },
  cardTitleContainer: {
    flex: 1,
    marginLeft: 12,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
  },
  cardDate: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
  priorityBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  priorityText: {
    fontSize: 11,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  cardDescription: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 12,
    lineHeight: 18,
  },
  progressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 16,
    gap: 12,
  },
  progressBar: {
    flex: 1,
    height: 8,
    backgroundColor: '#f1f5f9',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#f59e0b',
    borderRadius: 4,
  },
  progressText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#f59e0b',
    minWidth: 40,
  },
  taskCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 1,
  },
  checkbox: {
    width: 26,
    height: 26,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#d1d5db',
    justifyContent: 'center',
    alignItems: 'center',
  },
  checkboxChecked: {
    backgroundColor: '#10b981',
    borderColor: '#10b981',
  },
  checkmark: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  taskContent: {
    flex: 1,
    marginLeft: 12,
  },
  taskTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1e293b',
  },
  taskTitleCompleted: {
    textDecorationLine: 'line-through',
    color: '#94a3b8',
  },
  taskMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 4,
    gap: 8,
  },
  taskDate: {
    fontSize: 12,
    color: '#94a3b8',
  },
  recurringBadge: {
    backgroundColor: '#e0e7ff',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  recurringText: {
    fontSize: 10,
    color: '#6366f1',
  },
  priorityDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: '#64748b',
    textAlign: 'center',
  },
  addButton: {
    marginTop: 24,
    borderRadius: 12,
    overflow: 'hidden',
  },
  addButtonGradient: {
    paddingHorizontal: 24,
    paddingVertical: 12,
  },
  addButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  fab: {
    position: 'absolute',
    right: 20,
    bottom: 24,
    borderRadius: 28,
    overflow: 'hidden',
    shadowColor: '#f59e0b',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  fabGradient: {
    width: 56,
    height: 56,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fabText: {
    fontSize: 28,
    color: '#ffffff',
  },
});
