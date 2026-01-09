import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, FlatList } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useQuery } from '@tanstack/react-query';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { budgetsApi } from '../../../src/api';

type CategoryItem = {
  id: number;
  name: string;
  icon: string;
  color: string;
  allocated_amount: number;
  formatted_allocated: string;
  spent: number;
  formatted_spent: string;
  remaining: number;
  formatted_remaining: string;
  spent_percentage: number;
  is_over_budget: boolean;
};

type ExpenseItem = {
  id: number;
  description: string;
  amount: number;
  formatted_amount: string;
  date: string;
  category: {
    id: number;
    name: string;
    icon: string;
    color: string;
  } | null;
  payee: string | null;
};

export default function BudgetDetailScreen() {
  const router = useRouter();
  const { id } = useLocalSearchParams<{ id: string }>();

  const { data, isLoading, refetch, isRefetching } = useQuery({
    queryKey: ['budget', id],
    queryFn: async () => {
      const response = await budgetsApi.getBudget(Number(id));
      return response.data.data;
    },
    enabled: !!id,
  });

  const budget = data?.budget;
  const categories = data?.categories || [];
  const expenses = data?.expenses || [];

  const getProgressColor = (percentage: number, isOver: boolean) => {
    if (isOver) return '#ef4444';
    if (percentage > 80) return '#f59e0b';
    return '#10b981';
  };

  const renderCategoryCard = (category: CategoryItem) => {
    const progressColor = getProgressColor(category.spent_percentage, category.is_over_budget);

    return (
      <View key={category.id} style={styles.categoryCard}>
        <View style={styles.categoryHeader}>
          <View style={[styles.categoryIconContainer, { backgroundColor: `${category.color}20` }]}>
            <Text style={styles.categoryIconText}>{category.icon}</Text>
          </View>
          <View style={styles.categoryInfo}>
            <Text style={styles.categoryName}>{category.name}</Text>
            <Text style={styles.categorySubtext}>
              {category.formatted_spent} of {category.formatted_allocated}
            </Text>
          </View>
          <View style={styles.categoryStats}>
            <Text style={[styles.categoryRemaining, category.is_over_budget && styles.overBudgetText]}>
              {category.formatted_remaining}
            </Text>
            <Text style={styles.categoryRemainingLabel}>
              {category.is_over_budget ? 'over' : 'left'}
            </Text>
          </View>
        </View>
        <View style={styles.categoryProgressContainer}>
          <View style={styles.categoryProgressBar}>
            <View
              style={[
                styles.categoryProgressFill,
                { width: `${Math.min(category.spent_percentage, 100)}%`, backgroundColor: progressColor }
              ]}
            />
          </View>
          <Text style={[styles.categoryProgressText, { color: progressColor }]}>
            {category.spent_percentage}%
          </Text>
        </View>
      </View>
    );
  };

  const renderExpenseItem = ({ item }: { item: ExpenseItem }) => (
    <View style={styles.expenseItem}>
      <View style={[styles.expenseIcon, { backgroundColor: item.category?.color ? `${item.category.color}20` : '#e5e7eb' }]}>
        <Text style={styles.expenseIconText}>{item.category?.icon || '💰'}</Text>
      </View>
      <View style={styles.expenseInfo}>
        <Text style={styles.expenseDescription} numberOfLines={1}>{item.description}</Text>
        <Text style={styles.expenseDate}>{item.date}</Text>
      </View>
      <Text style={styles.expenseAmount}>{item.formatted_amount}</Text>
    </View>
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

  if (!budget) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>Budget not found</Text>
          <TouchableOpacity onPress={() => router.back()} style={styles.backLink}>
            <Text style={styles.backLinkText}>Go back</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const progressColor = getProgressColor(budget.spent_percentage, budget.is_over_budget);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      {/* Header */}
      <LinearGradient
        colors={budget.is_over_budget ? ['#ef4444', '#dc2626'] : ['#6366f1', '#4f46e5']}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Text style={styles.backButtonText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>{budget.name}</Text>
        <Text style={styles.headerSubtitle}>{budget.period_label} Budget</Text>

        {/* Budget Summary */}
        <View style={styles.summaryRow}>
          <View style={styles.summaryItem}>
            <Text style={styles.summaryLabel}>Budget</Text>
            <Text style={styles.summaryValue}>{budget.formatted_total_amount}</Text>
          </View>
          <View style={styles.summaryDivider} />
          <View style={styles.summaryItem}>
            <Text style={styles.summaryLabel}>Spent</Text>
            <Text style={styles.summaryValue}>{budget.formatted_spent}</Text>
          </View>
          <View style={styles.summaryDivider} />
          <View style={styles.summaryItem}>
            <Text style={styles.summaryLabel}>{budget.is_over_budget ? 'Over' : 'Left'}</Text>
            <Text style={styles.summaryValue}>{budget.formatted_remaining}</Text>
          </View>
        </View>

        {/* Progress Bar */}
        <View style={styles.headerProgressContainer}>
          <View style={styles.headerProgressBar}>
            <View
              style={[
                styles.headerProgressFill,
                { width: `${Math.min(budget.spent_percentage, 100)}%` }
              ]}
            />
          </View>
          <Text style={styles.headerProgressText}>{budget.spent_percentage}% used</Text>
        </View>
      </LinearGradient>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={refetch} />
        }
      >
        {/* Categories Section */}
        {categories.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Categories</Text>
            {categories.map((category: CategoryItem) => renderCategoryCard(category))}
          </View>
        )}

        {/* Expenses Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Transactions</Text>
            <Text style={styles.sectionCount}>{expenses.length} this month</Text>
          </View>

          {expenses.length > 0 ? (
            <View style={styles.expensesList}>
              {expenses.map((expense: ExpenseItem) => (
                <View key={expense.id}>
                  {renderExpenseItem({ item: expense })}
                </View>
              ))}
            </View>
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>📝</Text>
              <Text style={styles.emptyTitle}>No Transactions</Text>
              <Text style={styles.emptyText}>No expenses recorded for this budget this month</Text>
            </View>
          )}
        </View>
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
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  errorText: {
    fontSize: 18,
    color: '#64748b',
    marginBottom: 16,
  },
  backLink: {
    paddingVertical: 8,
    paddingHorizontal: 16,
  },
  backLinkText: {
    fontSize: 16,
    color: '#6366f1',
    fontWeight: '600',
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
    marginBottom: 20,
  },
  summaryRow: {
    flexDirection: 'row',
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderRadius: 16,
    padding: 16,
  },
  summaryItem: {
    flex: 1,
    alignItems: 'center',
  },
  summaryDivider: {
    width: 1,
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  summaryLabel: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
    marginBottom: 4,
  },
  summaryValue: {
    fontSize: 18,
    fontWeight: '700',
    color: '#ffffff',
  },
  headerProgressContainer: {
    marginTop: 16,
  },
  headerProgressBar: {
    height: 8,
    backgroundColor: 'rgba(255,255,255,0.3)',
    borderRadius: 4,
    overflow: 'hidden',
  },
  headerProgressFill: {
    height: '100%',
    backgroundColor: '#ffffff',
    borderRadius: 4,
  },
  headerProgressText: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.9)',
    marginTop: 8,
    textAlign: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 40,
  },
  section: {
    padding: 16,
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
  sectionCount: {
    fontSize: 14,
    color: '#64748b',
  },
  categoryCard: {
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
  categoryHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  categoryIconContainer: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  categoryIconText: {
    fontSize: 20,
  },
  categoryInfo: {
    flex: 1,
    marginLeft: 12,
  },
  categoryName: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1e293b',
  },
  categorySubtext: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
  },
  categoryStats: {
    alignItems: 'flex-end',
  },
  categoryRemaining: {
    fontSize: 15,
    fontWeight: '700',
    color: '#10b981',
  },
  overBudgetText: {
    color: '#ef4444',
  },
  categoryRemainingLabel: {
    fontSize: 11,
    color: '#94a3b8',
  },
  categoryProgressContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  categoryProgressBar: {
    flex: 1,
    height: 6,
    backgroundColor: '#e2e8f0',
    borderRadius: 3,
    overflow: 'hidden',
  },
  categoryProgressFill: {
    height: '100%',
    borderRadius: 3,
  },
  categoryProgressText: {
    fontSize: 12,
    fontWeight: '600',
    width: 40,
    textAlign: 'right',
  },
  expensesList: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  expenseItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  expenseIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  expenseIconText: {
    fontSize: 18,
  },
  expenseInfo: {
    flex: 1,
    marginLeft: 12,
  },
  expenseDescription: {
    fontSize: 15,
    fontWeight: '500',
    color: '#1e293b',
  },
  expenseDate: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 2,
  },
  expenseAmount: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1e293b',
  },
  emptyContainer: {
    alignItems: 'center',
    padding: 40,
    backgroundColor: '#ffffff',
    borderRadius: 16,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1e293b',
    marginBottom: 4,
  },
  emptyText: {
    fontSize: 14,
    color: '#64748b',
    textAlign: 'center',
  },
});
