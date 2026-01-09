<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Budget;
use App\Models\BudgetTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /**
     * Get all budgets for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $budgets = Budget::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($budget) use ($tenant) {
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
                    'period' => $budget->period,
                    'type' => $budget->type,
                ];
            });

        return $this->success([
            'budgets' => $budgets,
            'total' => $budgets->count(),
        ]);
    }

    /**
     * Get a single budget with details.
     */
    public function show(Request $request, Budget $budget): JsonResponse
    {
        $user = $request->user();

        if ($budget->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        // Calculate budget stats for current month
        $spentThisMonth = BudgetTransaction::where('budget_id', $budget->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $remaining = $budget->total_amount - $spentThisMonth;
        $spentPercentage = $budget->total_amount > 0
            ? round(($spentThisMonth / $budget->total_amount) * 100, 1)
            : 0;

        // Get expenses for this budget (current month)
        $expenses = BudgetTransaction::where('budget_id', $budget->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'formatted_amount' => '$' . number_format($expense->amount, 2),
                    'date' => $expense->transaction_date?->format('M d, Y'),
                    'transaction_date' => $expense->transaction_date,
                    'category' => $expense->category ? [
                        'id' => $expense->category->id,
                        'name' => $expense->category->name,
                        'icon' => $expense->category->icon,
                        'color' => $expense->category->color,
                    ] : null,
                    'payee' => $expense->payee,
                ];
            });

        // Get spending by category for this budget
        $spendingByCategory = BudgetTransaction::where('budget_id', $budget->id)
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

        // Get budget categories with their allocated amounts
        $categories = $budget->categories->map(function ($category) use ($budget) {
            $spent = BudgetTransaction::where('budget_id', $budget->id)
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');

            $categoryRemaining = $category->allocated_amount - $spent;
            $categoryPercentage = $category->allocated_amount > 0
                ? round(($spent / $category->allocated_amount) * 100, 1)
                : 0;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'allocated_amount' => $category->allocated_amount,
                'formatted_allocated' => '$' . number_format($category->allocated_amount, 2),
                'spent' => round($spent, 2),
                'formatted_spent' => '$' . number_format($spent, 2),
                'remaining' => round($categoryRemaining, 2),
                'formatted_remaining' => '$' . number_format($categoryRemaining, 2),
                'spent_percentage' => $categoryPercentage,
                'is_over_budget' => $spent > $category->allocated_amount,
            ];
        });

        return $this->success([
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'description' => $budget->description,
                'type' => $budget->type,
                'period' => $budget->period,
                'period_label' => $budget->period_label,
                'total_amount' => $budget->total_amount,
                'formatted_total_amount' => '$' . number_format($budget->total_amount, 2),
                'spent' => round($spentThisMonth, 2),
                'formatted_spent' => '$' . number_format($spentThisMonth, 2),
                'remaining' => round($remaining, 2),
                'formatted_remaining' => '$' . number_format($remaining, 2),
                'spent_percentage' => $spentPercentage,
                'is_over_budget' => $spentThisMonth > $budget->total_amount,
                'start_date' => $budget->start_date?->format('Y-m-d'),
                'end_date' => $budget->end_date?->format('Y-m-d'),
            ],
            'categories' => $categories,
            'expenses' => $expenses,
            'spending_by_category' => $spendingByCategory,
            'stats' => [
                'total_expenses' => $expenses->count(),
                'total_categories' => $categories->count(),
            ],
        ]);
    }
}
