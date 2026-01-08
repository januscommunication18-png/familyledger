@extends('backoffice.layouts.app')

@php
    $header = 'My Profile';
@endphp

@section('content')
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update your account's profile information.</p>
            </div>

            <form method="POST" action="{{ route('backoffice.settings.profile.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Name
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $admin->name) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $admin->email) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mt-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Account Information</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Account Created</span>
                    <span class="text-gray-900 dark:text-white">{{ $admin->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Last Login</span>
                    <span class="text-gray-900 dark:text-white">{{ $admin->last_login_at?->format('M d, Y g:i A') ?? 'Never' }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-500 dark:text-gray-400">Last Login IP</span>
                    <span class="text-gray-900 dark:text-white font-mono text-sm">{{ $admin->last_login_ip ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
