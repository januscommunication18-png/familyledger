import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { shoppingApi } from '../../../src/api';
import { ShoppingList } from '../../../src/types';

const COLOR_CLASSES: Record<string, [string, string]> = {
  emerald: ['#059669', '#10b981'],
  blue: ['#2563eb', '#3b82f6'],
  purple: ['#7c3aed', '#8b5cf6'],
  pink: ['#db2777', '#ec4899'],
  orange: ['#ea580c', '#f97316'],
  red: ['#dc2626', '#ef4444'],
};

export default function ShoppingScreen() {
  const router = useRouter();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['shopping-lists'],
    queryFn: async () => {
      const response = await shoppingApi.getLists();
      return response.data.data;
    },
  });

  const lists = data?.lists || [];
  const stats = data?.stats;

  const renderListCard = (list: ShoppingList) => {
    const colors = COLOR_CLASSES[list.color] || COLOR_CLASSES.emerald;
    const progress = list.progress_percentage || 0;

    return (
      <TouchableOpacity
        key={list.id}
        style={styles.listCard}
        onPress={() => router.push(`/(app)/shopping/${list.id}`)}
        activeOpacity={0.7}
      >
        <LinearGradient colors={colors} style={styles.listIconContainer}>
          <Text style={styles.listIcon}>{list.icon || '🛒'}</Text>
        </LinearGradient>
        <View style={styles.listInfo}>
          <View style={styles.listHeader}>
            <Text style={styles.listName} numberOfLines={1}>{list.name}</Text>
            {list.is_default && (
              <View style={styles.defaultBadge}>
                <Text style={styles.defaultText}>Default</Text>
              </View>
            )}
          </View>
          {list.store_name && (
            <Text style={styles.listStore}>{list.store_name}</Text>
          )}
          {list.items_count > 0 ? (
            <>
              <View style={styles.progressContainer}>
                <View style={styles.progressBar}>
                  <View style={[styles.progressFill, { width: `${progress}%` }]} />
                </View>
                <Text style={styles.progressText}>{progress}%</Text>
              </View>
              <View style={styles.listStats}>
                <Text style={styles.statText}>
                  <Text style={styles.statHighlight}>{list.purchased_count || 0}</Text> of {list.items_count} items
                </Text>
              </View>
            </>
          ) : (
            <Text style={styles.noItemsText}>No items yet</Text>
          )}
        </View>
        <View style={styles.chevron}>
          <Text style={styles.chevronText}>›</Text>
        </View>
      </TouchableOpacity>
    );
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#10b981" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <LinearGradient
        colors={['#059669', '#10b981']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Shopping Lists</Text>
        <Text style={styles.headerSubtitle}>Track your shopping</Text>
      </LinearGradient>

      {/* Stats Cards */}
      <View style={styles.statsContainer}>
        <View style={styles.statCard}>
          <Text style={styles.statCardEmoji}>📋</Text>
          <Text style={styles.statCardValue}>{stats?.total_lists || 0}</Text>
          <Text style={styles.statCardLabel}>Lists</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statCardEmoji}>🛒</Text>
          <Text style={[styles.statCardValue, styles.statCardValuePending]}>{stats?.pending_items || 0}</Text>
          <Text style={styles.statCardLabel}>To Buy</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statCardEmoji}>✅</Text>
          <Text style={[styles.statCardValue, styles.statCardValueDone]}>{stats?.completed_items || 0}</Text>
          <Text style={styles.statCardLabel}>Done</Text>
        </View>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {lists.length > 0 ? (
          lists.map(renderListCard)
        ) : (
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIconContainer}>
              <Text style={styles.emptyIcon}>🛒</Text>
            </View>
            <Text style={styles.emptyTitle}>No Shopping Lists</Text>
            <Text style={styles.emptyText}>Create your first shopping list to get started</Text>
            <TouchableOpacity style={styles.addButton}>
              <LinearGradient colors={['#059669', '#10b981']} style={styles.addButtonGradient}>
                <Text style={styles.addButtonText}>Create List</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#059669', '#10b981']} style={styles.fabGradient}>
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
  statsContainer: {
    flexDirection: 'row',
    marginHorizontal: 16,
    marginTop: -12,
    gap: 10,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 12,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statCardEmoji: {
    fontSize: 20,
    marginBottom: 4,
  },
  statCardValue: {
    fontSize: 22,
    fontWeight: '700',
    color: '#1e293b',
  },
  statCardValuePending: {
    color: '#f59e0b',
  },
  statCardValueDone: {
    color: '#10b981',
  },
  statCardLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  listCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 18,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  listIconContainer: {
    width: 52,
    height: 52,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listIcon: {
    fontSize: 24,
  },
  listInfo: {
    flex: 1,
    marginLeft: 14,
  },
  listHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  listName: {
    fontSize: 17,
    fontWeight: '700',
    color: '#1e293b',
    flex: 1,
  },
  defaultBadge: {
    backgroundColor: '#dbeafe',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  defaultText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#2563eb',
  },
  listStore: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
  },
  progressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
    gap: 8,
  },
  progressBar: {
    flex: 1,
    height: 6,
    backgroundColor: '#e2e8f0',
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#10b981',
    borderRadius: 3,
  },
  progressText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#10b981',
    width: 36,
  },
  listStats: {
    marginTop: 4,
  },
  statText: {
    fontSize: 12,
    color: '#64748b',
  },
  statHighlight: {
    fontWeight: '700',
    color: '#10b981',
  },
  noItemsText: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 6,
  },
  chevron: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  chevronText: {
    fontSize: 18,
    color: '#94a3b8',
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIconContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#d1fae5',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyIcon: {
    fontSize: 48,
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
    shadowColor: '#10b981',
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
