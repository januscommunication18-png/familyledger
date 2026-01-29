@extends('backoffice.layouts.app')

@php
    $header = 'Drip Campaigns';
@endphp

@section('content')
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-gray-600 dark:text-gray-400">Manage automated email sequences for your users</p>
        </div>
        <a href="{{ route('backoffice.drip-campaigns.create') }}"
           class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Campaign
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mb-6 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <div class="w-40">
                <label for="trigger" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trigger</label>
                <select
                    id="trigger"
                    name="trigger"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">All Triggers</option>
                    @foreach ($triggerTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('trigger') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-40">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">All</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Filter
            </button>

            @if (request()->hasAny(['search', 'trigger', 'status']))
                <a href="{{ route('backoffice.drip-campaigns.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trigger</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Steps</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sent</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <a href="{{ route('backoffice.drip-campaigns.show', $campaign) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                        {{ $campaign->name }}
                                    </a>
                                    @if ($campaign->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $campaign->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $campaign->getTriggerLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-900 dark:text-white">{{ $campaign->steps_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($campaign->logs_count) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($campaign->isActive())
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                @elseif ($campaign->isPaused())
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Paused
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('backoffice.drip-campaigns.show', $campaign) }}"
                                       class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                       title="View Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('backoffice.drip-campaigns.edit', $campaign) }}"
                                       class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                       title="Edit Campaign">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('backoffice.drip-campaigns.logs', $campaign) }}"
                                       class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                       title="View Logs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('backoffice.drip-campaigns.toggleStatus', $campaign) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $campaign->isActive() ? 'paused' : 'active' }}">
                                        <button type="submit"
                                                class="p-2 text-gray-500 hover:text-{{ $campaign->isActive() ? 'yellow' : 'green' }}-600 transition-colors"
                                                title="{{ $campaign->isActive() ? 'Pause' : 'Activate' }}">
                                            @if ($campaign->isActive())
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('backoffice.drip-campaigns.destroy', $campaign) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this campaign? All email steps and logs will also be deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-gray-500 hover:text-red-600 transition-colors"
                                                title="Delete Campaign">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mb-2">No drip campaigns found</p>
                                    <a href="{{ route('backoffice.drip-campaigns.create') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                        Create your first campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($campaigns->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
@endsection
