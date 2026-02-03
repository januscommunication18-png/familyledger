@extends('backoffice.layouts.app')

@php
    $header = 'Client Details';
@endphp

@section('content')
    <div x-data="{ ...clientViewAccess(), showDeleteDataModal: false, showDeleteClientModal: false, deleteDataConfirmText: '', deleteClientConfirmText: '' }" x-init="init()">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('backoffice.clients.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Clients
            </a>
        </div>

        <!-- Client Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
                        <span class="text-gray-600 dark:text-gray-300 font-bold text-2xl">
                            {{ strtoupper(substr($client->id, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $client->name ?? $client->id }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">
                            ID: {{ $client->id }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1.5 text-sm font-medium rounded-full {{ $client->is_active ?? true ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                        {{ $client->is_active ?? true ? 'Active' : 'Inactive' }}
                    </span>
                    <form method="POST" action="{{ route('backoffice.clients.toggleStatus', $client) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg border {{ $client->is_active ?? true ? 'border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20' : 'border-green-300 text-green-600 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/20' }} transition-colors">
                            {{ $client->is_active ?? true ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <button
                        type="button"
                        @click="showDeleteDataModal = true"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                    >
                        Delete Data
                    </button>
                    <button
                        type="button"
                        @click="showDeleteClientModal = true"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors"
                    >
                        Delete Client
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats (counts only, no PII) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['users_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Family Members</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['family_members_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Joined</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $client->created_at->format('M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Data Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Client Data</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Secure access to client information</p>
                </div>
            </div>

            <!-- Access Request -->
            <div x-show="!hasAccess && !showCodeInput" class="text-center py-12">
                <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Secure Access Required</h4>
                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                    To view client data, you need to verify your identity. A 6-digit code will be sent to your email.
                </p>
                <button
                    @click="requestCode()"
                    :disabled="loading"
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50"
                >
                    <span x-show="!loading">Request Access Code</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>
            </div>

            <!-- Code Input -->
            <div x-show="showCodeInput && !hasAccess" x-cloak class="max-w-md mx-auto py-8">
                <div x-show="debugCode" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        <strong>Debug Mode:</strong> Your code is <span class="font-mono font-bold" x-text="debugCode"></span>
                    </p>
                </div>

                <div x-show="message" class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-sm text-green-800 dark:text-green-300" x-text="message"></p>
                </div>

                <div x-show="error" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-800 dark:text-red-300" x-text="error"></p>
                </div>

                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">Enter Security Code</h4>
                <p class="text-gray-500 dark:text-gray-400 mb-6 text-center text-sm">
                    Enter the 6-digit code sent to your email
                </p>

                <div class="flex gap-2 justify-center mb-6">
                    <template x-for="(digit, index) in 6" :key="index">
                        <input
                            type="text"
                            maxlength="1"
                            class="w-12 h-14 text-center text-2xl font-bold border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :data-view-code-input="index"
                            @input="handleCodeInput($event, index)"
                            @keydown="handleCodeKeydown($event, index)"
                            @paste="handleCodePaste($event)"
                            inputmode="numeric"
                            pattern="[0-9]*"
                        >
                    </template>
                </div>

                <div class="flex gap-3 justify-center">
                    <button
                        @click="verifyCode()"
                        :disabled="loading || code.length !== 6"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50"
                    >
                        <span x-show="!loading">Verify Code</span>
                        <span x-show="loading">Verifying...</span>
                    </button>
                    <button
                        @click="requestCode()"
                        :disabled="loading"
                        class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                    >
                        Resend Code
                    </button>
                </div>
            </div>

            <!-- Access Granted - View Data Button -->
            <div x-show="hasAccess" x-cloak class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Access Granted</h4>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    You have temporary access to view client data. Access will expire when you leave this page.
                </p>
                <a href="{{ route('backoffice.clients.data', $client) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Client Data
                </a>
            </div>
        </div>

        <!-- Delete Data Modal -->
        <div x-show="showDeleteDataModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Backdrop -->
                <div
                    x-show="showDeleteDataModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/50"
                    @click="showDeleteDataModal = false"
                ></div>

                <!-- Modal Content -->
                <div
                    x-show="showDeleteDataModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full p-6 mx-auto"
                    @click.stop
                >
                    <h3 class="font-bold text-lg text-orange-600 dark:text-orange-400 flex items-center gap-2 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Client Data Only
                    </h3>

                    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-4">
                        <p class="text-sm text-orange-800 dark:text-orange-300">
                            <strong>Warning:</strong> This will delete all data but keep the account:
                        </p>
                        <ul class="list-disc list-inside text-sm text-orange-700 dark:text-orange-400 mt-2 space-y-1">
                            <li>All family members and circles</li>
                            <li>All documents and files</li>
                            <li>All financial records and transactions</li>
                            <li>All journal entries and attachments</li>
                            <li>All other associated data</li>
                        </ul>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-3">
                            <strong>Kept:</strong> User accounts and tenant will remain intact.
                        </p>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Type <strong class="text-orange-600 dark:text-orange-400">DELETE</strong> to confirm:
                    </p>

                    <form method="POST" action="{{ route('backoffice.clients.destroyData', $client) }}">
                        @csrf
                        @method('DELETE')
                        <input
                            type="text"
                            name="confirmation"
                            x-model="deleteDataConfirmText"
                            placeholder="Type DELETE to confirm"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 mb-4"
                            autocomplete="off"
                        >

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showDeleteDataModal = false; deleteDataConfirmText = ''"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="deleteDataConfirmText !== 'DELETE'"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Delete Data Only
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Client Modal (Everything) -->
        <div x-show="showDeleteClientModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Backdrop -->
                <div
                    x-show="showDeleteClientModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/50"
                    @click="showDeleteClientModal = false"
                ></div>

                <!-- Modal Content -->
                <div
                    x-show="showDeleteClientModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full p-6 mx-auto"
                    @click.stop
                >
                    <h3 class="font-bold text-lg text-red-600 dark:text-red-400 flex items-center gap-2 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Delete Client Permanently
                    </h3>

                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                        <p class="text-sm text-red-800 dark:text-red-300">
                            <strong>DANGER:</strong> This will permanently delete EVERYTHING:
                        </p>
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 mt-2 space-y-1">
                            <li>All user accounts ({{ $stats['users_count'] }} users)</li>
                            <li>All family members ({{ $stats['family_members_count'] }} members)</li>
                            <li>All documents, files, and attachments</li>
                            <li>All financial and personal records</li>
                            <li>The tenant record itself</li>
                        </ul>
                        <p class="text-sm text-red-800 dark:text-red-300 mt-3 font-semibold">
                            This action CANNOT be undone. Users will no longer be able to login.
                        </p>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        Type <strong class="text-red-600 dark:text-red-400">DELETE FOREVER</strong> to confirm:
                    </p>

                    <form method="POST" action="{{ route('backoffice.clients.destroy', $client) }}">
                        @csrf
                        @method('DELETE')
                        <input
                            type="text"
                            name="confirmation"
                            x-model="deleteClientConfirmText"
                            placeholder="Type DELETE FOREVER to confirm"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 mb-4"
                            autocomplete="off"
                        >

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showDeleteClientModal = false; deleteClientConfirmText = ''"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="deleteClientConfirmText !== 'DELETE FOREVER'"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Delete Forever
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function clientViewAccess() {
    return {
        hasAccess: {{ $hasViewAccess ? 'true' : 'false' }},
        showCodeInput: false,
        code: '',
        debugCode: '',
        loading: false,
        message: '',
        error: '',

        init() {
            // Revoke access when leaving the page
            window.addEventListener('beforeunload', () => {
                if (this.hasAccess) {
                    navigator.sendBeacon('{{ route('backoffice.clients.revokeAccess', $client) }}', new URLSearchParams({
                        _token: '{{ csrf_token() }}'
                    }));
                }
            });
        },

        async requestCode() {
            this.loading = true;
            this.error = '';
            this.message = '';

            try {
                const response = await fetch('{{ route('backoffice.clients.requestViewCode', $client) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showCodeInput = true;
                    this.message = data.message;
                    this.debugCode = data.code_debug || '';

                    // Focus first input
                    this.$nextTick(() => {
                        document.querySelector('[data-view-code-input="0"]')?.focus();
                    });
                } else {
                    this.error = data.message || 'Failed to request code';
                }
            } catch (err) {
                this.error = 'An error occurred. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async verifyCode() {
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('{{ route('backoffice.clients.verifyViewCode', $client) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: this.code })
                });

                const data = await response.json();

                if (data.success) {
                    this.hasAccess = true;
                } else {
                    this.error = data.message || 'Invalid code';
                }
            } catch (err) {
                this.error = 'An error occurred. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        handleCodeInput(event, index) {
            const input = event.target;
            const value = input.value.replace(/[^0-9]/g, '');
            input.value = value;

            if (value && index < 5) {
                const next = document.querySelector(`[data-view-code-input="${index + 1}"]`);
                if (next) next.focus();
            }

            this.updateCode();
        },

        handleCodeKeydown(event, index) {
            if (event.key === 'Backspace' && !event.target.value && index > 0) {
                const prev = document.querySelector(`[data-view-code-input="${index - 1}"]`);
                if (prev) {
                    prev.focus();
                    prev.value = '';
                }
                this.updateCode();
            }
        },

        handleCodePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);

            digits.split('').forEach((digit, i) => {
                const input = document.querySelector(`[data-view-code-input="${i}"]`);
                if (input) input.value = digit;
            });

            this.updateCode();

            if (digits.length === 6) {
                this.verifyCode();
            }
        },

        updateCode() {
            let code = '';
            for (let i = 0; i < 6; i++) {
                const input = document.querySelector(`[data-view-code-input="${i}"]`);
                code += input ? input.value : '';
            }
            this.code = code;
        }
    };
}
</script>
@endpush
