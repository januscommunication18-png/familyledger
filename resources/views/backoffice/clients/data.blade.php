@extends('backoffice.layouts.app')

@php
    $header = 'Client Data';
@endphp

@section('content')
    <div x-data="{ activeTab: 'users' }" x-init="
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon('{{ route('backoffice.clients.revokeAccess', $client) }}', new URLSearchParams({
                _token: '{{ csrf_token() }}'
            }));
        });
    ">
        <!-- Warning Banner -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Secure Viewing Session</h4>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400">
                        You are viewing sensitive client data. This session is being logged. Access will expire when you leave this page.
                    </p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('backoffice.clients.show', $client) }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Client Overview
            </a>
        </div>

        <!-- Client Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
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
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="flex gap-4">
                <button
                    @click="activeTab = 'users'"
                    :class="activeTab === 'users' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
                >
                    Users ({{ $users->count() }})
                </button>
                <button
                    @click="activeTab = 'family'"
                    :class="activeTab === 'family' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
                >
                    Family Members ({{ $familyMembers->count() }})
                </button>
            </nav>
        </div>

        <!-- Users Tab -->
        <div x-show="activeTab === 'users'">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Login</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                                <span class="text-primary-700 dark:text-primary-300 font-medium">
                                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $user->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Family Members Tab -->
        <div x-show="activeTab === 'family'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Relationship</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date of Birth</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($familyMembers as $member)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                                <span class="text-purple-700 dark:text-purple-300 font-medium">
                                                    {{ strtoupper(substr($member->first_name ?? 'M', 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ $member->first_name }} {{ $member->last_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $member->relationship ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $member->date_of_birth?->format('M d, Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $member->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No family members found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
