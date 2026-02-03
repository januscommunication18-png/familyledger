@extends('backoffice.layouts.app')

@php
    $header = 'Create Drip Campaign';
@endphp

@section('content')
    <div class="max-w-3xl">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('backoffice.drip-campaigns.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Drip Campaigns
            </a>
        </div>

        <form method="POST" action="{{ route('backoffice.drip-campaigns.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Campaign Information</h2>

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Campaign Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="e.g., Welcome Series, Trial Expiring Reminder"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Brief description of this campaign's purpose..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="trigger_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Trigger Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="trigger_type"
                                name="trigger_type"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                required
                            >
                                @foreach ($triggerTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('trigger_type', 'custom') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">When should this campaign start?</p>
                            @error('trigger_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                required
                            >
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', 'draft') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Initial Delay -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Initial Delay</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">How long to wait after the trigger event before sending the first email.</p>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="delay_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Days
                        </label>
                        <input
                            type="number"
                            id="delay_days"
                            name="delay_days"
                            value="{{ old('delay_days', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('delay_days')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delay_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Hours
                        </label>
                        <input
                            type="number"
                            id="delay_hours"
                            name="delay_hours"
                            value="{{ old('delay_hours', 0) }}"
                            min="0"
                            max="23"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required
                        >
                        @error('delay_hours')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Info box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <p class="font-medium">Next Steps</p>
                        <p>After creating the campaign, you'll be able to add email steps with content and delay settings.</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('backoffice.drip-campaigns.index') }}"
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Create Campaign
                </button>
            </div>
        </form>
    </div>
@endsection
