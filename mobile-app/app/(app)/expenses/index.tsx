import { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, FlatList } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { expensesApi } from '../../../src/api';
import { Expense } from '../../../src/types';

export default function ExpensesScreen() {
  const router = useRouter();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['expenses'],
    queryFn: async () => {
      const response = await expensesApi.getExpenses();
      return response.data.data;
    },
  });

  const stats = data?.stats;
  const expenses = data?.expenses || [];
  const spendingByCategory = data?.spending_by_category || [];
  const budgets = data?.budgets || [];

  // Define a type for budget items
  type BudgetItem = {
    id: number;
    name: string;
    description?: string;
    total_amount: number;
    formatted_total_amount: string;
    spent: number;
    formatted_spent: string;
    remaining: number;
    formatted_remaining: string;
    spent_percentage: number;
    is_over_budget: boolean;
    color: string;
    icon: string;
  };

  const renderBudgetCard = (budget: BudgetItem) => {
    const progressColor = budget.is_over_budget ? '#ef4444' : budget.spent_percentage > 80 ? '#f59e0b' : '#10b981';

    return (
      <TouchableOpacity
        key={budget.id}
        style={styles.budgetCard}
        onPress={() => router.push(`/(app)/budget/${budget.id}`)}
        activeOpacity={0.7}
      >
        <View style={styles.budgetHeader}>
          <View style={[styles.budgetIconContainer, { backgroundColor: `${budget.color}20` }]}>
            <Text style={styles.budgetIconText}>{budget.icon}</Text>
          </View>
          <View style={styles.budgetInfo}>
            <Text style={styles.budgetTitle}>{budget.name}</Text>
            <Text style={styles.budgetSubtitle}>
              {budget.formatted_spent} of {budget.formatted_total_amount}
            </Text>
          </View>
          <View style={styles.budgetStats}>
            <Text style={[styles.budgetRemaining, budget.is_over_budget && styles.budgetOverBudget]}>
              {budget.is_over_budget ? '-' : ''}{budget.formatted_remaining}
            </Text>
            <Text style={styles.budgetRemainingLabel}>
              {budget.is_over_budget ? 'over budget' : 'remaining'}
            </Text>
          </View>
          <View style={styles.budgetChevron}>
            <Text style={styles.budgetChevronText}>›</Text>
          </View>
        </View>
        <View style={styles.budgetProgressContainer}>
          <View style={styles.budgetProgressBar}>
            <View
              style={[
                styles.budgetProgressFill,
                { width: `${Math.min(budget.spent_percentage, 100)}%`, backgroundColor: progressColor }
              ]}
            />
          </View>
          <Text style={[styles.budgetProgressText, { color: progressColor }]}>
            {budget.spent_percentage}%
          </Text>
        </View>
      </TouchableOpacity>
    );
  };

  const renderExpenseCard = ({ item }: { item: Expense }) => (
    <TouchableOpacity style={styles.expenseCard}>
      <View style={styles.expenseLeft}>
        <View style={[styles.expenseIcon, { backgroundColor: item.category?.color ? `${item.category.color}20` : '#d1fae5' }]}>
          <Text style={styles.expenseIconText}>
            {item.category?.icon || '💰'}
          </Text>
        </View>
        <View style={styles.expenseInfo}>
          <Text style={styles.expenseDescription} numberOfLines={1}>{item.description}</Text>
          <Text style={styles.expenseDate}>{item.date}</Text>
          {item.category && (
            <View style={[styles.categoryBadge, { backgroundColor: `${item.category.color}15` }]}>
              <Text style={[styles.categoryText, { color: item.category.color }]}>{item.category.name}</Text>
            </View>
          )}
        </View>
      </View>
      <View style={styles.expenseRight}>
        <Text style={styles.expenseAmount}>{item.formatted_amount || `$${item.amount}`}</Text>
        {item.budget && (
          <Text style={styles.budgetName}>{item.budget.name}</Text>
        )}
      </View>
    </TouchableOpacity>
  );

  const renderCategoryCard = ({ item }: { item: any }) => (
    <View style={styles.categoryCard}>
      <View style={[styles.categoryIcon, { backgroundColor: `${item.category_color}20` }]}>
        <Text style={styles.categoryIconText}>{item.category_icon}</Text>
      </View>
      <Text style={styles.categoryStat}>{item.formatted_total}</Text>
      <Text style={styles.categoryName}>{item.category_name}</Text>
      <Text style={styles.categoryCount}>{item.count} expense{item.count !== 1 ? 's' : ''}</Text>
    </View>
  );

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#059669" />
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
        <Text style={styles.headerTitle}>Expenses</Text>
        <Text style={styles.headerSubtitle}>Monthly budget overview</Text>
      </LinearGradient>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Budget Summary Cards */}
        <View style={styles.summaryContainer}>
          <View style={styles.mainStatCard}>
            <Text style={styles.mainStatLabel}>Total Budget</Text>
            <Text style={styles.mainStatValue}>{stats?.formatted_total_budget || '$0.00'}</Text>
            <Text style={styles.mainStatSubtext}>Monthly budget</Text>
          </View>

          <View style={styles.statsRow}>
            <View style={[styles.statCard, styles.statCardSpent]}>
              <Text style={styles.statLabel}>Total Spent</Text>
              <Text style={[styles.statValue, styles.statValueSpent]}>{stats?.formatted_total_spent || '$0.00'}</Text>
              <View style={styles.percentBadge}>
                <Text style={styles.percentText}>{stats?.spent_percentage || 0}%</Text>
              </View>
            </View>
            <View style={[styles.statCard, styles.statCardRemaining]}>
              <Text style={styles.statLabel}>Remaining</Text>
              <Text style={[styles.statValue, styles.statValueRemaining]}>{stats?.formatted_remaining || '$0.00'}</Text>
            </View>
          </View>
        </View>

        {/* Progress Bar */}
        <View style={styles.progressContainer}>
          <View style={styles.progressBar}>
            <View
              style={[
                styles.progressFill,
                { width: `${Math.min(stats?.spent_percentage || 0, 100)}%` },
                (stats?.spent_percentage || 0) > 80 && styles.progressFillWarning
              ]}
            />
          </View>
          <Text style={styles.progressText}>
            {stats?.spent_percentage || 0}% of budget used
          </Text>
        </View>

        {/* Individual Budgets */}
        {budgets.length > 0 && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>My Budgets</Text>
              <Text style={styles.expenseCount}>{budgets.length} budget{budgets.length !== 1 ? 's' : ''}</Text>
            </View>
            {budgets.map((budget: BudgetItem) => renderBudgetCard(budget))}
          </View>
        )}

        {/* Spending by Category */}
        {spendingByCategory.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Spending by Category</Text>
            <FlatList
              horizontal
              data={spendingByCategory}
              renderItem={renderCategoryCard}
              keyExtractor={(item) => item.category_id?.toString() || 'uncategorized'}
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.categoriesContainer}
            />
          </View>
        )}

        {/* Recent Expenses */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Expenses</Text>
            <Text style={styles.expenseCount}>{expenses.length} total</Text>
          </View>

          {expenses.length > 0 ? (
            expenses.map((expense: Expense) => (
              <View key={expense.id}>
                {renderExpenseCard({ item: expense })}
              </View>
            ))
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🧾</Text>
              <Text style={styles.emptyTitle}>No Expenses</Text>
              <Text style={styles.emptyText}>Start tracking your expenses</Text>
            </View>
          )}
        </View>
      </ScrollView>

      {/* FAB */}
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
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 100,
  },
  summaryContainer: {
    padding: 16,
    marginTop: -12,
  },
  mainStatCard: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
    marginBottom: 12,
  },
  mainStatLabel: {
    fontSize: 14,
    color: '#64748b',
    marginBottom: 8,
  },
  mainStatValue: {
    fontSize: 36,
    fontWeight: '700',
    color: '#1e293b',
  },
  mainStatSubtext: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 4,
  },
  statsRow: {
    flexDirection: 'row',
    gap: 12,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statCardSpent: {
    borderLeftWidth: 4,
    borderLeftColor: '#ef4444',
  },
  statCardRemaining: {
    borderLeftWidth: 4,
    borderLeftColor: '#10b981',
  },
  statLabel: {
    fontSize: 12,
    color: '#64748b',
    marginBottom: 4,
  },
  statValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1e293b',
  },
  statValueSpent: {
    color: '#ef4444',
  },
  statValueRemaining: {
    color: '#10b981',
  },
  percentBadge: {
    backgroundColor: '#fef2f2',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
    alignSelf: 'flex-start',
    marginTop: 6,
  },
  percentText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#ef4444',
  },
  progressContainer: {
    paddingHorizontal: 16,
    marginBottom: 20,
  },
  progressBar: {
    height: 8,
    backgroundColor: '#e2e8f0',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#10b981',
    borderRadius: 4,
  },
  progressFillWarning: {
    backgroundColor: '#f59e0b',
  },
  progressText: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 6,
    textAlign: 'center',
  },
  section: {
    paddingHorizontal: 16,
    marginBottom: 20,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 12,
  },
  expenseCount: {
    fontSize: 14,
    color: '#64748b',
  },
  categoriesContainer: {
    gap: 12,
  },
  categoryCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    width: 120,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  categoryIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  categoryIconText: {
    fontSize: 20,
  },
  categoryStat: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
  },
  categoryName: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
    textAlign: 'center',
  },
  categoryCount: {
    fontSize: 10,
    color: '#94a3b8',
    marginTop: 2,
  },
  expenseCard: {
    flexDirection: 'row',
    justifyContent: 'space-between',
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
  expenseLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  expenseIcon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  expenseIconText: {
    fontSize: 18,
  },
  expenseInfo: {
    marginLeft: 12,
    flex: 1,
  },
  expenseDescription: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1e293b',
  },
  expenseDate: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
  categoryBadge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
    alignSelf: 'flex-start',
    marginTop: 6,
  },
  categoryText: {
    fontSize: 11,
    fontWeight: '500',
  },
  expenseRight: {
    alignItems: 'flex-end',
  },
  expenseAmount: {
    fontSize: 17,
    fontWeight: '700',
    color: '#1e293b',
  },
  budgetName: {
    fontSize: 11,
    color: '#94a3b8',
    marginTop: 2,
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
  budgetCard: {
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
  budgetHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  budgetIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
  },
  budgetIconText: {
    fontSize: 22,
  },
  budgetInfo: {
    flex: 1,
    marginLeft: 12,
  },
  budgetTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1e293b',
  },
  budgetSubtitle: {
    fontSize: 13,
    color: '#64748b',
    marginTop: 2,
  },
  budgetStats: {
    alignItems: 'flex-end',
  },
  budgetRemaining: {
    fontSize: 16,
    fontWeight: '700',
    color: '#10b981',
  },
  budgetOverBudget: {
    color: '#ef4444',
  },
  budgetRemainingLabel: {
    fontSize: 11,
    color: '#94a3b8',
    marginTop: 2,
  },
  budgetProgressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  budgetProgressBar: {
    flex: 1,
    height: 8,
    backgroundColor: '#e2e8f0',
    borderRadius: 4,
    overflow: 'hidden',
  },
  budgetProgressFill: {
    height: '100%',
    borderRadius: 4,
  },
  budgetProgressText: {
    fontSize: 12,
    fontWeight: '600',
    width: 40,
    textAlign: 'right',
  },
  budgetChevron: {
    marginLeft: 8,
  },
  budgetChevronText: {
    fontSize: 20,
    color: '#94a3b8',
    fontWeight: '300',
  },
  fab: {
    position: 'absolute',
    right: 20,
    bottom: 24,
    borderRadius: 28,
    overflow: 'hidden',
    shadowColor: '#059669',
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
