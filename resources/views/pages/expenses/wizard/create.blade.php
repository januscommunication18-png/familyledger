@extends('layouts.dashboard')

@section('page-name', 'Create Budget')

@section('content')
<div class="p-4 lg:p-6 max-w-3xl mx-auto">
    {{-- Progress Steps --}}
    <div class="mb-8">
        <ul class="steps steps-horizontal w-full text-xs sm:text-sm">
            <li class="step {{ $step >= 1 ? 'step-primary' : '' }}">Type</li>
            <li class="step {{ $step >= 2 ? 'step-primary' : '' }}">Details</li>
            <li class="step {{ $step >= 3 ? 'step-primary' : '' }}">Amount</li>
            <li class="step {{ $step >= 4 ? 'step-primary' : '' }}">Categories</li>
            <li class="step {{ $step >= 5 ? 'step-primary' : '' }}">Share</li>
            <li class="step {{ $step >= 6 ? 'step-primary' : '' }}">Review</li>
        </ul>
    </div>

    {{-- Step 1: Budget Type --}}
    @if($step === 1)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Choose Your Budgeting Method</h2>
            <p class="text-slate-500 mb-6">Select how you want to manage your money.</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="1">

                <div class="grid grid-cols-1 gap-4 mb-6">
                    @foreach($budgetTypes as $type => $info)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $type }}" class="peer hidden" {{ ($wizardData['type'] ?? 'envelope') === $type ? 'checked' : '' }}>
                        <div class="card border-2 peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                            <div class="card-body py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl {{ $type === 'envelope' ? 'bg-emerald-100' : 'bg-blue-100' }} flex items-center justify-center shrink-0">
                                        @if($type === 'envelope')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(59 130 246)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-800">{{ $info['label'] }}</h3>
                                        <p class="text-sm text-slate-500">{{ $info['description'] }}</p>
                                    </div>
                                    @if($type === 'envelope')
                                    <span class="badge badge-success badge-sm">Recommended</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Step 2: Budget Details --}}
    @if($step === 2)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Budget Details</h2>
            <p class="text-slate-500 mb-6">Name your budget and set the time period.</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="2">

                <div class="space-y-4 mb-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Budget Name</span>
                        </label>
                        <input type="text" name="name" class="input input-bordered" placeholder="e.g., Family Budget, Monthly Expenses" value="{{ $wizardData['name'] ?? '' }}" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Budget Period</span>
                        </label>
                        <select name="period" class="select select-bordered" required>
                            @foreach($periods as $value => $label)
                            <option value="{{ $value }}" {{ ($wizardData['period'] ?? 'monthly') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-slate-500">How often do you want to reset your budget?</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Start Date</span>
                        </label>
                        <input type="date" name="start_date" class="input input-bordered" value="{{ $wizardData['start_date'] ?? now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('expenses.budget.create', ['step' => 1]) }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Step 3: Total Budget Amount --}}
    @if($step === 3)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Set Your Budget Amount</h2>
            <p class="text-slate-500 mb-6">How much do you want to budget for each {{ $wizardData['period'] ?? 'month' }}?</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="3">

                <div class="space-y-4 mb-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Total Budget Amount</span>
                        </label>
                        <label class="input-group">
                            <span class="bg-base-200">$</span>
                            <input type="number" name="total_amount" step="0.01" min="0" class="input input-bordered flex-1" placeholder="5000.00" value="{{ $wizardData['total_amount'] ?? '' }}" required>
                        </label>
                        <label class="label">
                            <span class="label-text-alt text-slate-500">This is the total amount you'll allocate across all categories.</span>
                        </label>
                    </div>

                    {{-- Quick Amount Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setAmount(1000)" class="btn btn-sm btn-outline">$1,000</button>
                        <button type="button" onclick="setAmount(2500)" class="btn btn-sm btn-outline">$2,500</button>
                        <button type="button" onclick="setAmount(5000)" class="btn btn-sm btn-outline">$5,000</button>
                        <button type="button" onclick="setAmount(7500)" class="btn btn-sm btn-outline">$7,500</button>
                        <button type="button" onclick="setAmount(10000)" class="btn btn-sm btn-outline">$10,000</button>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('expenses.budget.create', ['step' => 2]) }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function setAmount(amount) {
        document.querySelector('input[name="total_amount"]').value = amount.toFixed(2);
    }
    </script>
    @endif

    {{-- Step 4: Categories --}}
    @if($step === 4)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Set Up Categories</h2>
            <p class="text-slate-500 mb-2">Create spending categories and allocate your budget.</p>
            <p class="text-sm text-emerald-600 font-medium mb-6">Total Budget: ${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST" id="categoriesForm">
                @csrf
                <input type="hidden" name="step" value="4">

                <div id="categoriesContainer" class="space-y-3 mb-4">
                    @php
                        $existingCategories = $wizardData['categories'] ?? $defaultCategories;
                    @endphp
                    @foreach($existingCategories as $index => $cat)
                    <div class="category-row flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                        <input type="text" name="categories[{{ $index }}][icon]" value="{{ $cat['icon'] ?? '📦' }}" class="input input-bordered input-sm w-14 text-center">
                        <input type="text" name="categories[{{ $index }}][name]" value="{{ $cat['name'] ?? '' }}" class="input input-bordered input-sm flex-1" placeholder="Category name" required>
                        <label class="input-group input-group-sm">
                            <span class="bg-base-300 text-xs">$</span>
                            <input type="number" name="categories[{{ $index }}][allocated_amount]" value="{{ $cat['allocated_amount'] ?? 0 }}" step="0.01" min="0" class="input input-bordered input-sm w-28 category-amount" placeholder="0.00" required onchange="updateTotal()">
                        </label>
                        <input type="hidden" name="categories[{{ $index }}][color]" value="{{ $cat['color'] ?? '#6b7280' }}">
                        <button type="button" onclick="removeCategory(this)" class="btn btn-ghost btn-sm btn-square text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addCategory()" class="btn btn-ghost btn-sm gap-1 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add Category
                </button>

                {{-- Allocation Summary --}}
                <div class="bg-base-200 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-slate-600">Total Allocated:</span>
                        <span id="totalAllocated" class="font-semibold">$0.00</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-slate-600">Budget Amount:</span>
                        <span class="font-semibold">${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</span>
                    </div>
                    <hr class="my-2 border-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Remaining:</span>
                        <span id="remaining" class="font-semibold text-emerald-600">$0.00</span>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('expenses.budget.create', ['step' => 3]) }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const budgetAmount = {{ $wizardData['total_amount'] ?? 0 }};
    let categoryIndex = {{ count($existingCategories) }};

    const defaultIcons = ['🏠', '💡', '🛒', '🚗', '🏥', '🎬', '🍽️', '🛍️', '💰', '📦'];
    const defaultColors = ['#ef4444', '#f97316', '#22c55e', '#3b82f6', '#ec4899', '#8b5cf6', '#f59e0b', '#06b6d4', '#10b981', '#6b7280'];

    function addCategory() {
        const container = document.getElementById('categoriesContainer');
        const icon = defaultIcons[categoryIndex % defaultIcons.length];
        const color = defaultColors[categoryIndex % defaultColors.length];

        const html = `
            <div class="category-row flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                <input type="text" name="categories[${categoryIndex}][icon]" value="${icon}" class="input input-bordered input-sm w-14 text-center">
                <input type="text" name="categories[${categoryIndex}][name]" class="input input-bordered input-sm flex-1" placeholder="Category name" required>
                <label class="input-group input-group-sm">
                    <span class="bg-base-300 text-xs">$</span>
                    <input type="number" name="categories[${categoryIndex}][allocated_amount]" value="0" step="0.01" min="0" class="input input-bordered input-sm w-28 category-amount" placeholder="0.00" required onchange="updateTotal()">
                </label>
                <input type="hidden" name="categories[${categoryIndex}][color]" value="${color}">
                <button type="button" onclick="removeCategory(this)" class="btn btn-ghost btn-sm btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        categoryIndex++;
        updateTotal();
    }

    function removeCategory(btn) {
        const rows = document.querySelectorAll('.category-row');
        if (rows.length > 1) {
            btn.closest('.category-row').remove();
            updateTotal();
        }
    }

    function updateTotal() {
        const amounts = document.querySelectorAll('.category-amount');
        let total = 0;
        amounts.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        document.getElementById('totalAllocated').textContent = '$' + total.toFixed(2);

        const remaining = budgetAmount - total;
        const remainingEl = document.getElementById('remaining');
        remainingEl.textContent = '$' + remaining.toFixed(2);
        remainingEl.className = remaining >= 0 ? 'font-semibold text-emerald-600' : 'font-semibold text-error';
    }

    // Initial calculation
    updateTotal();
    </script>
    @endif

    {{-- Step 5: Share with Family --}}
    @if($step === 5)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Share with Family</h2>
            <p class="text-slate-500 mb-6">Share this budget with family members so they can view or contribute to tracking expenses together.</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="5">

                {{-- Family Circles --}}
                @php
                    $hasMembers = $familyCircles->sum(fn($c) => $c->members->count()) > 0;
                @endphp

                @if($familyCircles->count() > 0 && $hasMembers)
                    {{-- Select All Checkbox --}}
                    <div class="form-control mb-4">
                        <label class="label cursor-pointer justify-start gap-3 p-2 rounded bg-base-200 hover:bg-base-300">
                            <input type="checkbox" id="selectAll" class="checkbox checkbox-primary checkbox-sm" onchange="toggleSelectAll()">
                            <span class="label-text font-medium">Select All Members from All Circles</span>
                        </label>
                    </div>

                    {{-- Loop through each family circle --}}
                    <div class="space-y-4 mb-6">
                        @foreach($familyCircles as $circle)
                            @if($circle->members->count() > 0)
                            <div class="bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-200 rounded-lg p-4">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">{{ $circle->name }}</h3>
                                        <p class="text-xs text-slate-500">{{ $circle->members->count() }} member(s) with accounts</p>
                                    </div>
                                </div>

                                {{-- Family Members List --}}
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($circle->members as $member)
                                    <label class="flex items-center gap-3 p-3 rounded-lg bg-white border border-slate-200 cursor-pointer hover:border-violet-300 transition-colors">
                                        <input type="checkbox" name="share_with_members[]" value="{{ $member->id }}" class="checkbox checkbox-primary checkbox-sm member-checkbox" {{ in_array($member->id, $wizardData['share_with_members'] ?? []) ? 'checked' : '' }}>
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center shrink-0">
                                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($member->first_name ?? $member->full_name, 0, 1)) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-slate-800 truncate">{{ $member->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $member->relationship_name }}</p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Permission Level --}}
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Permission Level for Selected Members</span>
                        </label>
                        <select name="share_permission" class="select select-bordered select-sm">
                            <option value="view">View Only - Can view budget and transactions</option>
                            <option value="edit" selected>Editor - Can add and edit transactions</option>
                            <option value="admin">Admin - Full access including settings</option>
                        </select>
                    </div>
                @else
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 text-center mb-6">
                    <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <p class="text-slate-600 mb-2">No family members with accounts found</p>
                    <p class="text-sm text-slate-500">You can share this budget later from the budget settings once family members create their accounts.</p>
                </div>
                @endif

                {{-- Recommendation Banner --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
                    <div class="flex gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        <div>
                            <p class="font-medium text-emerald-800">Recommended</p>
                            <p class="text-sm text-emerald-700">Sharing your budget helps family members stay on the same page about spending and savings goals.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('expenses.budget.create', ['step' => 4]) }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </a>
                    <div class="flex gap-2">
                        <a href="{{ route('expenses.budget.create', ['step' => 6, 'skip_share' => 1]) }}" class="btn btn-ghost">
                            Skip for Later
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Continue
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.member-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    // Update select all state based on individual checkboxes
    document.querySelectorAll('.member-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const all = document.querySelectorAll('.member-checkbox');
            const checked = document.querySelectorAll('.member-checkbox:checked');
            document.getElementById('selectAll').checked = all.length === checked.length;
        });
    });
    </script>
    @endif

    {{-- Step 6: Review --}}
    @if($step === 6)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Review Your Budget</h2>
            <p class="text-slate-500 mb-6">Make sure everything looks good before creating your budget.</p>

            <div class="space-y-4 mb-6">
                {{-- Budget Info --}}
                <div class="bg-base-200 rounded-lg p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Budget Details</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-slate-500">Name:</span>
                            <span class="ml-2 font-medium">{{ $wizardData['name'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Type:</span>
                            <span class="ml-2 font-medium capitalize">{{ $wizardData['type'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Period:</span>
                            <span class="ml-2 font-medium capitalize">{{ $wizardData['period'] ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Start Date:</span>
                            <span class="ml-2 font-medium">{{ isset($wizardData['start_date']) ? \Carbon\Carbon::parse($wizardData['start_date'])->format('M j, Y') : 'N/A' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-500">Total Budget:</span>
                            <span class="ml-2 font-bold text-lg text-emerald-600">${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Categories --}}
                <div class="bg-base-200 rounded-lg p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Categories ({{ count($wizardData['categories'] ?? []) }})</h3>
                    <div class="space-y-2">
                        @php $totalAllocated = 0; @endphp
                        @foreach(($wizardData['categories'] ?? []) as $cat)
                        @php $totalAllocated += $cat['allocated_amount'] ?? 0; @endphp
                        <div class="flex items-center justify-between text-sm">
                            <span>
                                <span class="mr-2">{{ $cat['icon'] ?? '📦' }}</span>
                                {{ $cat['name'] }}
                            </span>
                            <span class="font-medium">${{ number_format($cat['allocated_amount'] ?? 0, 2) }}</span>
                        </div>
                        @endforeach
                        <hr class="border-slate-300 my-2">
                        <div class="flex items-center justify-between font-semibold">
                            <span>Total Allocated</span>
                            <span class="{{ $totalAllocated <= ($wizardData['total_amount'] ?? 0) ? 'text-emerald-600' : 'text-error' }}">${{ number_format($totalAllocated, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Sharing --}}
                <div class="bg-base-200 rounded-lg p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Sharing</h3>
                    @if(!empty($wizardData['share_with_members']))
                    <div class="space-y-2">
                        <p class="text-sm text-slate-600 mb-2">
                            This budget will be shared with {{ count($wizardData['share_with_members']) }} family member(s)
                            with <span class="font-medium capitalize">{{ $wizardData['share_permission'] ?? 'edit' }}</span> permission.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($wizardData['share_with_members'] as $memberId)
                                @php $member = \App\Models\FamilyMember::find($memberId); @endphp
                                @if($member)
                                <span class="badge badge-primary badge-outline">{{ $member->full_name }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-slate-500">This budget will not be shared with anyone. You can share it later from the budget settings.</p>
                    @endif
                </div>
            </div>

            <form action="{{ route('expenses.budget.store') }}" method="POST">
                @csrf
                <input type="hidden" name="step" value="6">

                <div class="flex justify-between">
                    <a href="{{ route('expenses.budget.create', ['step' => 5]) }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </a>
                    <button type="submit" class="btn btn-success gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Create Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
