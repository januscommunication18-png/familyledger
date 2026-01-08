@extends('layouts.dashboard')

@section('page-name', 'Create Budget')

@section('content')
<div class="p-4 lg:p-6 max-w-3xl mx-auto">
    {{-- Progress Steps --}}
    @php
        $budgetType = $wizardData['type'] ?? 'envelope';
        $isEnvelopeBudget = $budgetType === 'envelope';

        // Define steps based on budget type
        if ($isEnvelopeBudget) {
            $steps = [
                1 => ['icon' => 'layers', 'label' => 'Type', 'color' => 'violet'],
                2 => ['icon' => 'file-text', 'label' => 'Details', 'color' => 'blue'],
                4 => ['icon' => 'folder', 'label' => 'Envelopes', 'color' => 'emerald'],
                5 => ['icon' => 'users', 'label' => 'Share', 'color' => 'amber'],
                6 => ['icon' => 'check-circle', 'label' => 'Review', 'color' => 'green'],
            ];
        } else {
            $steps = [
                1 => ['icon' => 'layers', 'label' => 'Type', 'color' => 'violet'],
                2 => ['icon' => 'file-text', 'label' => 'Details', 'color' => 'blue'],
                3 => ['icon' => 'dollar-sign', 'label' => 'Budget', 'color' => 'cyan'],
                4 => ['icon' => 'target', 'label' => 'Goals', 'color' => 'emerald'],
                5 => ['icon' => 'users', 'label' => 'Share', 'color' => 'amber'],
                6 => ['icon' => 'check-circle', 'label' => 'Review', 'color' => 'green'],
            ];
        }
        $stepKeys = array_keys($steps);
        $currentStepIndex = array_search($step, $stepKeys);
        if ($currentStepIndex === false) $currentStepIndex = 0;
    @endphp

    {{-- Modern Progress Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-between relative">
            {{-- Progress Line Background --}}
            <div class="absolute top-5 left-0 right-0 h-0.5 bg-slate-200 hidden sm:block" style="left: 10%; right: 10%;"></div>

            {{-- Progress Line Active --}}
            @php
                $progressPercent = count($stepKeys) > 1 ? ($currentStepIndex / (count($stepKeys) - 1)) * 80 : 0;
            @endphp
            <div class="absolute top-5 h-0.5 bg-gradient-to-r from-violet-500 via-emerald-500 to-green-500 hidden sm:block transition-all duration-500" style="left: 10%; width: {{ $progressPercent }}%;"></div>

            @foreach($steps as $stepNum => $stepInfo)
            @php
                $stepIndex = array_search($stepNum, $stepKeys);
                $isCompleted = $step > $stepNum;
                $isCurrent = $step === $stepNum;
                $isPending = $step < $stepNum;
            @endphp
            <div class="flex flex-col items-center relative z-10 flex-1">
                {{-- Step Circle --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-sm
                    @if($isCompleted)
                        bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-emerald-200
                    @elseif($isCurrent)
                        bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-violet-200 ring-4 ring-violet-100 scale-110
                    @else
                        bg-white border-2 border-slate-200 text-slate-400
                    @endif
                ">
                    @if($isCompleted)
                        {{-- Checkmark for completed --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        {{-- Icon for current/pending --}}
                        @switch($stepInfo['icon'])
                            @case('layers')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                @break
                            @case('file-text')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                @break
                            @case('dollar-sign')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                @break
                            @case('folder')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                @break
                            @case('target')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                @break
                            @case('users')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @break
                            @case('check-circle')
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                @break
                        @endswitch
                    @endif
                </div>

                {{-- Step Label --}}
                <span class="mt-2 text-xs font-medium transition-colors duration-300
                    @if($isCompleted) text-emerald-600
                    @elseif($isCurrent) text-violet-600
                    @else text-slate-400
                    @endif
                ">
                    {{ $stepInfo['label'] }}
                </span>
            </div>
            @endforeach
        </div>

        {{-- Current Step Indicator (Mobile Friendly) --}}
        <div class="mt-4 text-center sm:hidden">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-violet-50 text-violet-700 rounded-full text-sm font-medium">
                <span class="w-6 h-6 bg-violet-600 text-white rounded-full flex items-center justify-center text-xs">{{ $currentStepIndex + 1 }}</span>
                Step {{ $currentStepIndex + 1 }} of {{ count($steps) }}: {{ $steps[$step]['label'] ?? 'Setup' }}
            </span>
        </div>
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
    @php
        $isEnvelope = ($wizardData['type'] ?? 'envelope') === 'envelope';
    @endphp
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
                        <select name="period" id="periodSelect" class="select select-bordered" required onchange="updateIncomeLabel()">
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
                        <input type="text" name="start_date" class="input input-bordered" value="{{ $wizardData['start_date'] ?? now()->startOfMonth()->format('Y-m-d') }}" data-datepicker placeholder="Select date" required>
                    </div>

                    {{-- Income Field for Envelope Budgeting --}}
                    @if($isEnvelope)
                    <div class="border-t border-slate-200 pt-4 mt-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">
                                    <span id="incomeLabel">Monthly</span> Income
                                </span>
                            </label>
                            <label class="input-group">
                                <span class="bg-base-200">$</span>
                                <input type="number" name="total_amount" id="incomeInput" step="0.01" min="0" class="input input-bordered flex-1" placeholder="5000.00" value="{{ $wizardData['total_amount'] ?? '' }}" required>
                            </label>
                            <label class="label">
                                <span class="label-text-alt text-slate-500">Enter your total income for this budget period. This will be the amount you allocate to your envelopes.</span>
                            </label>
                        </div>

                        {{-- Quick Amount Buttons --}}
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button type="button" onclick="setIncome(2000)" class="btn btn-xs btn-outline">$2,000</button>
                            <button type="button" onclick="setIncome(3000)" class="btn btn-xs btn-outline">$3,000</button>
                            <button type="button" onclick="setIncome(4000)" class="btn btn-xs btn-outline">$4,000</button>
                            <button type="button" onclick="setIncome(5000)" class="btn btn-xs btn-outline">$5,000</button>
                            <button type="button" onclick="setIncome(6000)" class="btn btn-xs btn-outline">$6,000</button>
                            <button type="button" onclick="setIncome(8000)" class="btn btn-xs btn-outline">$8,000</button>
                            <button type="button" onclick="setIncome(10000)" class="btn btn-xs btn-outline">$10,000</button>
                        </div>

                        {{-- Envelope Info --}}
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mt-4">
                            <div class="flex gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgb(16 185 129)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                <p class="text-sm text-emerald-700">With envelope budgeting, your income is divided into "envelopes" for different spending categories. Each envelope gets a fixed amount, helping you control where your money goes.</p>
                            </div>
                        </div>
                    </div>
                    @endif
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

    @if($isEnvelope)
    <script>
    const periodLabels = {
        'weekly': 'Weekly',
        'biweekly': 'Bi-weekly',
        'monthly': 'Monthly',
        'yearly': 'Yearly'
    };

    function updateIncomeLabel() {
        const period = document.getElementById('periodSelect').value;
        document.getElementById('incomeLabel').textContent = periodLabels[period] || 'Monthly';
    }

    function setIncome(amount) {
        document.getElementById('incomeInput').value = amount.toFixed(2);
    }

    // Initialize on load
    updateIncomeLabel();
    </script>
    @endif
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

    {{-- Step 4: Categories (Envelope) or Goals (Traditional) --}}
    @if($step === 4)
    @php
        $isTraditional = ($wizardData['type'] ?? 'envelope') === 'traditional';
    @endphp

    @if($isTraditional)
    {{-- TRADITIONAL BUDGET: Goals --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Set Up Your Goals</h2>
            <p class="text-slate-500 mb-2">Create financial goals to track your spending, income, and savings.</p>
            <p class="text-sm text-blue-600 font-medium mb-6">Total Budget: ${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</p>

            <form action="{{ route('expenses.budget.store') }}" method="POST" id="goalsForm">
                @csrf
                <input type="hidden" name="step" value="4">

                <div id="goalsContainer" class="space-y-4 mb-4">
                    @php
                        $existingGoals = $wizardData['goals'] ?? [
                            ['name' => 'Monthly Spending', 'type' => 'expense', 'target_amount' => $wizardData['total_amount'] ?? 0, 'icon' => '📉', 'description' => ''],
                        ];
                    @endphp
                    @foreach($existingGoals as $index => $goal)
                    <div class="goal-row p-4 bg-base-200 rounded-lg">
                        <div class="flex items-start gap-3 mb-3">
                            <input type="text" name="goals[{{ $index }}][icon]" value="{{ $goal['icon'] ?? '🎯' }}" class="input input-bordered input-sm w-14 text-center text-lg">
                            <div class="flex-1 space-y-3">
                                <input type="text" name="goals[{{ $index }}][name]" value="{{ $goal['name'] ?? '' }}" class="input input-bordered input-sm w-full" placeholder="Goal name (e.g., Monthly Savings)" required>
                                <input type="text" name="goals[{{ $index }}][description]" value="{{ $goal['description'] ?? '' }}" class="input input-bordered input-sm w-full" placeholder="Description (optional)">
                                <div class="flex flex-wrap gap-3">
                                    <select name="goals[{{ $index }}][type]" class="select select-bordered select-sm flex-1 min-w-[150px]" required>
                                        @foreach($goalTypes as $type => $info)
                                        <option value="{{ $type }}" {{ ($goal['type'] ?? 'expense') === $type ? 'selected' : '' }}>{{ $info['icon'] }} {{ $info['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <label class="input-group input-group-sm flex-1 min-w-[150px]">
                                        <span class="bg-base-300 text-xs">Target $</span>
                                        <input type="number" name="goals[{{ $index }}][target_amount]" value="{{ $goal['target_amount'] ?? 0 }}" step="0.01" min="0" class="input input-bordered input-sm w-full" placeholder="0.00" required>
                                    </label>
                                </div>
                            </div>
                            <button type="button" onclick="removeGoal(this)" class="btn btn-ghost btn-sm btn-square text-error mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addGoal()" class="btn btn-ghost btn-sm gap-1 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add Goal
                </button>

                {{-- Goal Types Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h4 class="font-medium text-blue-800 mb-2">Goal Types</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        @foreach($goalTypes as $type => $info)
                        <div class="flex items-start gap-2">
                            <span class="text-lg">{{ $info['icon'] }}</span>
                            <div>
                                <p class="font-medium text-slate-700">{{ $info['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $info['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
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
    let goalIndex = {{ count($existingGoals) }};
    const goalIcons = {!! json_encode($goalIcons) !!};

    function addGoal() {
        const container = document.getElementById('goalsContainer');
        const icon = goalIcons[goalIndex % goalIcons.length];

        const html = `
            <div class="goal-row p-4 bg-base-200 rounded-lg">
                <div class="flex items-start gap-3 mb-3">
                    <input type="text" name="goals[${goalIndex}][icon]" value="${icon}" class="input input-bordered input-sm w-14 text-center text-lg">
                    <div class="flex-1 space-y-3">
                        <input type="text" name="goals[${goalIndex}][name]" class="input input-bordered input-sm w-full" placeholder="Goal name (e.g., Monthly Savings)" required>
                        <input type="text" name="goals[${goalIndex}][description]" class="input input-bordered input-sm w-full" placeholder="Description (optional)">
                        <div class="flex flex-wrap gap-3">
                            <select name="goals[${goalIndex}][type]" class="select select-bordered select-sm flex-1 min-w-[150px]" required>
                                <option value="expense">📉 Spending Limit</option>
                                <option value="income">📈 Income Target</option>
                                <option value="saving">🎯 Savings Goal</option>
                            </select>
                            <label class="input-group input-group-sm flex-1 min-w-[150px]">
                                <span class="bg-base-300 text-xs">Target $</span>
                                <input type="number" name="goals[${goalIndex}][target_amount]" value="0" step="0.01" min="0" class="input input-bordered input-sm w-full" placeholder="0.00" required>
                            </label>
                        </div>
                    </div>
                    <button type="button" onclick="removeGoal(this)" class="btn btn-ghost btn-sm btn-square text-error mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        goalIndex++;
    }

    function removeGoal(btn) {
        const rows = document.querySelectorAll('.goal-row');
        if (rows.length > 1) {
            btn.closest('.goal-row').remove();
        }
    }
    </script>

    @else
    {{-- ENVELOPE BUDGET: Categories --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Set Up Your Envelopes</h2>
            <p class="text-slate-500 mb-2">Divide your income into spending categories (envelopes).</p>
            <p class="text-sm text-emerald-600 font-medium mb-6">{{ ucfirst($wizardData['period'] ?? 'Monthly') }} Income: ${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</p>

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
                        <span class="text-sm text-slate-600">{{ ucfirst($wizardData['period'] ?? 'Monthly') }} Income:</span>
                        <span class="font-semibold">${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</span>
                    </div>
                    <hr class="my-2 border-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Unallocated:</span>
                        <span id="remaining" class="font-semibold text-emerald-600">$0.00</span>
                    </div>
                </div>

                <div class="flex justify-between">
                    {{-- For envelope budgets, go back to step 2 (skipping step 3) --}}
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
                            <span class="text-slate-500">{{ ($wizardData['type'] ?? 'envelope') === 'envelope' ? ucfirst($wizardData['period'] ?? 'Monthly') . ' Income' : 'Total Budget' }}:</span>
                            <span class="ml-2 font-bold text-lg text-emerald-600">${{ number_format($wizardData['total_amount'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Categories or Goals --}}
                @if(($wizardData['type'] ?? 'envelope') === 'traditional')
                {{-- Goals for Traditional Budget --}}
                <div class="bg-base-200 rounded-lg p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Goals ({{ count($wizardData['goals'] ?? []) }})</h3>
                    <div class="space-y-2">
                        @foreach(($wizardData['goals'] ?? []) as $goal)
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span>{{ $goal['icon'] ?? '🎯' }}</span>
                                <span>{{ $goal['name'] }}</span>
                                <span class="badge badge-xs {{ $goal['type'] === 'expense' ? 'badge-error' : ($goal['type'] === 'income' ? 'badge-success' : 'badge-info') }}">
                                    {{ ucfirst($goal['type']) }}
                                </span>
                            </span>
                            <span class="font-medium">${{ number_format($goal['target_amount'] ?? 0, 2) }}</span>
                        </div>
                        @if(!empty($goal['description']))
                        <p class="text-xs text-slate-500 ml-8">{{ $goal['description'] }}</p>
                        @endif
                        @endforeach
                    </div>
                </div>
                @else
                {{-- Categories for Envelope Budget --}}
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
                @endif

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
