import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { shoppingApi } from '../../../src/api';
import { ShoppingItem } from '../../../src/types';

export default function ShoppingListDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();
  const queryClient = useQueryClient();
  const [newItemName, setNewItemName] = useState('');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['shopping-list', id],
    queryFn: async () => {
      const response = await shoppingApi.getList(Number(id));
      return response.data.data;
    },
  });

  const list = data?.list;
  const items = data?.items || [];
  const stats = data?.stats;

  const toggleMutation = useMutation({
    mutationFn: (itemId: number) => shoppingApi.toggleItem(Number(id), itemId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['shopping-list', id] });
    },
  });

  const addItemMutation = useMutation({
    mutationFn: (name: string) => shoppingApi.addItem(Number(id), { name }),
    onSuccess: () => {
      setNewItemName('');
      queryClient.invalidateQueries({ queryKey: ['shopping-list', id] });
    },
  });

  const handleToggle = (itemId: number) => {
    toggleMutation.mutate(itemId);
  };

  const handleAddItem = () => {
    if (newItemName.trim()) {
      addItemMutation.mutate(newItemName.trim());
    }
  };

  const uncheckedItems = items.filter((i: ShoppingItem) => !i.is_checked);
  const checkedItems = items.filter((i: ShoppingItem) => i.is_checked);

  const renderItem = (item: ShoppingItem) => (
    <TouchableOpacity
      key={item.id}
      style={styles.itemCard}
      onPress={() => handleToggle(item.id)}
    >
      <View style={[styles.checkbox, item.is_checked && styles.checkboxChecked]}>
        {item.is_checked && <Text style={styles.checkmark}>✓</Text>}
      </View>
      <View style={styles.itemContent}>
        <Text style={[styles.itemName, item.is_checked && styles.itemNameChecked]}>
          {item.name}
        </Text>
        <View style={styles.itemMeta}>
          {item.quantity && (
            <Text style={styles.itemQuantity}>
              {item.quantity} {item.unit || ''}
            </Text>
          )}
          {item.category && (
            <View style={styles.categoryBadge}>
              <Text style={styles.categoryText}>{item.category}</Text>
            </View>
          )}
        </View>
        {item.notes && (
          <Text style={styles.itemNotes} numberOfLines={1}>{item.notes}</Text>
        )}
      </View>
      {(item.formatted_price || item.price) && (
        <Text style={[styles.itemPrice, item.is_checked && styles.itemPriceChecked]}>
          {item.formatted_price || `$${item.price?.toFixed(2)}`}
        </Text>
      )}
    </TouchableOpacity>
  );

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
        <Text style={styles.headerTitle}>{list?.name || 'Shopping List'}</Text>
        {list?.store_name && (
          <Text style={styles.headerSubtitle}>{list.store_name}</Text>
        )}
      </LinearGradient>

      {/* Add Item Input */}
      <View style={styles.addItemContainer}>
        <TextInput
          style={styles.addItemInput}
          placeholder="Add an item..."
          value={newItemName}
          onChangeText={setNewItemName}
          onSubmitEditing={handleAddItem}
          returnKeyType="done"
        />
        <TouchableOpacity
          style={[styles.addItemButton, !newItemName.trim() && styles.addItemButtonDisabled]}
          onPress={handleAddItem}
          disabled={!newItemName.trim()}
        >
          <Text style={styles.addItemButtonText}>+</Text>
        </TouchableOpacity>
      </View>

      {/* Progress Stats */}
      {stats && items.length > 0 && (
        <View style={styles.progressSection}>
          <View style={styles.progressStats}>
            <View style={styles.progressStatItem}>
              <Text style={styles.progressStatValue}>{stats.purchased_items}</Text>
              <Text style={styles.progressStatLabel}>Done</Text>
            </View>
            <View style={styles.progressBarContainer}>
              <View style={styles.progressBarBg}>
                <View style={[styles.progressBarFill, { width: `${stats.progress_percentage}%` }]} />
              </View>
              <Text style={styles.progressPercentage}>{stats.progress_percentage}%</Text>
            </View>
            <View style={styles.progressStatItem}>
              <Text style={[styles.progressStatValue, styles.progressStatValuePending]}>{stats.pending_items}</Text>
              <Text style={styles.progressStatLabel}>To Buy</Text>
            </View>
          </View>
        </View>
      )}

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Unchecked Items */}
        {uncheckedItems.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>To Buy ({uncheckedItems.length})</Text>
            {uncheckedItems.map(renderItem)}
          </View>
        )}

        {/* Checked Items */}
        {checkedItems.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Done ({checkedItems.length})</Text>
            {checkedItems.map(renderItem)}
          </View>
        )}

        {/* Empty State */}
        {items.length === 0 && (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>📝</Text>
            <Text style={styles.emptyTitle}>List is Empty</Text>
            <Text style={styles.emptyText}>Add items to your shopping list</Text>
          </View>
        )}
      </ScrollView>
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
    fontSize: 26,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 4,
  },
  addItemContainer: {
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
  addItemInput: {
    flex: 1,
    paddingHorizontal: 16,
    paddingVertical: 12,
    fontSize: 16,
    color: '#1e293b',
  },
  addItemButton: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: '#10b981',
    justifyContent: 'center',
    alignItems: 'center',
  },
  addItemButtonDisabled: {
    backgroundColor: '#d1d5db',
  },
  addItemButtonText: {
    fontSize: 24,
    color: '#ffffff',
    fontWeight: '500',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 40,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
    marginBottom: 12,
  },
  itemCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
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
  itemContent: {
    flex: 1,
    marginLeft: 12,
  },
  itemName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
  },
  itemNameChecked: {
    textDecorationLine: 'line-through',
    color: '#94a3b8',
  },
  itemMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 4,
    gap: 8,
  },
  itemQuantity: {
    fontSize: 13,
    color: '#64748b',
  },
  categoryBadge: {
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
  },
  categoryText: {
    fontSize: 11,
    fontWeight: '500',
    color: '#64748b',
  },
  itemNotes: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 4,
    fontStyle: 'italic',
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#10b981',
  },
  itemPriceChecked: {
    color: '#94a3b8',
    textDecorationLine: 'line-through',
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 4,
  },
  emptyText: {
    fontSize: 14,
    color: '#64748b',
  },
  progressSection: {
    marginHorizontal: 16,
    marginTop: 16,
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  progressStats: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  progressStatItem: {
    alignItems: 'center',
    minWidth: 50,
  },
  progressStatValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#10b981',
  },
  progressStatValuePending: {
    color: '#f59e0b',
  },
  progressStatLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
  },
  progressBarContainer: {
    flex: 1,
    marginHorizontal: 16,
    alignItems: 'center',
  },
  progressBarBg: {
    width: '100%',
    height: 8,
    backgroundColor: '#e2e8f0',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    backgroundColor: '#10b981',
    borderRadius: 4,
  },
  progressPercentage: {
    fontSize: 12,
    fontWeight: '600',
    color: '#10b981',
    marginTop: 4,
  },
});
