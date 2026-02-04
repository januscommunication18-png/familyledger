@extends('backoffice.layouts.app')

@php
    $header = 'Drip Campaign Email Logs';
@endphp

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Sent</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['sent']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Failed</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['failed']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Opened</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['opened']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Clicked</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['clicked']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Skipped</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ number_format($stats['skipped']) }}</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-4">
            <a href="{{ route('backoffice.email-logs.index') }}"
               class="pb-3 px-1 border-b-2 {{ request()->routeIs('backoffice.email-logs.index') ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} font-medium transition-colors">
                All Emails
            </a>
            <a href="{{ route('backoffice.email-logs.drip') }}"
               class="pb-3 px-1 border-b-2 {{ request()->routeIs('backoffice.email-logs.drip') ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} font-medium transition-colors">
                Drip Campaign Emails
            </a>
        </nav>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mb-6 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input
                    type="text"
                    id="email"
                    name="email"
                    value="{{ request('email') }}"
                    placeholder="Search by email..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <div class="w-48">
                <label for="campaign" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campaign</label>
                <select
                    id="campaign"
                    name="campaign"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">All Campaigns</option>
                    @foreach ($campaigns as $id => $name)
                        <option value="{{ $id }}" {{ request('campaign') == $id ? 'selected' : '' }}>{{ $name }}</option>
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

            <div class="w-36">
                <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                <input
                    type="date"
                    id="from_date"
                    name="from_date"
                    value="{{ request('from_date') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <div class="w-36">
                <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                <input
                    type="date"
                    id="to_date"
                    name="to_date"
                    value="{{ request('to_date') }}"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Filter
            </button>

            @if (request()->hasAny(['email', 'campaign', 'status', 'from_date', 'to_date']))
                <a href="{{ route('backoffice.email-logs.drip') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Step</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opened</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clicked</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($dripLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $log->email }}</p>
                                    @if ($log->user)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $log->user->name ?? 'Unknown User' }}</p>
                                    @endif
                                    @if ($log->tenant)
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $log->tenant->name }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($log->campaign)
                                    <a href="{{ route('backoffice.drip-campaigns.show', $log->campaign) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                        {{ $log->campaign->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($log->step)
                                    <div>
                                        <p class="text-gray-900 dark:text-white">Step {{ $log->step->sequence_order }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $log->step->subject }}</p>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                    @if ($log->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif ($log->status === 'opened') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif ($log->status === 'clicked') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400
                                    @elseif ($log->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @elseif ($log->status === 'skipped') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @endif">
                                    {{ $log->getStatusLabel() }}
                                </span>
                                @if ($log->status === 'failed' && $log->error_message)
                                    <p class="text-xs text-red-500 mt-1 truncate max-w-[150px]" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 30) }}</p>
                                @endif
                                @if ($log->status === 'skipped' && $log->error_message)
                                    <p class="text-xs text-gray-500 mt-1 truncate max-w-[150px]" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                @if ($log->sent_at)
                                    <span title="{{ $log->sent_at->format('M j, Y g:i A') }}">
                                        {{ $log->sent_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                @if ($log->opened_at)
                                    <span class="text-green-600 dark:text-green-400" title="{{ $log->opened_at->format('M j, Y g:i A') }}">
                                        {{ $log->opened_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                @if ($log->clicked_at)
                                    <span class="text-purple-600 dark:text-purple-400" title="{{ $log->clicked_at->format('M j, Y g:i A') }}">
                                        {{ $log->clicked_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2">No drip campaign email logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($dripLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $dripLogs->links() }}
            </div>
        @endif
    </div>
@endsection
