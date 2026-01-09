import { useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, ActivityIndicator, ScrollView, Dimensions } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { assetsApi } from '../../../src/api';
import { Asset, AssetCategory } from '../../../src/types';

const { width } = Dimensions.get('window');

const CATEGORIES: { key: AssetCategory; label: string; icon: string; colors: [string, string] }[] = [
  { key: 'property', label: 'Property', icon: '🏠', colors: ['#3b82f6', '#1d4ed8'] },
  { key: 'vehicle', label: 'Vehicles', icon: '🚗', colors: ['#8b5cf6', '#6d28d9'] },
  { key: 'valuable', label: 'Valuables', icon: '💎', colors: ['#f59e0b', '#d97706'] },
  { key: 'inventory', label: 'Inventory', icon: '📦', colors: ['#10b981', '#059669'] },
];

export default function AssetsScreen() {
  const router = useRouter();
  const [selectedCategory, setSelectedCategory] = useState<AssetCategory | 'all'>('all');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['assets'],
    queryFn: async () => {
      const response = await assetsApi.getAssets();
      return response.data.data;
    },
  });

  const filteredAssets = selectedCategory === 'all'
    ? data?.assets || []
    : (data?.assets || []).filter(a => a.asset_category === selectedCategory);

  const totalValue = filteredAssets.reduce((sum, a) => sum + (parseFloat(String(a.current_value)) || 0), 0);

  const renderAsset = ({ item, index }: { item: Asset; index: number }) => {
    const category = CATEGORIES.find(c => c.key === item.asset_category);
    const categoryIcon = category?.icon || '📦';
    const categoryColors = category?.colors || ['#6b7280', '#4b5563'];
    const purchaseValue = parseFloat(String(item.purchase_value)) || 0;
    const currentValue = parseFloat(String(item.current_value)) || 0;
    const valueChange = currentValue - purchaseValue;
    const valueChangePercent = purchaseValue > 0 ? ((valueChange / purchaseValue) * 100) : 0;
    const isPositive = valueChange >= 0;

    return (
      <TouchableOpacity
        style={styles.assetCard}
        onPress={() => router.push(`/(app)/asset/${item.id}`)}
        activeOpacity={0.7}
      >
        <LinearGradient
          colors={categoryColors}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.assetIconGradient}
        >
          <Text style={styles.assetIconText}>{categoryIcon}</Text>
        </LinearGradient>
        <View style={styles.assetInfo}>
          <Text style={styles.assetName} numberOfLines={1}>{item.name}</Text>
          <Text style={styles.assetType}>{item.asset_type.replace(/_/g, ' ')}</Text>
          {item.status && (
            <View style={[styles.statusBadge, item.status === 'active' ? styles.statusActive : styles.statusInactive]}>
              <Text style={styles.statusText}>{item.status}</Text>
            </View>
          )}
        </View>
        <View style={styles.assetValue}>
          <Text style={styles.assetValueText}>
            {item.formatted_current_value || '-'}
          </Text>
          {purchaseValue > 0 && (
            <View style={[styles.changeContainer, isPositive ? styles.changePositive : styles.changeNegative]}>
              <Text style={[styles.changeText, isPositive ? styles.changeTextPositive : styles.changeTextNegative]}>
                {isPositive ? '+' : ''}{valueChangePercent.toFixed(1)}%
              </Text>
            </View>
          )}
        </View>
        <View style={styles.chevron}>
          <Text style={styles.chevronText}>{'>'}</Text>
        </View>
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
      {/* Gradient Header */}
      <LinearGradient
        colors={['#4f46e5', '#6366f1', '#818cf8']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.header}
      >
        <View style={styles.headerTop}>
          <Text style={styles.title}>My Assets</Text>
          <View style={styles.totalBadge}>
            <Text style={styles.totalBadgeText}>{data?.total || 0} Items</Text>
          </View>
        </View>
        <Text style={styles.totalLabel}>Total Portfolio Value</Text>
        <Text style={styles.totalValue}>
          ${totalValue.toLocaleString('en-US', { minimumFractionDigits: 2 })}
        </Text>

        {/* Category Summary Cards */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.summaryScroll}
          contentContainerStyle={styles.summaryContent}
        >
          {CATEGORIES.map(cat => {
            const count = data?.by_category?.[cat.key]?.count || 0;
            const value = data?.by_category?.[cat.key]?.total_value || 0;
            return (
              <TouchableOpacity
                key={cat.key}
                style={styles.summaryCard}
                onPress={() => setSelectedCategory(cat.key)}
              >
                <View style={styles.summaryIconContainer}>
                  <Text style={styles.summaryIcon}>{cat.icon}</Text>
                </View>
                <Text style={styles.summaryLabel}>{cat.label}</Text>
                <Text style={styles.summaryCount}>{count}</Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </LinearGradient>

      {/* Filter Tabs */}
      <View style={styles.tabsWrapper}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.tabsContent}
        >
          <TouchableOpacity
            style={[styles.tab, selectedCategory === 'all' && styles.tabActive]}
            onPress={() => setSelectedCategory('all')}
          >
            <Text style={[styles.tabText, selectedCategory === 'all' && styles.tabTextActive]}>
              All Assets
            </Text>
          </TouchableOpacity>
          {CATEGORIES.map(cat => {
            const count = data?.by_category?.[cat.key]?.count || 0;
            return (
              <TouchableOpacity
                key={cat.key}
                style={[styles.tab, selectedCategory === cat.key && styles.tabActive]}
                onPress={() => setSelectedCategory(cat.key)}
              >
                <Text style={styles.tabIcon}>{cat.icon}</Text>
                <Text style={[styles.tabText, selectedCategory === cat.key && styles.tabTextActive]}>
                  {cat.label}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {/* List */}
      <FlatList
        data={filteredAssets}
        renderItem={renderAsset}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <LinearGradient
              colors={['#e0e7ff', '#c7d2fe']}
              style={styles.emptyIconContainer}
            >
              <Text style={styles.emptyIcon}>💎</Text>
            </LinearGradient>
            <Text style={styles.emptyTitle}>No Assets Found</Text>
            <Text style={styles.emptyText}>
              {selectedCategory === 'all'
                ? 'Start tracking your valuables by adding your first asset'
                : `No ${selectedCategory} assets found. Try selecting a different category.`}
            </Text>
            <TouchableOpacity style={styles.emptyButton}>
              <LinearGradient
                colors={['#6366f1', '#4f46e5']}
                style={styles.emptyButtonGradient}
              >
                <Text style={styles.emptyButtonText}>Add Asset</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        }
      />
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
    paddingTop: 20,
    paddingBottom: 16,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  totalBadge: {
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  totalBadgeText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '600',
  },
  totalLabel: {
    fontSize: 13,
    color: 'rgba(255, 255, 255, 0.8)',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  totalValue: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#ffffff',
    marginTop: 4,
    marginBottom: 16,
  },
  summaryScroll: {
    marginHorizontal: -20,
  },
  summaryContent: {
    paddingHorizontal: 20,
    gap: 12,
  },
  summaryCard: {
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    borderRadius: 16,
    padding: 14,
    alignItems: 'center',
    minWidth: 80,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.2)',
  },
  summaryIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255, 255, 255, 0.25)',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  summaryIcon: {
    fontSize: 20,
  },
  summaryLabel: {
    fontSize: 11,
    color: 'rgba(255, 255, 255, 0.8)',
    marginBottom: 2,
  },
  summaryCount: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  tabsWrapper: {
    backgroundColor: '#ffffff',
    marginTop: -12,
    marginHorizontal: 16,
    borderRadius: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  tabsContent: {
    padding: 8,
    gap: 8,
  },
  tab: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
    backgroundColor: 'transparent',
    gap: 6,
  },
  tabActive: {
    backgroundColor: '#6366f1',
  },
  tabIcon: {
    fontSize: 14,
  },
  tabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#64748b',
  },
  tabTextActive: {
    color: '#ffffff',
  },
  listContent: {
    padding: 16,
    paddingTop: 12,
    gap: 12,
  },
  assetCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 16,
    shadowColor: '#6366f1',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  assetIconGradient: {
    width: 52,
    height: 52,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
  },
  assetIconText: {
    fontSize: 26,
  },
  assetInfo: {
    flex: 1,
    marginLeft: 14,
  },
  assetName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 2,
  },
  assetType: {
    fontSize: 13,
    color: '#64748b',
    textTransform: 'capitalize',
  },
  statusBadge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    marginTop: 6,
  },
  statusActive: {
    backgroundColor: '#dcfce7',
  },
  statusInactive: {
    backgroundColor: '#fef3c7',
  },
  statusText: {
    fontSize: 10,
    fontWeight: '600',
    textTransform: 'uppercase',
    color: '#166534',
  },
  assetValue: {
    alignItems: 'flex-end',
    marginRight: 8,
  },
  assetValueText: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0f172a',
  },
  changeContainer: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    marginTop: 4,
  },
  changePositive: {
    backgroundColor: '#dcfce7',
  },
  changeNegative: {
    backgroundColor: '#fef2f2',
  },
  changeText: {
    fontSize: 11,
    fontWeight: '600',
  },
  changeTextPositive: {
    color: '#16a34a',
  },
  changeTextNegative: {
    color: '#dc2626',
  },
  chevron: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  chevronText: {
    fontSize: 14,
    color: '#94a3b8',
    fontWeight: '600',
  },
  emptyContainer: {
    alignItems: 'center',
    padding: 48,
  },
  emptyIconContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyIcon: {
    fontSize: 48,
  },
  emptyTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 15,
    color: '#64748b',
    textAlign: 'center',
    lineHeight: 22,
    maxWidth: 280,
  },
  emptyButton: {
    marginTop: 24,
    borderRadius: 14,
    overflow: 'hidden',
  },
  emptyButtonGradient: {
    paddingHorizontal: 32,
    paddingVertical: 14,
  },
  emptyButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
});
