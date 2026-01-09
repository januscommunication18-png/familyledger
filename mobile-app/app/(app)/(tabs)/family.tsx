import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { familyApi } from '../../../src/api';
import { FamilyCircle } from '../../../src/types';

export default function FamilyScreen() {
  const router = useRouter();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['familyCircles'],
    queryFn: async () => {
      const response = await familyApi.getCircles();
      return response.data.data;
    },
  });

  const renderCircle = ({ item }: { item: FamilyCircle }) => (
    <TouchableOpacity
      style={styles.circleCard}
      onPress={() => router.push(`/(app)/family-circle/${item.id}`)}
    >
      <View style={styles.circleIcon}>
        <Text style={styles.circleIconText}>👨‍👩‍👧‍👦</Text>
      </View>
      <View style={styles.circleInfo}>
        <Text style={styles.circleName}>{item.name}</Text>
        <Text style={styles.circleMemberCount}>
          {item.members_count} {item.members_count === 1 ? 'member' : 'members'}
        </Text>
      </View>
      <Text style={styles.chevron}>›</Text>
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
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>Family Circle</Text>
        <Text style={styles.subtitle}>Manage your family members</Text>
      </View>

      {/* List */}
      <FlatList
        data={data?.family_circles || []}
        renderItem={renderCircle}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>👨‍👩‍👧‍👦</Text>
            <Text style={styles.emptyTitle}>No Family Circles</Text>
            <Text style={styles.emptyText}>
              Create your first family circle to start managing your family members
            </Text>
          </View>
        }
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f9fafb',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#ffffff',
    padding: 24,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1f2937',
  },
  subtitle: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 4,
  },
  listContent: {
    padding: 16,
    gap: 12,
  },
  circleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  circleIcon: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#eef2ff',
    justifyContent: 'center',
    alignItems: 'center',
  },
  circleIconText: {
    fontSize: 28,
  },
  circleInfo: {
    flex: 1,
    marginLeft: 16,
  },
  circleName: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
  },
  circleMemberCount: {
    fontSize: 14,
    color: '#6b7280',
    marginTop: 2,
  },
  chevron: {
    fontSize: 24,
    color: '#9ca3af',
  },
  emptyContainer: {
    alignItems: 'center',
    padding: 40,
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#1f2937',
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: '#6b7280',
    textAlign: 'center',
    lineHeight: 20,
  },
});
