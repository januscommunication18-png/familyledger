<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BudgetTransaction;
use App\Models\BudgetCategory;
use App\Models\Budget;
use App\Models\FamilyMember;
use App\Models\Collaborator;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Get categories for expense creation.
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get active budget
        $budget = Budget::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();

        if (!$budget) {
            return $this->success([
                'categories' => [],
                'budgets' => [],
                'children' => [],
            ]);
        }

        $categories = $budget->categories()->ordered()->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'icon' => $cat->icon,
                'color' => $cat->color,
            ];
        });

        $budgets = Budget::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'icon' => $b->icon ?? '💰',
                    'color' => $b->color ?? '#6366f1',
                ];
            });

        // Get children for co-parenting expense sharing
        $ownChildren = FamilyMember::where('tenant_id', $tenant->id)
            ->with(['coparents.user'])
            ->where(function ($q) {
                $q->where('is_minor', true)
                    ->orWhere('relationship', 'child')
                    ->orWhere('relationship', 'stepchild');
            })
            ->get();

        // Get children accessible via co-parenting relationship
        $coparentChildren = collect();
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('coparentChildren')
            ->first();

        if ($collaborator) {
            $coparentChildren = $collaborator->coparentChildren()
                ->with(['familyCircle.creator'])
                ->get()
                ->map(function ($child) {
                    $owner = $child->familyCircle?->creator;
                    $child->otherParentName = $owner?->name ?? 'Parent';
                    $child->otherParentId = $owner?->id;
                    $child->isCoparentChild = true;
                    return $child;
                });
        }

        // Merge and transform children
        $children = $ownChildren->merge($coparentChildren)->unique('id')->sortBy('first_name')->values()
            ->map(function ($child) {
                // Determine co-parent info
                $hasCoparent = false;
                $coparentName = null;

                if (!empty($child->isCoparentChild)) {
                    $hasCoparent = !empty($child->otherParentId);
                    $coparentName = $child->otherParentName ?? 'Parent';
                } else {
                    $coparent = $child->coparents->first();
                    $hasCoparent = $coparent !== null;
                    $coparentName = $coparent?->user?->name;
                }

                return [
                    'id' => $child->id,
                    'name' => $child->full_name ?? ($child->first_name . ' ' . $child->last_name),
                    'first_name' => $child->first_name,
                    'has_coparent' => $hasCoparent,
                    'coparent_name' => $coparentName,
                ];
            });

        return $this->success([
            'categories' => $categories,
            'budgets' => $budgets,
            'children' => $children,
        ]);
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'payee' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:budget_categories,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|string', // Base64 encoded image
            // Co-parenting fields
            'is_shared' => 'nullable|boolean',
            'shared_for_child_id' => 'nullable|exists:family_members,id',
            'request_payment' => 'nullable|boolean',
            'split_percentage' => 'nullable|numeric|min:1|max:100',
            'payment_note' => 'nullable|string|max:500',
        ]);

        // Get active budget if not specified
        $budgetId = $validated['budget_id'] ?? null;
        if (!$budgetId) {
            $budget = Budget::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();
            $budgetId = $budget?->id;
        }

        if (!$budgetId) {
            return $this->error('No active budget found. Please create a budget first.', 422);
        }

        // Handle base64 receipt upload
        $receiptPath = null;
        $receiptOriginalFilename = null;
        if (!empty($validated['receipt'])) {
            try {
                // Decode base64 image
                $imageData = $validated['receipt'];

                // Extract image type and data
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                } else {
                    $imageType = 'jpg';
                }

                $decodedImage = base64_decode($imageData);

                // Generate filename
                $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $imageType;
                $receiptPath = 'receipts/' . $tenant->id . '/' . $filename;
                $receiptOriginalFilename = $filename;

                // Store the file
                \Illuminate\Support\Facades\Storage::disk('public')->put($receiptPath, $decodedImage);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::error('Failed to save receipt: ' . $e->getMessage());
            }
        }

        $isShared = $request->boolean('is_shared');
        $sharedForChildId = $isShared ? ($validated['shared_for_child_id'] ?? null) : null;

        $transaction = BudgetTransaction::create([
            'tenant_id' => $tenant->id,
            'budget_id' => $budgetId,
            'created_by' => $user->id,
            'type' => 'expense',
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'payee' => $validated['payee'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'source' => 'manual', // Mobile entries are stored as manual
            'is_shared' => $isShared,
            'shared_for_child_id' => $sharedForChildId,
            'receipt_path' => $receiptPath,
            'receipt_original_filename' => $receiptOriginalFilename,
        ]);

        // Create payment request if requested
        $paymentRequest = null;
        if ($isShared && $request->boolean('request_payment') && $sharedForChildId) {
            $child = FamilyMember::find($sharedForChildId);
            if ($child) {
                // Find the co-parent to request payment from
                $coparent = $child->coparents->first();
                if ($coparent && $coparent->user_id) {
                    $splitPercentage = $validated['split_percentage'] ?? 50;
                    $requestAmount = ($validated['amount'] * $splitPercentage) / 100;

                    $paymentRequest = PaymentRequest::create([
                        'tenant_id' => $tenant->id,
                        'transaction_id' => $transaction->id,
                        'requester_id' => $user->id,
                        'payer_id' => $coparent->user_id,
                        'child_id' => $sharedForChildId,
                        'amount' => $requestAmount,
                        'split_percentage' => $splitPercentage,
                        'note' => $validated['payment_note'] ?? null,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        $transaction->load(['category', 'budget']);

        return $this->success([
            'expense' => [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'formatted_amount' => '$' . number_format($transaction->amount, 2),
                'date' => $transaction->transaction_date?->format('M d, Y'),
                'transaction_date' => $transaction->transaction_date,
                'category' => $transaction->category ? [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name,
                    'icon' => $transaction->category->icon,
                    'color' => $transaction->category->color,
                ] : null,
                'budget' => $transaction->budget ? [
                    'id' => $transaction->budget->id,
                    'name' => $transaction->budget->name,
                ] : null,
                'payee' => $transaction->payee,
                'is_shared' => $transaction->is_shared,
                'has_receipt' => !empty($receiptPath),
                'payment_requested' => $paymentRequest !== null,
            ],
        ], 'Expense created successfully', 201);
    }
}
