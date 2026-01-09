<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BudgetTransaction;
use App\Models\BudgetCategory;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Get all expenses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawExpenses = BudgetTransaction::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->with(['category', 'budget'])
            ->orderBy('transaction_date', 'desc')
            ->take(100)
            ->get();

        // Transform expenses to match mobile app format
        $expenses = $rawExpenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'formatted_amount' => '$' . number_format($expense->amount, 2),
                'date' => $expense->transaction_date?->format('M d, Y'),
                'transaction_date' => $expense->transaction_date,
                'status' => 'settled', // Budget transactions are always settled
                'category' => $expense->category ? [
                    'id' => $expense->category->id,
                    'name' => $expense->category->name,
                    'icon' => $expense->category->icon,
                    'color' => $expense->category->color,
                ] : null,
                'budget' => $expense->budget ? [
                    'id' => $expense->budget->id,
                    'name' => $expense->budget->name,
                ] : null,
                'payee' => $expense->payee,
                'is_shared' => $expense->is_shared,
            ];
        });

        // Get categories through budgets
        $budgetIds = Budget::where('tenant_id', $tenant->id)->pluck('id');
        $categories = BudgetCategory::whereIn('budget_id', $budgetIds)->get();

        // Get active budgets for this month with their spent amounts
        $budgets = Budget::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        // Calculate spent amount for each budget
        $budgetsWithStats = $budgets->map(function ($budget) use ($tenant) {
            $spentThisMonth = BudgetTransaction::where('tenant_id', $tenant->id)
                ->where('budget_id', $budget->id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');

            $remaining = $budget->total_amount - $spentThisMonth;
            $spentPercentage = $budget->total_amount > 0
                ? round(($spentThisMonth / $budget->total_amount) * 100, 1)
                : 0;

            return [
                'id' => $budget->id,
                'name' => $budget->name,
                'description' => $budget->description,
                'total_amount' => $budget->total_amount,
                'formatted_total_amount' => '$' . number_format($budget->total_amount, 2),
                'spent' => round($spentThisMonth, 2),
                'formatted_spent' => '$' . number_format($spentThisMonth, 2),
                'remaining' => round($remaining, 2),
                'formatted_remaining' => '$' . number_format($remaining, 2),
                'spent_percentage' => $spentPercentage,
                'is_over_budget' => $spentThisMonth > $budget->total_amount,
                'color' => $budget->color ?? '#6366f1',
                'icon' => $budget->icon ?? '💰',
            ];
        });

        $totalBudget = $budgets->sum('total_amount');

        // Calculate total spent this month
        $totalSpent = BudgetTransaction::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $remaining = $totalBudget - $totalSpent;
        $spentPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        // Calculate stats
        $totalThisMonth = $totalSpent;

        $totalLastMonth = BudgetTransaction::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->sum('amount');

        // Get spending by category
        $spendingByCategory = BudgetTransaction::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions, $categoryId) {
                $category = $transactions->first()->category;
                $total = $transactions->sum('amount');
                return [
                    'category_id' => $categoryId,
                    'category_name' => $category?->name ?? 'Uncategorized',
                    'category_icon' => $category?->icon ?? '📦',
                    'category_color' => $category?->color ?? '#6b7280',
                    'total' => round($total, 2),
                    'formatted_total' => '$' . number_format($total, 2),
                    'count' => $transactions->count(),
                ];
            })
            ->values();

        return $this->success([
            'expenses' => $expenses,
            'categories' => $categories,
            'budgets' => $budgetsWithStats,
            'spending_by_category' => $spendingByCategory,
            'stats' => [
                'total_budget' => round($totalBudget, 2),
                'formatted_total_budget' => '$' . number_format($totalBudget, 2),
                'total_spent' => round($totalSpent, 2),
                'formatted_total_spent' => '$' . number_format($totalSpent, 2),
                'remaining' => round($remaining, 2),
                'formatted_remaining' => '$' . number_format($remaining, 2),
                'spent_percentage' => $spentPercentage,
                'total_this_month' => round($totalThisMonth, 2),
                'formatted_this_month' => '$' . number_format($totalThisMonth, 2),
                'total_last_month' => round($totalLastMonth, 2),
                'formatted_last_month' => '$' . number_format($totalLastMonth, 2),
                'month_over_month_change' => $totalLastMonth > 0
                    ? round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100, 1)
                    : 0,
            ],
            'total' => $expenses->count(),
        ]);
    }

    /**
     * Get expenses by category.
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $expenses = BudgetTransaction::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->whereHas('category', function($q) use ($category) {
                $q->where('slug', $category);
            })
            ->with(['category', 'budget'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        return $this->success([
            'expenses' => $expenses,
            'total' => $expenses->count(),
        ]);
    }

    /**
     * Get a single expense.
     */
    public function show(Request $request, BudgetTransaction $expense): JsonResponse
    {
        $user = $request->user();

        if ($expense->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        return $this->success([
            'expense' => $expense->load(['category', 'budget']),
        ]);
    }
}
