@extends('backoffice.layouts.app')

@php
    $header = 'Client Data';
@endphp

@section('content')
    <div x-data="{ activeTab: 'users' }">
        <!-- Warning Banner -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Secure Viewing Session</h4>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400">
                        You are viewing sensitive client data. This session is being logged.
                        Access expires: <strong>{{ $activeRequest->access_expires_at->format('M j, Y g:i A') }}</strong>
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
                    <p class="text-gray-500 dark:text-gray-400">ID: {{ $client->id }}</p>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
            <nav class="flex gap-1 min-w-max">
                @php
                    $tabs = [
                        'users' => ['label' => 'Users', 'count' => $users->count()],
                        'family' => ['label' => 'Family Members', 'count' => $familyMembers->count()],
                        'pets' => ['label' => 'Pets', 'count' => $pets->count()],
                        'assets' => ['label' => 'Assets', 'count' => $assets->count()],
                        'insurance' => ['label' => 'Insurance', 'count' => $insurancePolicies->count()],
                        'legal' => ['label' => 'Legal Docs', 'count' => $legalDocuments->count()],
                        'tax' => ['label' => 'Tax Returns', 'count' => $taxReturns->count()],
                        'budgets' => ['label' => 'Budgets', 'count' => $budgets->count()],
                        'goals' => ['label' => 'Goals', 'count' => $goals->count()],
                        'contacts' => ['label' => 'Contacts', 'count' => $persons->count()],
                        'journal' => ['label' => 'Journal', 'count' => $journalEntries->count()],
                        'invoices' => ['label' => 'Invoices', 'count' => $invoices->count()],
                    ];
                @endphp
                @foreach($tabs as $key => $tab)
                    <button
                        @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'border-primary-600 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="px-3 py-2 text-sm font-medium border-b-2 -mb-px transition-colors rounded-t-lg"
                    >
                        {{ $tab['label'] }} ({{ $tab['count'] }})
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Users Tab -->
        <div x-show="activeTab === 'users'">
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['User', 'Email', 'Phone', 'Created', 'Last Login'],
                'emptyMessage' => 'No users found',
                'items' => $users,
                'rowTemplate' => 'users'
            ])
        </div>

        <!-- Family Members Tab -->
        <div x-show="activeTab === 'family'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Name', 'Relationship', 'Date of Birth', 'Gender', 'Created'],
                'emptyMessage' => 'No family members found',
                'items' => $familyMembers,
                'rowTemplate' => 'family'
            ])
        </div>

        <!-- Pets Tab -->
        <div x-show="activeTab === 'pets'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Name', 'Species', 'Breed', 'Date of Birth', 'Created'],
                'emptyMessage' => 'No pets found',
                'items' => $pets,
                'rowTemplate' => 'pets'
            ])
        </div>

        <!-- Assets Tab -->
        <div x-show="activeTab === 'assets'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Name', 'Type', 'Value', 'Location', 'Created'],
                'emptyMessage' => 'No assets found',
                'items' => $assets,
                'rowTemplate' => 'assets'
            ])
        </div>

        <!-- Insurance Tab -->
        <div x-show="activeTab === 'insurance'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Policy Name', 'Type', 'Provider', 'Premium', 'Expiry'],
                'emptyMessage' => 'No insurance policies found',
                'items' => $insurancePolicies,
                'rowTemplate' => 'insurance'
            ])
        </div>

        <!-- Legal Documents Tab -->
        <div x-show="activeTab === 'legal'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Document Name', 'Type', 'Status', 'Effective Date', 'Created'],
                'emptyMessage' => 'No legal documents found',
                'items' => $legalDocuments,
                'rowTemplate' => 'legal'
            ])
        </div>

        <!-- Tax Returns Tab -->
        <div x-show="activeTab === 'tax'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Tax Year', 'Status', 'Filing Date', 'Refund/Owed', 'Created'],
                'emptyMessage' => 'No tax returns found',
                'items' => $taxReturns,
                'rowTemplate' => 'tax'
            ])
        </div>

        <!-- Budgets Tab -->
        <div x-show="activeTab === 'budgets'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Name', 'Period', 'Amount', 'Spent', 'Created'],
                'emptyMessage' => 'No budgets found',
                'items' => $budgets,
                'rowTemplate' => 'budgets'
            ])
        </div>

        <!-- Goals Tab -->
        <div x-show="activeTab === 'goals'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Title', 'Category', 'Target', 'Progress', 'Due Date'],
                'emptyMessage' => 'No goals found',
                'items' => $goals,
                'rowTemplate' => 'goals'
            ])
        </div>

        <!-- Contacts Tab -->
        <div x-show="activeTab === 'contacts'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Name', 'Type', 'Company', 'Email', 'Phone'],
                'emptyMessage' => 'No contacts found',
                'items' => $persons,
                'rowTemplate' => 'contacts'
            ])
        </div>

        <!-- Journal Tab -->
        <div x-show="activeTab === 'journal'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Title', 'Type', 'Mood', 'Date', 'Attachments'],
                'emptyMessage' => 'No journal entries found',
                'items' => $journalEntries,
                'rowTemplate' => 'journal'
            ])
        </div>

        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'" x-cloak>
            @include('backoffice.clients.partials.data-table', [
                'headers' => ['Invoice #', 'Amount', 'Status', 'Due Date', 'Paid At'],
                'emptyMessage' => 'No invoices found',
                'items' => $invoices,
                'rowTemplate' => 'invoices'
            ])
        </div>
    </div>
@endsection
