import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { remindersApi } from '../../../src/api';
import { Reminder } from '../../../src/types';

type FilterType = 'all' | 'upcoming' | 'overdue';

const PRIORITY_COLORS = {
  low: { bg: '#dbeafe', text: '#1d4ed8', dot: '#3b82f6' },
  medium: { bg: '#fef3c7', text: '#b45309', dot: '#f59e0b' },
  high: { bg: '#fee2e2', text: '#dc2626', dot: '#ef4444' },
};

export default function RemindersScreen() {
  const router = useRouter();
  const [filter, setFilter] = useState<FilterType>('all');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['reminders'],
    queryFn: async () => {
      const response = await remindersApi.getReminders();
      return response.data.data;
    },
  });

  const reminders = data?.reminders || [];
  const upcoming = data?.upcoming || [];
  const overdue = data?.overdue || [];

  const getFilteredReminders = () => {
    switch (filter) {
      case 'upcoming':
        return upcoming;
      case 'overdue':
        return overdue;
      default:
        return reminders;
    }
  };

  const renderReminderCard = (reminder: Reminder) => {
    const isOverdue = overdue.some(r => r.id === reminder.id);
    const priorityColors = PRIORITY_COLORS[reminder.priority];

    return (
      <TouchableOpacity key={reminder.id} style={[styles.reminderCard, isOverdue && styles.reminderCardOverdue]}>
        <View style={styles.reminderLeft}>
          <View style={[styles.priorityIndicator, { backgroundColor: priorityColors.dot }]} />
          <View style={styles.reminderContent}>
            <Text style={styles.reminderTitle} numberOfLines={1}>{reminder.title}</Text>
            <View style={styles.reminderMeta}>
              <Text style={styles.reminderDate}>
                📅 {reminder.due_date}
                {reminder.due_time && ` at ${reminder.due_time}`}
              </Text>
            </View>
            {reminder.description && (
              <Text style={styles.reminderDescription} numberOfLines={1}>
                {reminder.description}
              </Text>
            )}
            <View style={styles.badgesContainer}>
              <View style={[styles.priorityBadge, { backgroundColor: priorityColors.bg }]}>
                <Text style={[styles.priorityText, { color: priorityColors.text }]}>
                  {reminder.priority}
                </Text>
              </View>
              {reminder.is_recurring && (
                <View style={styles.recurringBadge}>
                  <Text style={styles.recurringText}>🔄 Recurring</Text>
                </View>
              )}
              {isOverdue && (
                <View style={styles.overdueBadge}>
                  <Text style={styles.overdueText}>⚠️ Overdue</Text>
                </View>
              )}
            </View>
          </View>
        </View>
        <TouchableOpacity style={styles.completeButton}>
          <Text style={styles.completeButtonText}>✓</Text>
        </TouchableOpacity>
      </TouchableOpacity>
    );
  };

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
        colors={['#3b82f6', '#60a5fa']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Reminders</Text>
        <Text style={styles.headerSubtitle}>Never miss important dates</Text>
      </LinearGradient>

      {/* Stats */}
      <View style={styles.statsRow}>
        <View style={styles.statCard}>
          <Text style={styles.statValue}>{data?.total || 0}</Text>
          <Text style={styles.statLabel}>Total</Text>
        </View>
        <View style={[styles.statCard, styles.statCardUpcoming]}>
          <Text style={[styles.statValue, styles.statValueUpcoming]}>{upcoming.length}</Text>
          <Text style={styles.statLabel}>Upcoming</Text>
        </View>
        <View style={[styles.statCard, styles.statCardOverdue]}>
          <Text style={[styles.statValue, styles.statValueOverdue]}>{overdue.length}</Text>
          <Text style={styles.statLabel}>Overdue</Text>
        </View>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterContainer}>
        {(['all', 'upcoming', 'overdue'] as FilterType[]).map((f) => (
          <TouchableOpacity
            key={f}
            style={[styles.filterTab, filter === f && styles.filterTabActive]}
            onPress={() => setFilter(f)}
          >
            <Text style={[styles.filterText, filter === f && styles.filterTextActive]}>
              {f.charAt(0).toUpperCase() + f.slice(1)}
              {f === 'overdue' && overdue.length > 0 && ` (${overdue.length})`}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {getFilteredReminders().length > 0 ? (
          getFilteredReminders().map(renderReminderCard)
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>🔔</Text>
            <Text style={styles.emptyTitle}>No Reminders</Text>
            <Text style={styles.emptyText}>
              {filter === 'overdue'
                ? 'Great! No overdue reminders'
                : 'Add reminders to stay on track'}
            </Text>
            {filter === 'all' && (
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#3b82f6', '#60a5fa']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Add Reminder</Text>
                </LinearGradient>
              </TouchableOpacity>
            )}
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#3b82f6', '#60a5fa']} style={styles.fabGradient}>
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
  statsRow: {
    flexDirection: 'row',
    marginHorizontal: 16,
    marginTop: -12,
    gap: 10,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statCardUpcoming: {
    borderBottomWidth: 3,
    borderBottomColor: '#3b82f6',
  },
  statCardOverdue: {
    borderBottomWidth: 3,
    borderBottomColor: '#ef4444',
  },
  statValue: {
    fontSize: 24,
    fontWeight: '700',
    color: '#1e293b',
  },
  statValueUpcoming: {
    color: '#3b82f6',
  },
  statValueOverdue: {
    color: '#ef4444',
  },
  statLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
  },
  filterContainer: {
    flexDirection: 'row',
    backgroundColor: '#ffffff',
    marginHorizontal: 16,
    marginTop: 16,
    borderRadius: 12,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  filterTab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderRadius: 10,
  },
  filterTabActive: {
    backgroundColor: '#3b82f6',
  },
  filterText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
  },
  filterTextActive: {
    color: '#ffffff',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  reminderCard: {
    flexDirection: 'row',
    alignItems: 'center',
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
  reminderCardOverdue: {
    borderLeftWidth: 4,
    borderLeftColor: '#ef4444',
  },
  reminderLeft: {
    flex: 1,
    flexDirection: 'row',
  },
  priorityIndicator: {
    width: 4,
    height: '100%',
    borderRadius: 2,
    marginRight: 12,
  },
  reminderContent: {
    flex: 1,
  },
  reminderTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 4,
  },
  reminderMeta: {
    marginBottom: 4,
  },
  reminderDate: {
    fontSize: 13,
    color: '#64748b',
  },
  reminderDescription: {
    fontSize: 13,
    color: '#94a3b8',
    marginBottom: 8,
  },
  badgesContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
  },
  priorityBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  priorityText: {
    fontSize: 10,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  recurringBadge: {
    backgroundColor: '#e0e7ff',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  recurringText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#6366f1',
  },
  overdueBadge: {
    backgroundColor: '#fee2e2',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  overdueText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#dc2626',
  },
  completeButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#f0fdf4',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#86efac',
  },
  completeButtonText: {
    fontSize: 18,
    color: '#22c55e',
    fontWeight: 'bold',
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
    shadowColor: '#3b82f6',
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
