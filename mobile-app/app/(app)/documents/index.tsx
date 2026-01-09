import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { documentsApi } from '../../../src/api';
import { InsurancePolicy, TaxReturn } from '../../../src/types';

type TabType = 'insurance' | 'tax-returns';

const POLICY_TYPES: Record<string, { label: string; icon: string }> = {
  health: { label: 'Health', icon: '🏥' },
  life: { label: 'Life', icon: '❤️' },
  auto: { label: 'Auto', icon: '🚗' },
  home: { label: 'Home', icon: '🏠' },
  other: { label: 'Other', icon: '📋' },
};

export default function DocumentsScreen() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<TabType>('insurance');

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['documents'],
    queryFn: async () => {
      const response = await documentsApi.getDocuments();
      return response.data.data;
    },
  });

  const insurancePolicies = data?.insurance_policies || [];
  const taxReturns = data?.tax_returns || [];

  const renderInsuranceCard = (policy: InsurancePolicy) => {
    const typeInfo = POLICY_TYPES[policy.policy_type] || POLICY_TYPES.other;
    return (
      <TouchableOpacity key={policy.id} style={styles.card}>
        <View style={styles.cardIcon}>
          <Text style={styles.cardIconText}>{typeInfo.icon}</Text>
        </View>
        <View style={styles.cardContent}>
          <Text style={styles.cardTitle}>{policy.provider}</Text>
          <Text style={styles.cardSubtitle}>{typeInfo.label} Insurance</Text>
          <Text style={styles.cardDetail}>Policy: {policy.policy_number}</Text>
          {policy.coverage_amount && (
            <Text style={styles.cardAmount}>
              Coverage: ${policy.coverage_amount.toLocaleString()}
            </Text>
          )}
        </View>
        <View style={styles.chevron}>
          <Text style={styles.chevronText}>›</Text>
        </View>
      </TouchableOpacity>
    );
  };

  const renderTaxReturnCard = (taxReturn: TaxReturn) => (
    <TouchableOpacity key={taxReturn.id} style={styles.card}>
      <View style={[styles.cardIcon, { backgroundColor: '#dbeafe' }]}>
        <Text style={styles.cardIconText}>📄</Text>
      </View>
      <View style={styles.cardContent}>
        <Text style={styles.cardTitle}>Tax Year {taxReturn.tax_year}</Text>
        <Text style={styles.cardSubtitle}>{taxReturn.filing_status?.replace(/_/g, ' ')}</Text>
        {taxReturn.filing_date && (
          <Text style={styles.cardDetail}>Filed: {taxReturn.filing_date}</Text>
        )}
        {taxReturn.refund_amount ? (
          <Text style={[styles.cardAmount, { color: '#16a34a' }]}>
            Refund: ${taxReturn.refund_amount.toLocaleString()}
          </Text>
        ) : taxReturn.amount_owed ? (
          <Text style={[styles.cardAmount, { color: '#dc2626' }]}>
            Owed: ${taxReturn.amount_owed.toLocaleString()}
          </Text>
        ) : null}
      </View>
      <View style={styles.chevron}>
        <Text style={styles.chevronText}>›</Text>
      </View>
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
        colors={['#6366f1', '#8b5cf6']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Documents</Text>
        <Text style={styles.headerSubtitle}>
          Manage insurance policies and tax returns
        </Text>
      </LinearGradient>

      {/* Tabs */}
      <View style={styles.tabsContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'insurance' && styles.tabActive]}
          onPress={() => setActiveTab('insurance')}
        >
          <Text style={styles.tabIcon}>🛡️</Text>
          <Text style={[styles.tabText, activeTab === 'insurance' && styles.tabTextActive]}>
            Insurance
          </Text>
          {insurancePolicies.length > 0 && (
            <View style={[styles.tabBadge, activeTab === 'insurance' && styles.tabBadgeActive]}>
              <Text style={styles.tabBadgeText}>{insurancePolicies.length}</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'tax-returns' && styles.tabActive]}
          onPress={() => setActiveTab('tax-returns')}
        >
          <Text style={styles.tabIcon}>📑</Text>
          <Text style={[styles.tabText, activeTab === 'tax-returns' && styles.tabTextActive]}>
            Tax Returns
          </Text>
          {taxReturns.length > 0 && (
            <View style={[styles.tabBadge, activeTab === 'tax-returns' && styles.tabBadgeActive]}>
              <Text style={styles.tabBadgeText}>{taxReturns.length}</Text>
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
        {activeTab === 'insurance' ? (
          insurancePolicies.length > 0 ? (
            insurancePolicies.map(renderInsuranceCard)
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🛡️</Text>
              <Text style={styles.emptyTitle}>No Insurance Policies</Text>
              <Text style={styles.emptyText}>Add your insurance policies to keep track of coverage</Text>
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#6366f1', '#4f46e5']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Add Policy</Text>
                </LinearGradient>
              </TouchableOpacity>
            </View>
          )
        ) : (
          taxReturns.length > 0 ? (
            taxReturns.map(renderTaxReturnCard)
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>📑</Text>
              <Text style={styles.emptyTitle}>No Tax Returns</Text>
              <Text style={styles.emptyText}>Track your tax return history and documents</Text>
              <TouchableOpacity style={styles.addButton}>
                <LinearGradient colors={['#6366f1', '#4f46e5']} style={styles.addButtonGradient}>
                  <Text style={styles.addButtonText}>Add Tax Return</Text>
                </LinearGradient>
              </TouchableOpacity>
            </View>
          )
        )}
      </ScrollView>

      {/* Floating Add Button */}
      <TouchableOpacity style={styles.fab}>
        <LinearGradient colors={['#6366f1', '#4f46e5']} style={styles.fabGradient}>
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
    backgroundColor: '#6366f1',
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
    backgroundColor: 'rgba(255,255,255,0.2)',
  },
  tabBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748b',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  card: {
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
  cardIcon: {
    width: 52,
    height: 52,
    borderRadius: 14,
    backgroundColor: '#e0e7ff',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cardIconText: {
    fontSize: 24,
  },
  cardContent: {
    flex: 1,
    marginLeft: 14,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
  },
  cardSubtitle: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
    textTransform: 'capitalize',
  },
  cardDetail: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 4,
  },
  cardAmount: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6366f1',
    marginTop: 4,
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
    maxWidth: 260,
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
    shadowColor: '#6366f1',
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
    fontWeight: '400',
  },
});
