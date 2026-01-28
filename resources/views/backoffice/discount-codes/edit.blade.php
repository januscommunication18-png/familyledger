@extends('backoffice.layouts.app')

@php
    $header = 'Edit Discount Code';
@endphp

@section('content')
    <div class="max-w-3xl">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('backoffice.discount-codes.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Discount Codes
            </a>
        </div>

        <form method="POST" action="{{ route('backoffice.discount-codes.update', $discountCode) }}" class="space-y-6" x-data="discountForm()">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Discount Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $discountCode->name) }}"
                            placeholder="e.g., Summer Sale 2026"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount Code <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="code"
                                name="code"
                                x-model="code"
                                value="{{ old('code', $discountCode->code) }}"
                                placeholder="e.g., SUMMER20"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 uppercase"
                                required
                            >
                            <button
                                type="button"
                                @click="generateCode()"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                title="Generate Random Code"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="discount_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount Percentage <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="discount_percentage"
                                name="discount_percentage"
                                x-model="discountPercentage"
                                value="{{ old('discount_percentage', $discountCode->discount_percentage) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="e.g., 20"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                required
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                        </div>
                        @error('discount_percentage')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="plan_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Used for Plan Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="plan_type"
                            name="plan_type"
                            x-model="planType"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                            <option value="monthly" {{ old('plan_type', $discountCode->plan_type) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('plan_type', $discountCode->plan_type) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="both" {{ old('plan_type', $discountCode->plan_type) === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                        @error('plan_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="package_plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Specific Package Plan (Optional)
                        </label>
                        <select
                            id="package_plan_id"
                            name="package_plan_id"
                            x-model="selectedPlanId"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <option value="">All Plans</option>
                            @foreach ($packagePlans as $plan)
                                <option value="{{ $plan->id }}" data-monthly="{{ $plan->cost_per_month }}" data-yearly="{{ $plan->cost_per_year }}" {{ old('package_plan_id', $discountCode->package_plan_id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} ({{ ucfirst($plan->type) }})
                                </option>
                            @endforeach
                        </select>
                        @error('package_plan_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Discounted Payment Preview -->
                <div x-show="selectedPlanId && discountPercentage > 0" class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Discounted Payment Preview</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div x-show="planType === 'monthly' || planType === 'both'">
                            <span class="text-green-700 dark:text-green-400">Monthly:</span>
                            <span class="font-medium text-green-800 dark:text-green-300">
                                $<span x-text="calculateDiscount('monthly')">0.00</span>
                            </span>
                            <span class="text-green-600 dark:text-green-500 text-xs line-through ml-1">
                                $<span x-text="getOriginalPrice('monthly')">0.00</span>
                            </span>
                        </div>
                        <div x-show="planType === 'yearly' || planType === 'both'">
                            <span class="text-green-700 dark:text-green-400">Yearly:</span>
                            <span class="font-medium text-green-800 dark:text-green-300">
                                $<span x-text="calculateDiscount('yearly')">0.00</span>
                            </span>
                            <span class="text-green-600 dark:text-green-500 text-xs line-through ml-1">
                                $<span x-text="getOriginalPrice('yearly')">0.00</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usage & Validity</h2>

                <!-- Usage Stats -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Times Used: <span class="font-medium text-gray-900 dark:text-white">{{ $discountCode->times_used }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="max_uses" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Maximum Uses
                        </label>
                        <input
                            type="number"
                            id="max_uses"
                            name="max_uses"
                            value="{{ old('max_uses', $discountCode->max_uses) }}"
                            min="1"
                            placeholder="Leave empty for unlimited"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited uses</p>
                        @error('max_uses')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Valid From
                        </label>
                        <input
                            type="date"
                            id="valid_from"
                            name="valid_from"
                            value="{{ old('valid_from', $discountCode->valid_from?->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('valid_from')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Valid Until
                        </label>
                        <input
                            type="date"
                            id="valid_until"
                            name="valid_until"
                            value="{{ old('valid_until', $discountCode->valid_until?->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('valid_until')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h2>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $discountCode->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                    >
                    <span class="text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('backoffice.discount-codes.index') }}"
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Update Discount
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function discountForm() {
            return {
                code: '{{ old('code', $discountCode->code) }}',
                discountPercentage: {{ old('discount_percentage', $discountCode->discount_percentage) }},
                planType: '{{ old('plan_type', $discountCode->plan_type) }}',
                selectedPlanId: '{{ old('package_plan_id', $discountCode->package_plan_id) }}',
                plans: @json($packagePlans->keyBy('id')),

                generateCode() {
                    fetch('{{ route('backoffice.discount-codes.generateCode') }}')
                        .then(response => response.json())
                        .then(data => {
                            this.code = data.code;
                        });
                },

                getOriginalPrice(type) {
                    if (!this.selectedPlanId || !this.plans[this.selectedPlanId]) return '0.00';
                    const plan = this.plans[this.selectedPlanId];
                    return type === 'monthly' ? parseFloat(plan.cost_per_month).toFixed(2) : parseFloat(plan.cost_per_year).toFixed(2);
                },

                calculateDiscount(type) {
                    if (!this.selectedPlanId || !this.plans[this.selectedPlanId] || !this.discountPercentage) return '0.00';
                    const plan = this.plans[this.selectedPlanId];
                    const price = type === 'monthly' ? parseFloat(plan.cost_per_month) : parseFloat(plan.cost_per_year);
                    const discounted = price - (price * this.discountPercentage / 100);
                    return discounted.toFixed(2);
                }
            }
        }
    </script>
    @endpush
@endsection
