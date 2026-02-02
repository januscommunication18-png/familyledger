<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\BudgetCategory;
use App\Models\BudgetGoal;
use App\Models\BudgetShare;
use App\Models\BudgetTransaction;
use App\Models\Collaborator;
use App\Models\FamilyMember;
use App\Models\SharedExpensePayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExpensesController extends Controller
{
    /**
     * Main expenses index - redirects based on state.
     */
    public function index()
    {
        $user = Auth::user();
        // Don't use forCurrentTenant() here - accessibleByUser() handles cross-tenant shared budgets
        $hasBudget = Budget::accessibleByUser()->active()->exists();

        if (!$hasBudget) {
            return redirect()->route('expenses.intro');
        }

        // Enter expenses mode automatically
        session(['expenses_mode' => true]);

        return redirect()->route('expenses.dashboard');
    }

    /**
     * Landing/intro page with video and features.
     */
    public function intro()
    {
        session(['expenses_mode' => true]);

        // Don't use forCurrentTenant() - accessibleByUser() handles cross-tenant shared budgets
        $hasBudget = Budget::accessibleByUser()->exists();

        return view('pages.expenses.intro', [
            'hasBudget' => $hasBudget,
        ]);
    }

    /**
     * Budget creation wizard.
     */
    public function createBudget(Request $request)
    {
        session(['expenses_mode' => true]);

        $step = (int) $request->get('step', 1);
        $wizardData = session('budget_wizard', []);

        // Handle skip_share parameter
        if ($request->get('skip_share')) {
            $wizardData['share_with_members'] = [];
            session(['budget_wizard' => $wizardData]);
        }

        // Get all family circles and members for step 5
        $familyCircles = collect();
        if ($step === 5) {
            $familyCircles = \App\Models\FamilyCircle::forCurrentTenant()
                ->with(['members' => function ($query) {
                    $query->where('is_minor', false)
                        ->whereNotNull('linked_user_id')
                        ->where('linked_user_id', '!=', auth()->id());
                }])
                ->get();
        }

        return view('pages.expenses.wizard.create', [
            'step' => $step,
            'wizardData' => $wizardData,
            'budgetTypes' => Budget::TYPES,
            'periods' => Budget::PERIODS,
            'defaultCategories' => $this->getDefaultCategories(),
            'familyCircles' => $familyCircles,
            'goalTypes' => BudgetGoal::TYPES,
            'goalIcons' => BudgetGoal::ICONS,
        ]);
    }

    /**
     * Process budget wizard step.
     */
    public function storeBudget(Request $request)
    {
        $step = (int) $request->get('step', 1);
        $wizardData = session('budget_wizard', []);

        // Validate based on step
        $validated = $this->validateWizardStep($request, $step);

        if ($validated === false) {
            return back()->withErrors(['error' => 'Invalid data provided.']);
        }

        // Merge validated data into wizard session
        $wizardData = array_merge($wizardData, $validated);
        session(['budget_wizard' => $wizardData]);

        // If final step (6), create the budget
        if ($step >= 6) {
            return $this->finalizeBudget($wizardData);
        }

        // Determine next step
        $nextStep = $step + 1;

        // For envelope budgets, skip step 3 (total amount) since income is collected in step 2
        if ($step === 2 && ($wizardData['type'] ?? 'envelope') === 'envelope') {
            $nextStep = 4; // Skip to categories
        }

        // Move to next step
        return redirect()->route('expenses.budget.create', ['step' => $nextStep]);
    }

    /**
     * Validate wizard step data.
     */
    protected function validateWizardStep(Request $request, int $step): array|false
    {
        $wizardData = session('budget_wizard', []);
        $isTraditional = ($wizardData['type'] ?? 'envelope') === 'traditional';

        $isEnvelope = ($wizardData['type'] ?? 'envelope') === 'envelope';

        $rules = match ($step) {
            1 => ['type' => 'required|in:envelope,traditional'],
            2 => $isEnvelope
                ? [
                    // Envelope budget includes income in step 2
                    'name' => 'required|string|max:100',
                    'period' => 'required|in:weekly,biweekly,monthly,yearly',
                    'start_date' => 'required|date',
                    'total_amount' => 'required|numeric|min:0',
                ]
                : [
                    // Traditional budget only gets name, period, start_date in step 2
                    'name' => 'required|string|max:100',
                    'period' => 'required|in:weekly,biweekly,monthly,yearly',
                    'start_date' => 'required|date',
                ],
            3 => ['total_amount' => 'required|numeric|min:0'],
            4 => $isTraditional
                ? [
                    // Traditional budget uses goals
                    'goals' => 'required|array|min:1',
                    'goals.*.name' => 'required|string|max:100',
                    'goals.*.description' => 'nullable|string|max:255',
                    'goals.*.type' => 'required|in:expense,income,saving',
                    'goals.*.target_amount' => 'required|numeric|min:0',
                    'goals.*.icon' => 'nullable|string|max:10',
                ]
                : [
                    // Envelope budget uses categories
                    'categories' => 'required|array|min:1',
                    'categories.*.name' => 'required|string|max:50',
                    'categories.*.allocated_amount' => 'required|numeric|min:0',
                    'categories.*.icon' => 'nullable|string|max:10',
                    'categories.*.color' => 'nullable|string|max:20',
                ],
            5 => [
                'share_with_members' => 'nullable|array',
                'share_with_members.*' => 'exists:family_members,id',
                'share_permission' => 'nullable|in:view,edit,admin',
            ],
            6 => [], // Review step - no validation needed
            default => [],
        };

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return false;
        }

        return $validator->validated();
    }

    /**
     * Finalize budget creation.
     */
    protected function finalizeBudget(array $wizardData)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Create budget
            $budget = Budget::create([
                'tenant_id' => $user->tenant_id,
                'created_by' => $user->id,
                'name' => $wizardData['name'],
                'type' => $wizardData['type'],
                'total_amount' => $wizardData['total_amount'],
                'period' => $wizardData['period'],
                'start_date' => $wizardData['start_date'],
                'is_active' => true,
            ]);

            // Create categories (for envelope budgets) or goals (for traditional budgets)
            $sortOrder = 0;

            if ($wizardData['type'] === 'traditional' && !empty($wizardData['goals'])) {
                // Create goals for traditional budget
                foreach ($wizardData['goals'] as $goalData) {
                    BudgetGoal::create([
                        'budget_id' => $budget->id,
                        'name' => $goalData['name'],
                        'description' => $goalData['description'] ?? null,
                        'type' => $goalData['type'],
                        'target_amount' => $goalData['target_amount'],
                        'icon' => $goalData['icon'] ?? null,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            } elseif (!empty($wizardData['categories'])) {
                // Create categories for envelope budget
                foreach ($wizardData['categories'] as $categoryData) {
                    BudgetCategory::create([
                        'budget_id' => $budget->id,
                        'name' => $categoryData['name'],
                        'icon' => $categoryData['icon'] ?? null,
                        'color' => $categoryData['color'] ?? null,
                        'allocated_amount' => $categoryData['allocated_amount'],
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            // Create budget shares for selected family members
            if (!empty($wizardData['share_with_members'])) {
                $permission = $wizardData['share_permission'] ?? 'edit';

                foreach ($wizardData['share_with_members'] as $memberId) {
                    $familyMember = FamilyMember::find($memberId);

                    if ($familyMember && $familyMember->linked_user_id) {
                        // Find or create collaborator for this user
                        $collaborator = Collaborator::forCurrentTenant()
                            ->where('user_id', $familyMember->linked_user_id)
                            ->first();

                        if (!$collaborator) {
                            $collaborator = Collaborator::create([
                                'tenant_id' => $user->tenant_id,
                                'user_id' => $familyMember->linked_user_id,
                                'invited_by' => $user->id,
                                'relationship_type' => 'relative',
                                'role' => 'contributor',
                                'is_active' => true,
                            ]);
                        }

                        // Create budget share
                        BudgetShare::create([
                            'budget_id' => $budget->id,
                            'collaborator_id' => $collaborator->id,
                            'permission' => $permission,
                        ]);
                    }
                }
            }

            DB::commit();

            // Clear wizard session
            session()->forget('budget_wizard');

            // Set the new budget as selected
            session(['selected_budget_id' => $budget->id]);

            return redirect()->route('expenses.dashboard')
                ->with('success', 'Budget created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create budget: ' . $e->getMessage()]);
        }
    }

    /**
     * Get default category templates.
     */
    protected function getDefaultCategories(): array
    {
        return [
            ['name' => 'Housing', 'icon' => '🏠', 'color' => '#ef4444'],
            ['name' => 'Utilities', 'icon' => '💡', 'color' => '#f97316'],
            ['name' => 'Groceries', 'icon' => '🛒', 'color' => '#22c55e'],
            ['name' => 'Transportation', 'icon' => '🚗', 'color' => '#3b82f6'],
            ['name' => 'Healthcare', 'icon' => '🏥', 'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => '🎬', 'color' => '#8b5cf6'],
            ['name' => 'Dining Out', 'icon' => '🍽️', 'color' => '#f59e0b'],
            ['name' => 'Shopping', 'icon' => '🛍️', 'color' => '#06b6d4'],
            ['name' => 'Savings', 'icon' => '💰', 'color' => '#10b981'],
            ['name' => 'Other', 'icon' => '📦', 'color' => '#6b7280'],
        ];
    }

    /**
     * Edit budget settings.
     */
    public function editBudget(Budget $budget)
    {
        $this->authorizeBudgetAccess($budget, 'admin');

        return view('pages.expenses.budget-edit', [
            'budget' => $budget,
            'budgetTypes' => Budget::TYPES,
            'periods' => Budget::PERIODS,
        ]);
    }

    /**
     * Update budget settings.
     */
    public function updateBudget(Request $request, Budget $budget)
    {
        $this->authorizeBudgetAccess($budget, 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'total_amount' => 'required|numeric|min:0',
            'period' => 'required|in:weekly,biweekly,monthly,yearly',
        ]);

        $budget->update($validated);

        return redirect()->route('expenses.dashboard')
            ->with('success', 'Budget updated successfully!');
    }

    /**
     * Delete budget.
     */
    public function deleteBudget(Budget $budget)
    {
        $this->authorizeBudgetAccess($budget, 'admin');

        $budget->delete();

        return redirect()->route('expenses.intro')
            ->with('success', 'Budget deleted successfully!');
    }

    // ==================== CATEGORIES ====================

    /**
     * Show categories management.
     */
    public function categories()
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $this->authorizeBudgetAccess($budget);

        $categories = $budget->categories()->ordered()->get();

        return view('pages.expenses.categories.index', [
            'budget' => $budget,
            'categories' => $categories,
            'defaultIcons' => BudgetCategory::DEFAULT_ICONS,
            'defaultColors' => BudgetCategory::DEFAULT_COLORS,
        ]);
    }

    /**
     * Store new category.
     */
    public function storeCategory(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return back()->withErrors(['error' => 'No budget selected.']);
        }

        $this->authorizeBudgetAccess($budget, 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'allocated_amount' => 'required|numeric|min:0',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = $budget->categories()->max('sort_order') ?? -1;

        BudgetCategory::create([
            'budget_id' => $budget->id,
            'name' => $validated['name'],
            'allocated_amount' => $validated['allocated_amount'],
            'icon' => $validated['icon'] ?? '📦',
            'color' => $validated['color'] ?? '#6b7280',
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Category added successfully!');
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, BudgetCategory $category)
    {
        $this->authorizeBudgetAccess($category->budget, 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'allocated_amount' => 'required|numeric|min:0',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category.
     */
    public function deleteCategory(BudgetCategory $category)
    {
        $this->authorizeBudgetAccess($category->budget, 'admin');

        // Move transactions to uncategorized
        $category->transactions()->update(['category_id' => null]);

        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Reorder categories.
     */
    public function reorderCategories(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:budget_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $item) {
            BudgetCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    // ==================== TRANSACTIONS ====================

    /**
     * Show transactions list.
     */
    public function transactions(Request $request)
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $query = $budget->transactions()->with(['category', 'creator', 'sharedForChild']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('payee', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(25);

        $categories = $budget->categories()->ordered()->get();

        // Get children for shared expense selection (co-parenting)
        // Include both own children and children accessible via co-parenting
        $user = Auth::user();

        // Get children from current tenant (if user owns them)
        $ownChildren = FamilyMember::forCurrentTenant()
            ->with(['coparents.user'])
            ->where(function ($q) {
                $q->where('is_minor', true)
                    ->orWhere('relationship', 'child')
                    ->orWhere('relationship', 'stepchild');
            })
            ->get();

        // Get children accessible via co-parenting relationship (any collaborator with coparent children)
        $coparentChildren = collect();
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('coparentChildren') // Has co-parenting children assigned
            ->first();

        if ($collaborator) {
            $coparentChildren = $collaborator->coparentChildren()
                ->with(['familyCircle.creator'])
                ->get()
                ->map(function ($child) use ($collaborator) {
                    $owner = $child->familyCircle?->creator;
                    $child->otherParentName = $owner?->name ?? 'Parent';
                    $child->otherParentId = $owner?->id;
                    $child->isCoparentChild = true;
                    return $child;
                });
        }

        $children = $ownChildren->merge($coparentChildren)->unique('id')->sortBy('first_name')->values();

        // Get user's permission for this budget
        $userPermission = $budget->getUserPermission();
        $isSharedBudget = $budget->isSharedWith();
        $budgetOwnerName = $isSharedBudget ? $budget->getOwnerName() : null;

        return view('pages.expenses.transactions.index', [
            'budget' => $budget,
            'transactions' => $transactions,
            'categories' => $categories,
            'children' => $children,
            'filters' => $request->only(['category_id', 'type', 'start_date', 'end_date', 'search']),
            'userPermission' => $userPermission,
            'isSharedBudget' => $isSharedBudget,
            'budgetOwnerName' => $budgetOwnerName,
        ]);
    }

    /**
     * Show create transaction form.
     */
    public function createTransaction()
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        // Check if user has permission to add transactions
        if (!$budget->canUserEdit()) {
            return redirect()->route('expenses.dashboard')
                ->withErrors(['error' => 'You do not have permission to add transactions to this budget.']);
        }

        $categories = $budget->categories()->ordered()->get();

        // Get children for shared expense selection (co-parenting)
        // Include both own children and children accessible via co-parenting
        $user = Auth::user();

        // Get children from current tenant (if user owns them)
        $ownChildren = FamilyMember::forCurrentTenant()
            ->with(['coparents.user'])
            ->where(function ($q) {
                $q->where('is_minor', true)
                    ->orWhere('relationship', 'child')
                    ->orWhere('relationship', 'stepchild');
            })
            ->get();

        // Get children accessible via co-parenting relationship (any collaborator with coparent children)
        $coparentChildren = collect();
        $collaborator = Collaborator::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('coparentChildren') // Has co-parenting children assigned
            ->first();

        if ($collaborator) {
            $coparentChildren = $collaborator->coparentChildren()
                ->with(['familyCircle.creator']) // Load the owner
                ->get()
                ->map(function ($child) use ($collaborator) {
                    // For co-parent, the "other parent" is the family circle owner
                    $owner = $child->familyCircle?->creator;
                    $child->otherParentName = $owner?->name ?? 'Parent';
                    $child->otherParentId = $owner?->id;
                    $child->isCoparentChild = true;
                    return $child;
                });
        }

        // Merge and dedupe by ID
        $children = $ownChildren->merge($coparentChildren)->unique('id')->sortBy('first_name')->values();

        return view('pages.expenses.transactions.create', [
            'budget' => $budget,
            'categories' => $categories,
            'children' => $children,
        ]);
    }

    /**
     * Store new transaction.
     */
    public function storeTransaction(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return back()->withErrors(['error' => 'No active budget found.']);
        }

        $this->authorizeBudgetAccess($budget, 'edit');

        $validated = $request->validate([
            'type' => 'required|in:expense,income,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'payee' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:budget_categories,id',
            'transaction_date' => 'required|date',
            'is_shared' => 'nullable|boolean',
            'shared_for_child_id' => 'nullable|exists:family_members,id',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
            'request_payment' => 'nullable|boolean',
            'split_percentage' => 'nullable|string',
            'custom_split_percentage' => 'nullable|numeric|min:1|max:100',
            'payment_note' => 'nullable|string|max:500',
        ]);

        // Handle receipt upload to Digital Ocean Spaces
        $receiptPath = null;
        $receiptOriginalFilename = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptOriginalFilename = $file->getClientOriginalName();
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $receiptPath = 'receipts/' . Auth::user()->tenant_id . '/' . $filename;

            // Store to Digital Ocean Spaces with public access
            $disk = \Storage::disk('do_spaces');
            $disk->put($receiptPath, file_get_contents($file->getRealPath()));
            $disk->setVisibility($receiptPath, 'public');
        }

        $transaction = BudgetTransaction::create([
            'tenant_id' => Auth::user()->tenant_id,
            'budget_id' => $budget->id,
            'created_by' => Auth::id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'payee' => $validated['payee'],
            'category_id' => $validated['category_id'],
            'transaction_date' => $validated['transaction_date'],
            'source' => 'manual',
            'is_shared' => $request->boolean('is_shared'),
            'shared_for_child_id' => $request->boolean('is_shared') ? ($validated['shared_for_child_id'] ?? null) : null,
            'receipt_path' => $receiptPath,
            'receipt_original_filename' => $receiptOriginalFilename,
        ]);

        // Create payment request if requested - send to ALL co-parents
        if ($request->boolean('request_payment') && $request->boolean('is_shared') && !empty($validated['shared_for_child_id'])) {
            $child = FamilyMember::with(['coparents.user', 'familyCircle.creator'])->find($validated['shared_for_child_id']);
            if ($child) {
                $user = Auth::user();
                $requestedFromUserIds = [];

                // Check if current user is the owner of the child (request from all co-parents)
                if ($child->tenant_id === $user->tenant_id) {
                    // Get all co-parents for this child
                    foreach ($child->coparents as $coparent) {
                        if ($coparent->user && $coparent->user_id !== $user->id) {
                            $requestedFromUserIds[] = $coparent->user_id;
                        }
                    }
                } else {
                    // Current user is a co-parent, request from the owner
                    $owner = $child->familyCircle?->creator;
                    if ($owner && $owner->id !== $user->id) {
                        $requestedFromUserIds[] = $owner->id;
                    }
                }

                if (!empty($requestedFromUserIds)) {
                    // Calculate split percentage
                    $splitPercentage = 50;
                    if ($validated['split_percentage'] === 'custom' && !empty($validated['custom_split_percentage'])) {
                        $splitPercentage = $validated['custom_split_percentage'];
                    }

                    $requestAmount = ($validated['amount'] * $splitPercentage / 100);

                    // Create payment request for each co-parent
                    foreach ($requestedFromUserIds as $requestedFromUserId) {
                        SharedExpensePayment::create([
                            'tenant_id' => $child->tenant_id, // Use child's tenant for visibility
                            'transaction_id' => $transaction->id,
                            'requested_by' => Auth::id(),
                            'requested_from' => $requestedFromUserId,
                            'child_id' => $child->id,
                            'amount' => $requestAmount,
                            'split_percentage' => $splitPercentage,
                            'note' => $validated['payment_note'] ?? null,
                            'status' => SharedExpensePayment::STATUS_PENDING,
                        ]);
                    }
                }
            }
        }

        // Check alerts
        $this->checkBudgetAlerts($budget);

        return back()->with('success', 'Transaction added successfully!');
    }

    /**
     * Update transaction.
     */
    public function updateTransaction(Request $request, BudgetTransaction $transaction)
    {
        $this->authorizeBudgetAccess($transaction->budget, 'edit');

        $validated = $request->validate([
            'type' => 'required|in:expense,income,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'payee' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:budget_categories,id',
            'transaction_date' => 'required|date',
            'is_shared' => 'nullable|boolean',
            'shared_for_child_id' => 'nullable|exists:family_members,id',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ]);

        $updateData = [
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'payee' => $validated['payee'],
            'category_id' => $validated['category_id'],
            'transaction_date' => $validated['transaction_date'],
            'is_shared' => $request->boolean('is_shared'),
            'shared_for_child_id' => $request->boolean('is_shared') ? ($validated['shared_for_child_id'] ?? null) : null,
        ];

        // Handle receipt upload to Digital Ocean Spaces
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($transaction->receipt_path) {
                \Storage::disk('do_spaces')->delete($transaction->receipt_path);
            }
            $file = $request->file('receipt');
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $receiptPath = 'receipts/' . Auth::user()->tenant_id . '/' . $filename;

            $disk = \Storage::disk('do_spaces');
            $disk->put($receiptPath, file_get_contents($file->getRealPath()));
            $disk->setVisibility($receiptPath, 'public');

            $updateData['receipt_path'] = $receiptPath;
            $updateData['receipt_original_filename'] = $file->getClientOriginalName();
        }

        $transaction->update($updateData);

        return back()->with('success', 'Transaction updated successfully!');
    }

    /**
     * Show transaction details.
     */
    public function showTransaction(BudgetTransaction $transaction)
    {
        session(['expenses_mode' => true]);

        $this->authorizeBudgetAccess($transaction->budget, 'view');

        $transaction->load(['category', 'creator', 'sharedForChild']);

        // Get payment request for this transaction that involves the current user
        $user = Auth::user();
        $paymentRequest = SharedExpensePayment::with(['requester', 'payer'])
            ->where('transaction_id', $transaction->id)
            ->where(function ($q) use ($user) {
                $q->where('requested_by', $user->id)
                    ->orWhere('requested_from', $user->id);
            })
            ->first();

        return view('pages.expenses.transactions.show', compact('transaction', 'paymentRequest'));
    }

    /**
     * Delete receipt from transaction.
     */
    public function deleteReceipt(BudgetTransaction $transaction)
    {
        $this->authorizeBudgetAccess($transaction->budget, 'edit');

        if ($transaction->receipt_path) {
            \Storage::disk('do_spaces')->delete($transaction->receipt_path);
            $transaction->update([
                'receipt_path' => null,
                'receipt_original_filename' => null,
            ]);
        }

        return back()->with('success', 'Receipt deleted successfully!');
    }

    /**
     * Delete transaction.
     */
    public function deleteTransaction(BudgetTransaction $transaction)
    {
        $this->authorizeBudgetAccess($transaction->budget, 'edit');

        $transaction->delete();

        return redirect()->route('expenses.transactions')->with('success', 'Transaction deleted successfully!');
    }

    // ==================== CSV IMPORT ====================

    /**
     * Show import form.
     */
    public function importForm()
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $categories = $budget->categories()->ordered()->get();

        return view('pages.expenses.import.upload', [
            'budget' => $budget,
            'categories' => $categories,
        ]);
    }

    /**
     * Upload CSV and show preview.
     */
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->store('temp');

        // Parse CSV
        $rows = [];
        $headers = [];

        if (($handle = fopen(storage_path('app/' . $path), 'r')) !== false) {
            $lineNum = 0;
            while (($data = fgetcsv($handle)) !== false && $lineNum < 10) {
                if ($lineNum === 0) {
                    $headers = $data;
                } else {
                    $rows[] = $data;
                }
                $lineNum++;
            }
            fclose($handle);
        }

        // Store path in session
        session(['csv_import_path' => $path]);

        $budget = $this->getSelectedBudget();
        $categories = $budget->categories()->ordered()->get();

        return view('pages.expenses.import.map', [
            'headers' => $headers,
            'rows' => $rows,
            'categories' => $categories,
        ]);
    }

    /**
     * Map columns and preview.
     */
    public function mapColumns(Request $request)
    {
        $validated = $request->validate([
            'date_column' => 'required|integer|min:0',
            'description_column' => 'required|integer|min:0',
            'amount_column' => 'required|integer|min:0',
            'category_column' => 'nullable|integer|min:0',
            'default_category_id' => 'nullable|exists:budget_categories,id',
        ]);

        session(['csv_mapping' => $validated]);

        // Parse and preview
        $path = session('csv_import_path');
        $budget = $this->getSelectedBudget();

        $transactions = [];
        $duplicates = 0;

        if (($handle = fopen(storage_path('app/' . $path), 'r')) !== false) {
            $lineNum = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if ($lineNum > 0 && $lineNum <= 50) { // Skip header, limit preview
                    $transactionData = [
                        'transaction_date' => $data[$validated['date_column']] ?? '',
                        'description' => $data[$validated['description_column']] ?? '',
                        'amount' => abs((float) preg_replace('/[^0-9.-]/', '', $data[$validated['amount_column']] ?? '0')),
                        'category_id' => $validated['default_category_id'],
                    ];

                    $importRef = BudgetTransaction::generateImportReference($transactionData);

                    if (BudgetTransaction::isDuplicate($budget->id, $importRef)) {
                        $duplicates++;
                        $transactionData['is_duplicate'] = true;
                    }

                    $transactions[] = $transactionData;
                }
                $lineNum++;
            }
            fclose($handle);
        }

        return view('pages.expenses.import.review', [
            'transactions' => $transactions,
            'duplicates' => $duplicates,
            'total' => count($transactions),
        ]);
    }

    /**
     * Process import.
     */
    public function processImport(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $this->authorizeBudgetAccess($budget, 'edit');

        $path = session('csv_import_path');
        $mapping = session('csv_mapping');

        $imported = 0;
        $skipped = 0;

        if (($handle = fopen(storage_path('app/' . $path), 'r')) !== false) {
            $lineNum = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if ($lineNum > 0) {
                    $transactionData = [
                        'transaction_date' => $data[$mapping['date_column']] ?? '',
                        'description' => $data[$mapping['description_column']] ?? '',
                        'amount' => abs((float) preg_replace('/[^0-9.-]/', '', $data[$mapping['amount_column']] ?? '0')),
                    ];

                    $importRef = BudgetTransaction::generateImportReference($transactionData);

                    if (!BudgetTransaction::isDuplicate($budget->id, $importRef)) {
                        BudgetTransaction::create([
                            'tenant_id' => Auth::user()->tenant_id,
                            'budget_id' => $budget->id,
                            'created_by' => Auth::id(),
                            'type' => 'expense',
                            'amount' => $transactionData['amount'],
                            'description' => $transactionData['description'],
                            'transaction_date' => Carbon::parse($transactionData['transaction_date']),
                            'category_id' => $mapping['default_category_id'] ?? null,
                            'source' => 'csv_import',
                            'import_reference' => $importRef,
                        ]);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
                $lineNum++;
            }
            fclose($handle);
        }

        // Clean up
        session()->forget(['csv_import_path', 'csv_mapping']);

        return redirect()->route('expenses.transactions')
            ->with('success', "Imported {$imported} transactions. Skipped {$skipped} duplicates.");
    }

    // ==================== SHARING ====================

    /**
     * Show sharing options.
     */
    public function shareForm()
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $this->authorizeBudgetAccess($budget, 'admin');

        $shares = $budget->shares()->with('collaborator.user')->get();
        $sharedCollaboratorIds = $shares->pluck('collaborator_id')->toArray();

        $collaborators = Collaborator::forCurrentTenant()
            ->where('is_active', true)
            ->whereNotIn('id', $sharedCollaboratorIds)
            ->get();

        // Get the user IDs of collaborators who already have access
        $sharedUserIds = $shares->pluck('collaborator.user_id')->filter()->toArray();

        // Get all family circles with their members who could potentially be shared with
        // Only include adults (non-minors) who have a linked user account
        // Exclude those whose linked user already has access
        $familyCircles = \App\Models\FamilyCircle::forCurrentTenant()
            ->with(['members' => function ($query) use ($sharedUserIds) {
                $query->where('is_minor', false)
                    ->whereNotNull('linked_user_id')
                    ->where('linked_user_id', '!=', auth()->id())
                    ->whereNotIn('linked_user_id', $sharedUserIds);
            }])
            ->get();

        return view('pages.expenses.share', [
            'budget' => $budget,
            'shares' => $shares,
            'collaborators' => $collaborators,
            'familyCircles' => $familyCircles,
            'permissions' => BudgetShare::PERMISSIONS,
        ]);
    }

    /**
     * Share budget with collaborator, family member, or by email.
     */
    public function shareWith(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return back()->withErrors(['error' => 'No budget selected.']);
        }

        $this->authorizeBudgetAccess($budget, 'admin');

        // Validate - either email or share_with is required
        $request->validate([
            'permission' => 'required|in:view,edit,admin',
        ]);

        $collaboratorId = null;

        // Handle email-based sharing
        if ($request->filled('email')) {
            $email = strtolower(trim($request->email));

            // Find the user by email
            $user = User::findByEmail($email);

            if (!$user) {
                return back()->withErrors(['error' => 'No user found with that email address. They need to create an account first.']);
            }

            // Can't share with yourself
            if ($user->id === Auth::id()) {
                return back()->withErrors(['error' => 'You cannot share a budget with yourself.']);
            }

            // Check if a collaborator already exists for this user in current tenant
            $collaborator = Collaborator::forCurrentTenant()
                ->where('user_id', $user->id)
                ->first();

            if (!$collaborator) {
                // Create a new collaborator for this user
                $collaborator = Collaborator::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'user_id' => $user->id,
                    'invited_by' => Auth::id(),
                    'relationship_type' => 'spouse',
                    'role' => 'contributor',
                    'is_active' => true,
                ]);
            }

            $collaboratorId = $collaborator->id;

        } elseif ($request->filled('share_with')) {
            // Handle dropdown-based sharing
            [$type, $id] = explode(':', $request->share_with);

            if ($type === 'collaborator') {
                // Direct collaborator share
                $collaborator = Collaborator::forCurrentTenant()->findOrFail($id);
                $collaboratorId = $collaborator->id;
            } elseif ($type === 'family_member') {
                // Family member - find or create collaborator
                $familyMember = FamilyMember::forCurrentTenant()->findOrFail($id);

                // Family member must have a linked user account to share with
                if (!$familyMember->linked_user_id) {
                    return back()->withErrors(['error' => 'This family member does not have a linked user account. They need to be invited as a collaborator first.']);
                }

                // Check if a collaborator already exists for this user
                $collaborator = Collaborator::forCurrentTenant()
                    ->where('user_id', $familyMember->linked_user_id)
                    ->first();

                if (!$collaborator) {
                    // Create a new collaborator for this family member
                    $collaborator = Collaborator::create([
                        'tenant_id' => Auth::user()->tenant_id,
                        'user_id' => $familyMember->linked_user_id,
                        'invited_by' => Auth::id(),
                        'relationship_type' => 'relative',
                        'role' => 'contributor',
                        'is_active' => true,
                    ]);
                }

                $collaboratorId = $collaborator->id;
            } else {
                return back()->withErrors(['error' => 'Invalid share type.']);
            }
        } else {
            return back()->withErrors(['error' => 'Please provide an email address or select a family member.']);
        }

        BudgetShare::updateOrCreate(
            [
                'budget_id' => $budget->id,
                'collaborator_id' => $collaboratorId,
            ],
            [
                'permission' => $request->permission,
            ]
        );

        return back()->with('success', 'Budget shared successfully!');
    }

    /**
     * Remove share.
     */
    public function removeShare(BudgetShare $share)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return back()->withErrors(['error' => 'No budget selected.']);
        }

        $this->authorizeBudgetAccess($budget, 'admin');

        $share->delete();

        return back()->with('success', 'Access removed successfully!');
    }

    // ==================== DASHBOARD & REPORTS ====================

    /**
     * Show expenses dashboard.
     */
    public function dashboard(Request $request)
    {
        session(['expenses_mode' => true]);

        // Get all budgets accessible by the current user (owned or shared, including cross-tenant)
        $allBudgets = Budget::accessibleByUser()->active()->orderBy('name')->get();

        if ($allBudgets->isEmpty()) {
            return redirect()->route('expenses.intro');
        }

        // Get selected budget (from request, session, or default to first)
        // 'all' means show all budgets combined
        $budgetId = $request->get('budget_id') ?? session('selected_budget_id');
        $showAllBudgets = $budgetId === 'all';

        if ($showAllBudgets) {
            $budget = null;
            session(['selected_budget_id' => 'all']);
        } else {
            $budget = $budgetId
                ? $allBudgets->firstWhere('id', $budgetId)
                : $allBudgets->first();

            // Fallback to first budget if selected doesn't exist
            if (!$budget) {
                $budget = $allBudgets->first();
            }

            // Store selected budget in session
            session(['selected_budget_id' => $budget->id]);
        }

        // Get period offset (0 = current, -1 = previous, etc.)
        $periodOffset = (int) $request->get('period', 0);

        // Calculate period dates and available periods
        $periodDates = null;
        $availablePeriods = [];
        $currentPeriodLabel = null;

        if (!$showAllBudgets && $budget) {
            $periodDates = $budget->getPeriodDates($periodOffset);
            $availablePeriods = $budget->getAvailablePeriods();
            $currentPeriodLabel = $periodDates['label'];
        }

        // Calculate stats (filtered by period for single budget)
        if ($showAllBudgets) {
            $totalBudget = $allBudgets->sum('total_amount');
            $totalSpent = $allBudgets->sum(fn($b) => $b->getTotalSpentForPeriod());
            $remaining = $totalBudget - $totalSpent;
            $progress = $totalBudget > 0 ? min(100, round(($totalSpent / $totalBudget) * 100, 1)) : 0;
            $categorySpending = []; // No category breakdown for all budgets
        } else {
            $totalBudget = $budget->total_amount;
            $totalSpent = $budget->getTotalSpentForPeriod($periodDates['start'], $periodDates['end']);
            $remaining = $budget->getRemainingAmountForPeriod($periodDates['start'], $periodDates['end']);
            $progress = $budget->getProgressPercentageForPeriod($periodDates['start'], $periodDates['end']);
            $categorySpending = $budget->getSpendingByCategoryForPeriod($periodDates['start'], $periodDates['end']);
        }

        // Get filter parameters
        $sharedFilter = $request->get('shared_filter', 'all'); // all, shared, member_id

        // Recent transactions with filter (filtered by period)
        if ($showAllBudgets) {
            $transactionsQuery = BudgetTransaction::whereIn('budget_id', $allBudgets->pluck('id'))
                ->with(['category', 'creator', 'budget', 'sharedForChild']);
        } else {
            $transactionsQuery = $budget->transactions()
                ->with(['category', 'creator', 'sharedForChild'])
                ->whereBetween('transaction_date', [$periodDates['start'], $periodDates['end']]);
        }

        // Apply shared expense filter
        if ($sharedFilter === 'shared') {
            $transactionsQuery->where('is_shared', true);
        } elseif (is_numeric($sharedFilter)) {
            // Filter by specific child
            $transactionsQuery->where('is_shared', true)
                ->where('shared_for_child_id', (int) $sharedFilter);
        }

        $recentTransactions = $transactionsQuery->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = $showAllBudgets ? $this->getMonthlyTrendAllBudgets($allBudgets) : $this->getMonthlyTrend($budget);

        // Active alerts
        if ($showAllBudgets) {
            $triggeredAlerts = collect();
            $categories = collect();
        } else {
            $alerts = $budget->alerts()->active()->get();
            $triggeredAlerts = $alerts->filter(fn($alert) => $alert->shouldTrigger());
            $categories = $budget->categories()->ordered()->get();
        }

        // Get all categories from all budgets for the modal when showing all budgets
        if ($showAllBudgets) {
            $categories = BudgetCategory::whereIn('budget_id', $allBudgets->pluck('id'))
                ->orderBy('name')
                ->get();
        }

        // Get goals for traditional budgets with calculated current amounts
        $goals = collect();
        if (!$showAllBudgets && $budget && $budget->is_traditional) {
            $goals = $budget->goals()->active()->ordered()->get();

            // Calculate current amount for each goal based on period transactions
            foreach ($goals as $goal) {
                $goal->calculated_current = $goal->calculateCurrentFromTransactions(
                    $periodDates['start'],
                    $periodDates['end']
                );
            }
        }

        // Get children for shared expense selection (co-parenting)
        $children = FamilyMember::forCurrentTenant()
            ->where(function ($q) {
                $q->where('is_minor', true)
                    ->orWhere('relationship', 'child')
                    ->orWhere('relationship', 'stepchild');
            })
            ->orderBy('first_name')
            ->get();

        // Determine user's permission for the current budget
        $userPermission = 'owner';
        $isSharedBudget = false;
        $budgetOwnerName = null;

        if (!$showAllBudgets && $budget) {
            $userPermission = $budget->getUserPermission();
            $isSharedBudget = $budget->isSharedWith();
            if ($isSharedBudget) {
                $budgetOwnerName = $budget->getOwnerName();
            }
        }

        // Add share info to all budgets for dropdown display
        foreach ($allBudgets as $b) {
            $b->is_shared_with_me = $b->isSharedWith();
            $b->owner_name = $b->is_shared_with_me ? $b->getOwnerName() : null;
        }

        return view('pages.expenses.dashboard', [
            'budget' => $budget,
            'allBudgets' => $allBudgets,
            'showAllBudgets' => $showAllBudgets,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'remaining' => $remaining,
            'progress' => $progress,
            'categorySpending' => $categorySpending,
            'recentTransactions' => $recentTransactions,
            'monthlyTrend' => $monthlyTrend,
            'triggeredAlerts' => $triggeredAlerts,
            'categories' => $categories,
            'children' => $children,
            'sharedFilter' => $sharedFilter,
            // Period data
            'periodOffset' => $periodOffset,
            'periodDates' => $periodDates,
            'availablePeriods' => $availablePeriods,
            'currentPeriodLabel' => $currentPeriodLabel,
            // Goals for traditional budgets
            'goals' => $goals,
            // Sharing info
            'userPermission' => $userPermission,
            'isSharedBudget' => $isSharedBudget,
            'budgetOwnerName' => $budgetOwnerName,
        ]);
    }

    /**
     * Get monthly spending trend.
     */
    protected function getMonthlyTrend(Budget $budget): array
    {
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $spent = $budget->transactions()
                ->expenses()
                ->forDateRange($startOfMonth, $endOfMonth)
                ->sum('amount');

            $trend[] = [
                'month' => $date->format('M'),
                'amount' => (float) $spent,
            ];
        }

        return $trend;
    }

    /**
     * Get monthly spending trend for all budgets combined.
     */
    protected function getMonthlyTrendAllBudgets($budgets): array
    {
        $trend = [];
        $budgetIds = $budgets->pluck('id');

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $spent = BudgetTransaction::whereIn('budget_id', $budgetIds)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $trend[] = [
                'month' => $date->format('M'),
                'amount' => (float) $spent,
            ];
        }

        return $trend;
    }

    /**
     * Show reports page.
     */
    public function reports(Request $request)
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        // Date range
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Category breakdown for period
        $categoryBreakdown = DB::table('budget_transactions')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->where('budget_id', $budget->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->get();

        // Daily spending
        $dailySpending = DB::table('budget_transactions')
            ->select('transaction_date', DB::raw('SUM(amount) as total'))
            ->where('budget_id', $budget->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $categories = $budget->categories()->ordered()->get()->keyBy('id');

        return view('pages.expenses.reports', [
            'budget' => $budget,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryBreakdown' => $categoryBreakdown,
            'dailySpending' => $dailySpending,
            'categories' => $categories,
        ]);
    }

    /**
     * Export report.
     */
    public function exportReport(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $transactions = $budget->transactions()
            ->with('category')
            ->forDateRange($startDate, $endDate)
            ->orderBy('transaction_date')
            ->get();

        $filename = "expenses_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Date', 'Description', 'Category', 'Type', 'Amount']);

            // Data
            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->description,
                    $t->category?->name ?? 'Uncategorized',
                    $t->type,
                    $t->amount,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== ALERTS ====================

    /**
     * Show alerts management.
     */
    public function alerts()
    {
        session(['expenses_mode' => true]);

        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return redirect()->route('expenses.intro');
        }

        $alerts = $budget->alerts()->with('category')->get();
        $categories = $budget->categories()->ordered()->get();

        return view('pages.expenses.alerts', [
            'budget' => $budget,
            'alerts' => $alerts,
            'categories' => $categories,
            'alertTypes' => BudgetAlert::TYPES,
            'commonThresholds' => BudgetAlert::COMMON_THRESHOLDS,
        ]);
    }

    /**
     * Create new alert.
     */
    public function storeAlert(Request $request)
    {
        $budget = $this->getSelectedBudget();

        if (!$budget) {
            return back()->withErrors(['error' => 'No active budget found.']);
        }

        $this->authorizeBudgetAccess($budget, 'admin');

        $validated = $request->validate([
            'category_id' => 'nullable|exists:budget_categories,id',
            'type' => 'required|in:percentage,amount',
            'threshold' => 'required|numeric|min:0.01',
        ]);

        BudgetAlert::create([
            'budget_id' => $budget->id,
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'threshold' => $validated['threshold'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Alert created successfully!');
    }

    /**
     * Delete alert.
     */
    public function deleteAlert(BudgetAlert $alert)
    {
        $this->authorizeBudgetAccess($alert->budget, 'admin');

        $alert->delete();

        return back()->with('success', 'Alert deleted successfully!');
    }

    /**
     * Check and trigger budget alerts.
     */
    protected function checkBudgetAlerts(Budget $budget): void
    {
        $alerts = $budget->alerts()->active()->get();

        foreach ($alerts as $alert) {
            $alert->checkAndTrigger();
        }
    }

    // ==================== MODE TOGGLE ====================

    /**
     * Enter expenses mode.
     */
    public function enterMode()
    {
        session(['expenses_mode' => true]);

        return redirect()->route('expenses.index');
    }

    /**
     * Exit expenses mode.
     */
    public function exitMode()
    {
        session()->forget('expenses_mode');

        return redirect()->route('dashboard');
    }

    // ==================== AUTHORIZATION ====================

    /**
     * Get the currently selected budget from session or default to first.
     */
    protected function getSelectedBudget(): ?Budget
    {
        $budgetId = session('selected_budget_id');

        if ($budgetId) {
            // Check if user has access to this budget (owned or shared, including cross-tenant)
            $budget = Budget::accessibleByUser()->active()->find($budgetId);
            if ($budget) {
                return $budget;
            }
        }

        // Fallback to first accessible active budget
        $budget = Budget::accessibleByUser()->active()->first();

        if ($budget) {
            session(['selected_budget_id' => $budget->id]);
        }

        return $budget;
    }

    /**
     * Authorize budget access.
     */
    protected function authorizeBudgetAccess(Budget $budget, string $requiredPermission = 'view'): void
    {
        $user = Auth::user();

        // Owner has full access
        if ($budget->created_by === $user->id) {
            return;
        }

        // Check shared access - look across ALL tenants since shared budgets may be from other tenants
        // Get all collaborator IDs for this user (they may have collaborator records in multiple tenants)
        $collaboratorIds = Collaborator::where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($collaboratorIds)) {
            abort(403, 'You do not have access to this budget.');
        }

        // Check if any of the user's collaborator records have access to this budget
        $share = $budget->shares()
            ->whereIn('collaborator_id', $collaboratorIds)
            ->first();

        if (!$share) {
            abort(403, 'You do not have access to this budget.');
        }

        // Check permission level
        $permissionHierarchy = ['view' => 1, 'edit' => 2, 'admin' => 3];
        $hasLevel = $permissionHierarchy[$share->permission] ?? 0;
        $needLevel = $permissionHierarchy[$requiredPermission] ?? 0;

        if ($hasLevel < $needLevel) {
            abort(403, 'You do not have sufficient permissions for this action.');
        }
    }

    // ==================== PAYMENT REQUESTS ====================

    /**
     * Show payment requests list.
     */
    public function paymentRequests()
    {
        session(['expenses_mode' => true]);

        $user = Auth::user();

        // Get requests sent to the current user (pending payments)
        $pendingRequests = SharedExpensePayment::with(['transaction', 'requester', 'child'])
            ->where('requested_from', $user->id)
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        // Get requests the user has sent
        $sentRequests = SharedExpensePayment::with(['transaction', 'payer', 'child'])
            ->where('requested_by', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Get history of received requests
        $receivedHistory = SharedExpensePayment::with(['transaction', 'requester', 'child'])
            ->where('requested_from', $user->id)
            ->whereIn('status', ['paid', 'declined'])
            ->orderByDesc('responded_at')
            ->limit(20)
            ->get();

        return view('pages.expenses.payment-requests.index', compact('pendingRequests', 'sentRequests', 'receivedHistory'));
    }

    /**
     * Show single payment request.
     */
    public function showPaymentRequest(SharedExpensePayment $payment)
    {
        session(['expenses_mode' => true]);

        $user = Auth::user();

        // Check access - only requester or payer can view
        if ($payment->requested_by !== $user->id && $payment->requested_from !== $user->id) {
            abort(403, 'You do not have access to this payment request.');
        }

        $payment->load(['transaction.category', 'requester', 'payer', 'child']);

        return view('pages.expenses.payment-requests.show', compact('payment'));
    }

    /**
     * Submit payment for a request.
     */
    public function submitPayment(Request $request, SharedExpensePayment $payment)
    {
        $user = Auth::user();

        // Only the payer can submit payment
        if ($payment->requested_from !== $user->id) {
            abort(403, 'You cannot submit payment for this request.');
        }

        if (!$payment->isPending()) {
            return back()->withErrors(['error' => 'This payment request is no longer pending.']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:' . implode(',', array_keys(SharedExpensePayment::PAYMENT_METHODS)),
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
            'response_note' => 'nullable|string|max:500',
        ]);

        // Handle receipt upload
        $receiptPath = null;
        $receiptFilename = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptPath = $file->store('payment-receipts/' . $user->tenant_id, 'public');
            $receiptFilename = $file->getClientOriginalName();
        }

        $payment->markAsPaid(
            $validated['payment_method'],
            $receiptPath,
            $receiptFilename,
            $validated['response_note'] ?? null
        );

        return redirect()->route('expenses.payment-requests')->with('success', 'Payment submitted successfully!');
    }

    /**
     * Decline a payment request.
     */
    public function declinePayment(Request $request, SharedExpensePayment $payment)
    {
        $user = Auth::user();

        // Only the payer can decline
        if ($payment->requested_from !== $user->id) {
            abort(403, 'You cannot decline this payment request.');
        }

        if (!$payment->isPending()) {
            return back()->withErrors(['error' => 'This payment request is no longer pending.']);
        }

        $validated = $request->validate([
            'response_note' => 'nullable|string|max:500',
        ]);

        $payment->markAsDeclined($validated['response_note'] ?? null);

        return redirect()->route('expenses.payment-requests')->with('success', 'Payment request declined.');
    }

    /**
     * Cancel a payment request (by the requester).
     */
    public function cancelPaymentRequest(SharedExpensePayment $payment)
    {
        $user = Auth::user();

        // Only the requester can cancel
        if ($payment->requested_by !== $user->id) {
            abort(403, 'You cannot cancel this payment request.');
        }

        if (!$payment->isPending()) {
            return back()->withErrors(['error' => 'This payment request is no longer pending.']);
        }

        $payment->cancel();

        return back()->with('success', 'Payment request cancelled.');
    }
}
