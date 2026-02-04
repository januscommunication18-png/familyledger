@extends('backoffice.layouts.app')

@php
    $header = 'Email Details';
@endphp

@section('content')
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('backoffice.email-logs.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Email Logs
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Email Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status</h3>

                <div class="flex items-center gap-3 mb-4">
                    <span class="px-3 py-1.5 text-sm font-medium rounded-full
                        @if ($emailLog->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif ($emailLog->status === 'opened') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif ($emailLog->status === 'clicked') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400
                        @elseif ($emailLog->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @elseif ($emailLog->status === 'bounced') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400
                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                        @endif">
                        {{ $emailLog->getStatusLabel() }}
                    </span>
                </div>

                @if ($emailLog->error_message)
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-300">{{ $emailLog->error_message }}</p>
                    </div>
                @endif

                <div class="space-y-3 mt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Created</span>
                        <span class="text-gray-900 dark:text-white">{{ $emailLog->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if ($emailLog->sent_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Sent</span>
                            <span class="text-gray-900 dark:text-white">{{ $emailLog->sent_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                    @if ($emailLog->opened_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Opened</span>
                            <span class="text-green-600 dark:text-green-400">{{ $emailLog->opened_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                    @if ($emailLog->clicked_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Clicked</span>
                            <span class="text-purple-600 dark:text-purple-400">{{ $emailLog->clicked_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Email Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Details</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->getMailableTypeLabel() }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">To</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->to_email }}</p>
                        @if ($emailLog->to_name)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $emailLog->to_name }}</p>
                        @endif
                    </div>

                    @if ($emailLog->from_email)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">From</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->from_email }}</p>
                            @if ($emailLog->from_name)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $emailLog->from_name }}</p>
                            @endif
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Subject</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->subject }}</p>
                    </div>

                    @if ($emailLog->message_id)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Message ID</p>
                            <p class="text-xs text-gray-700 dark:text-gray-300 font-mono break-all">{{ $emailLog->message_id }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User/Tenant Info -->
            @if ($emailLog->user || $emailLog->tenant)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Related Info</h3>

                    <div class="space-y-4">
                        @if ($emailLog->user)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                                <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->user->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $emailLog->user->email }}</p>
                            </div>
                        @endif

                        @if ($emailLog->tenant)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tenant</p>
                                <p class="text-gray-900 dark:text-white font-medium">{{ $emailLog->tenant->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $emailLog->tenant_id }}</p>
                            </div>
                        @endif

                        @if ($emailLog->metadata)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Metadata</p>
                                <pre class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg overflow-x-auto">{{ json_encode($emailLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Email Content -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email Content</h3>
                </div>

                @if ($emailLog->body_html)
                    <div class="p-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">HTML Preview</p>
                            </div>
                            <iframe
                                srcdoc="{{ $emailLog->body_html }}"
                                class="w-full bg-white"
                                style="min-height: 500px; border: none;"
                                sandbox="allow-same-origin"
                            ></iframe>
                        </div>
                    </div>
                @endif

                @if ($emailLog->body_text)
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Plain Text Version</p>
                        <pre class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg overflow-x-auto whitespace-pre-wrap">{{ $emailLog->body_text }}</pre>
                    </div>
                @endif

                @if (!$emailLog->body_html && !$emailLog->body_text)
                    <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2">Email content not available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
