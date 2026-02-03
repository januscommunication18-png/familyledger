@extends('backoffice.layouts.app')

@php
    $header = 'Create Package Plan';
@endphp

@section('content')
    <div class="max-w-4xl">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('backoffice.package-plans.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Plans
            </a>
        </div>

        <form method="POST" action="{{ route('backoffice.package-plans.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Package Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Type of Plan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="type"
                            name="type"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                            <option value="free" {{ old('type') === 'free' ? 'selected' : '' }}>Free Plan</option>
                            <option value="paid" {{ old('type') === 'paid' ? 'selected' : '' }}>Paid Plan</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Package Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pricing</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="trial_period_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Trial Period (Days) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="trial_period_days"
                            name="trial_period_days"
                            value="{{ old('trial_period_days', 30) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('trial_period_days')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cost_per_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Cost Per Month ($) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="cost_per_month"
                            name="cost_per_month"
                            value="{{ old('cost_per_month', 0) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('cost_per_month')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cost_per_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Cost Per Year ($) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="cost_per_year"
                            name="cost_per_year"
                            value="{{ old('cost_per_year', 0) }}"
                            min="0"
                            step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('cost_per_year')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Feature Limits -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Feature Limits</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Enter 0 for unlimited</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="family_circles_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Family Circles <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="family_circles_limit"
                            name="family_circles_limit"
                            value="{{ old('family_circles_limit', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = Unlimited family circles</p>
                        @error('family_circles_limit')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="family_members_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Family Members <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="family_members_limit"
                            name="family_members_limit"
                            value="{{ old('family_members_limit', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = Unlimited family members</p>
                        @error('family_members_limit')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="document_storage_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Document Storage <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="document_storage_limit"
                            name="document_storage_limit"
                            value="{{ old('document_storage_limit', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = Unlimited uploads</p>
                        @error('document_storage_limit')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Reminder Features -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reminder Features</h2>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="reminder_features[]"
                            value="push_notification"
                            {{ in_array('push_notification', old('reminder_features', [])) ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <span class="text-gray-700 dark:text-gray-300">Push Notification</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="reminder_features[]"
                            value="email_reminder"
                            {{ in_array('email_reminder', old('reminder_features', [])) ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <span class="text-gray-700 dark:text-gray-300">Email Reminder</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="reminder_features[]"
                            value="sms_reminder"
                            {{ in_array('sms_reminder', old('reminder_features', [])) ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <span class="text-gray-700 dark:text-gray-300">SMS Reminder</span>
                    </label>
                </div>
                @error('reminder_features')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Paddle Integration -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Paddle Integration</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="paddle_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Paddle Product ID
                        </label>
                        <input
                            type="text"
                            id="paddle_product_id"
                            name="paddle_product_id"
                            value="{{ old('paddle_product_id') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('paddle_product_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="paddle_monthly_price_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Monthly Price ID
                        </label>
                        <input
                            type="text"
                            id="paddle_monthly_price_id"
                            name="paddle_monthly_price_id"
                            value="{{ old('paddle_monthly_price_id') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('paddle_monthly_price_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="paddle_yearly_price_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Yearly Price ID
                        </label>
                        <input
                            type="text"
                            id="paddle_yearly_price_id"
                            name="paddle_yearly_price_id"
                            value="{{ old('paddle_yearly_price_id') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('paddle_yearly_price_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status & Order -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Settings</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                            >
                            <span class="text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sort Order
                        </label>
                        <input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            value="{{ old('sort_order', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('backoffice.package-plans.index') }}"
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Create Plan
                </button>
            </div>
        </form>
    </div>
@endsection
