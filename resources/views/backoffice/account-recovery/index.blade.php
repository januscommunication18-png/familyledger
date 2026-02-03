@extends('backoffice.layouts.app')

@php
    $header = 'Account Recovery';
@endphp

@section('content')
    <div class="max-w-2xl mx-auto">
        <!-- Search Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Account Recovery</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Search for a client to help them recover their account. You'll need to verify their recovery code before performing any actions.
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('backoffice.account-recovery.search') }}" method="GET" class="space-y-4">
                <div>
                    <label for="query" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Search by Email or Client ID
                    </label>
                    <input
                        type="text"
                        id="query"
                        name="query"
                        value="{{ old('query', request('query')) }}"
                        placeholder="Enter client email or ID..."
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        required
                        minlength="3"
                        autofocus
                    >
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Enter the client's email address or their tenant ID
                    </p>
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search Client
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
            <h3 class="font-medium text-amber-800 dark:text-amber-400 mb-2">How Account Recovery Works</h3>
            <ol class="text-sm text-amber-700 dark:text-amber-300 space-y-2 list-decimal list-inside">
                <li>Search for the client using their email address or ID</li>
                <li>Ask the client to provide their 16-digit recovery code (verbally or via chat)</li>
                <li>Enter the recovery code to verify their identity</li>
                <li>Once verified, you can perform recovery actions (change email, reset password, etc.)</li>
            </ol>
        </div>

        <!-- Security Notice -->
        <div class="mt-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-medium text-gray-700 dark:text-gray-300">Security Notice</p>
                    <p class="mt-1">All recovery actions are logged for audit purposes. Verification expires after 30 minutes of inactivity.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
