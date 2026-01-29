@extends('backoffice.layouts.app')

@php
    $header = $campaign->name;
@endphp

@section('content')
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('backoffice.drip-campaigns.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Drip Campaigns
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Campaign Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $campaign->name }}</h2>
                            @if ($campaign->isActive())
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                            @elseif ($campaign->isPaused())
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Paused</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">Draft</span>
                            @endif
                        </div>
                        @if ($campaign->description)
                            <p class="text-gray-600 dark:text-gray-400">{{ $campaign->description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('backoffice.drip-campaigns.edit', $campaign) }}"
                       class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Campaign
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Trigger</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $campaign->getTriggerLabel() }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email Steps</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $campaign->steps->count() }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Initial Delay</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            @if ($campaign->delay_days > 0 || $campaign->delay_hours > 0)
                                {{ $campaign->delay_days }}d {{ $campaign->delay_hours }}h
                            @else
                                Immediate
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $campaign->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Email Steps Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Sequence</h3>

                @if ($campaign->steps->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No email steps configured yet.</p>
                        <a href="{{ route('backoffice.drip-campaigns.edit', $campaign) }}" class="text-primary-600 hover:text-primary-700">Add email steps</a>
                    </div>
                @else
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-6">
                            @foreach ($campaign->steps as $step)
                                <div class="relative pl-10">
                                    <div class="absolute left-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center font-semibold text-sm border-4 border-white dark:border-gray-800">
                                        {{ $step->sequence_order }}
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $step->subject }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $step->getFormattedDelay() }} after {{ $step->sequence_order === 1 ? 'trigger' : 'previous step' }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $step->getSentCount() }} sent
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Logs -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <a href="{{ route('backoffice.drip-campaigns.logs', $campaign) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                        View All Logs
                    </a>
                </div>

                @if ($recentLogs->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No emails sent yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <th class="pb-3">Email</th>
                                    <th class="pb-3">Step</th>
                                    <th class="pb-3 text-center">Status</th>
                                    <th class="pb-3 text-right">Sent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($recentLogs as $log)
                                    <tr>
                                        <td class="py-3 text-gray-900 dark:text-white">{{ $log->email }}</td>
                                        <td class="py-3 text-gray-500 dark:text-gray-400">Step {{ $log->step?->sequence_order ?? '-' }}</td>
                                        <td class="py-3 text-center">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $log->getStatusColor() }}-100 text-{{ $log->getStatusColor() }}-800 dark:bg-{{ $log->getStatusColor() }}-900/30 dark:text-{{ $log->getStatusColor() }}-400">
                                                {{ $log->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right text-gray-500 dark:text-gray-400 text-sm">
                                            {{ $log->sent_at?->diffForHumans() ?? 'Pending' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistics</h3>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Sent</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_sent']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Opened</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ number_format($stats['opened']) }}
                            @if ($stats['total_sent'] > 0)
                                <span class="text-sm text-gray-500">({{ round(($stats['opened'] / $stats['total_sent']) * 100, 1) }}%)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Clicked</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ number_format($stats['clicked']) }}
                            @if ($stats['total_sent'] > 0)
                                <span class="text-sm text-gray-500">({{ round(($stats['clicked'] / $stats['total_sent']) * 100, 1) }}%)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Failed</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">{{ number_format($stats['failed']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>

                <div class="space-y-3">
                    <form method="POST" action="{{ route('backoffice.drip-campaigns.toggleStatus', $campaign) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $campaign->isActive() ? 'paused' : 'active' }}">
                        <button type="submit" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                            @if ($campaign->isActive())
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pause Campaign
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activate Campaign
                            @endif
                        </button>
                    </form>

                    <a href="{{ route('backoffice.drip-campaigns.logs', $campaign) }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        View All Logs
                    </a>

                    <form method="POST" action="{{ route('backoffice.drip-campaigns.destroy', $campaign) }}" onsubmit="return confirm('Are you sure you want to delete this campaign? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 font-medium rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Campaign
                        </button>
                    </form>
                </div>
            </div>

            <!-- Campaign Info -->
            @if ($campaign->creator)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Created By</h3>
                    <p class="text-gray-900 dark:text-white">{{ $campaign->creator->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $campaign->created_at->format('M j, Y \a\t g:i A') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
