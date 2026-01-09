import { View, Text, StyleSheet, ScrollView, RefreshControl, TouchableOpacity } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { dashboardApi } from '../../../src/api';
import { useAuthStore } from '../../../src/store/authStore';

export default function DashboardScreen() {
  const router = useRouter();
  const { user } = useAuthStore();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => {
      const response = await dashboardApi.getDashboard();
      return response.data.data;
    },
  });

  const stats = data?.stats;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Welcome back,</Text>
            <Text style={styles.userName}>{user?.name || 'User'}</Text>
          </View>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {user?.name?.charAt(0)?.toUpperCase() || 'U'}
            </Text>
          </View>
        </View>

        {/* Stats Cards */}
        <View style={styles.statsGrid}>
          <TouchableOpacity
            style={styles.statCard}
            onPress={() => router.push('/(app)/(tabs)/family')}
          >
            <Text style={styles.statIcon}>👨‍👩‍👧‍👦</Text>
            <Text style={styles.statValue}>{stats?.family_members ?? '-'}</Text>
            <Text style={styles.statLabel}>Family Members</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.statCard}
            onPress={() => router.push('/(app)/(tabs)/assets')}
          >
            <Text style={styles.statIcon}>💎</Text>
            <Text style={styles.statValue}>{stats?.assets ?? '-'}</Text>
            <Text style={styles.statLabel}>Assets</Text>
          </TouchableOpacity>

          <View style={[styles.statCard, styles.statCardWide]}>
            <Text style={styles.statIcon}>💰</Text>
            <Text style={styles.statValue}>
              {stats?.formatted_asset_value ?? '$0.00'}
            </Text>
            <Text style={styles.statLabel}>Total Asset Value</Text>
          </View>
        </View>

        {/* Quick Actions */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.actionsGrid}>
            <TouchableOpacity style={styles.actionCard}>
              <Text style={styles.actionIcon}>➕</Text>
              <Text style={styles.actionLabel}>Add Member</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard}>
              <Text style={styles.actionIcon}>📦</Text>
              <Text style={styles.actionLabel}>Add Asset</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionCard}
              onPress={() => router.push('/(app)/documents')}
            >
              <Text style={styles.actionIcon}>📄</Text>
              <Text style={styles.actionLabel}>Documents</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.actionCard}
              onPress={() => router.push('/(app)/(tabs)/settings')}
            >
              <Text style={styles.actionIcon}>⚙️</Text>
              <Text style={styles.actionLabel}>Settings</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Features */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Features</Text>
          <View style={styles.featuresGrid}>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/expenses')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#d1fae5' }]}>
                <Text style={styles.featureIconText}>💰</Text>
              </View>
              <Text style={styles.featureLabel}>Expenses</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/goals')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#fef3c7' }]}>
                <Text style={styles.featureIconText}>🎯</Text>
              </View>
              <Text style={styles.featureLabel}>Goals</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/journal')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#ede9fe' }]}>
                <Text style={styles.featureIconText}>📔</Text>
              </View>
              <Text style={styles.featureLabel}>Journal</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/pets')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#fce7f3' }]}>
                <Text style={styles.featureIconText}>🐾</Text>
              </View>
              <Text style={styles.featureLabel}>Pets</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/shopping')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#d1fae5' }]}>
                <Text style={styles.featureIconText}>🛒</Text>
              </View>
              <Text style={styles.featureLabel}>Shopping</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/reminders')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#dbeafe' }]}>
                <Text style={styles.featureIconText}>🔔</Text>
              </View>
              <Text style={styles.featureLabel}>Reminders</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/people')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#ede9fe' }]}>
                <Text style={styles.featureIconText}>👥</Text>
              </View>
              <Text style={styles.featureLabel}>People</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.featureCard}
              onPress={() => router.push('/(app)/resources')}
            >
              <View style={[styles.featureIcon, { backgroundColor: '#cffafe' }]}>
                <Text style={styles.featureIconText}>📂</Text>
              </View>
              <Text style={styles.featureLabel}>Resources</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#6366f1',
    padding: 24,
    paddingBottom: 32,
  },
  greeting: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.8)',
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
    marginTop: 4,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 16,
    marginTop: -20,
    gap: 12,
  },
  statCard: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 20,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statCardWide: {
    minWidth: '100%',
  },
  statIcon: {
    fontSize: 28,
    marginBottom: 8,
  },
  statValue: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1f2937',
  },
  statLabel: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 4,
  },
  section: {
    padding: 16,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
    marginBottom: 16,
  },
  actionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  actionCard: {
    width: '48%',
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  actionIcon: {
    fontSize: 24,
    marginBottom: 8,
  },
  actionLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#374151',
  },
  featuresGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  featureCard: {
    width: '31%',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  featureIcon: {
    width: 48,
    height: 48,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  featureIconText: {
    fontSize: 22,
  },
  featureLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#374151',
    textAlign: 'center',
  },
});
