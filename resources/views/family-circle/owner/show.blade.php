@extends('layouts.dashboard')

@php
    $ownerNameParts = explode(' ', $owner->name, 2);
    $firstName = $ownerNameParts[0];
    $lastName = $ownerNameParts[1] ?? '';
    $age = $owner->date_of_birth ? \Carbon\Carbon::parse($owner->date_of_birth)->age : null;
    // Get the self member record for this circle
    $selfMember = $circle->members->where('relationship', 'self')->where('linked_user_id', auth()->id())->first();
@endphp

@section('title', $owner->name)
@section('page-name', $owner->name)

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.index') }}" class="hover:text-violet-600">Family Circle</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('family-circle.show', $circle) }}" class="hover:text-violet-600">{{ $circle->name }}</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $owner->name }}</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Personal Information Card -->
    <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Personal Information</h2>
                        <p class="text-xs text-slate-400">Account owner details</p>
                    </div>
                </div>
                @if($selfMember)
                    <a href="{{ route('family-circle.member.edit', [$circle, $selfMember]) }}" class="btn btn-sm btn-ghost gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        Edit
                    </a>
                @else
                    <a href="{{ route('settings.index') }}" class="btn btn-sm btn-ghost gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        Edit
                    </a>
                @endif
            </div>

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Profile Photo -->
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg overflow-hidden">
                        @if($owner->profile_image)
                            <img src="{{ Storage::disk('do_spaces')->url($owner->profile_image) }}" alt="{{ $owner->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ strtoupper(substr($firstName, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1 mt-3 justify-center">
                        <span class="badge badge-primary badge-sm">Account Owner</span>
                    </div>
                </div>

                <!-- Profile Info Grid -->
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Full Name -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Full Name</p>
                        <p class="font-semibold text-slate-800 flex items-center gap-2">
                            {{ $owner->name }}
                            <span class="badge badge-sm bg-violet-100 text-violet-700 border-0">You</span>
                        </p>
                        <p class="text-xs text-slate-500">Self</p>
                    </div>

                    <!-- Date of Birth -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Date of Birth</p>
                        @if($owner->date_of_birth)
                            <p class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($owner->date_of_birth)->format('M d, Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $age }} years old</p>
                        @else
                            <p class="text-sm text-slate-400 italic">Not specified</p>
                        @endif
                    </div>

                    <!-- Email -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Email</p>
                        @if($owner->email)
                            <p class="font-semibold text-slate-800 truncate">{{ $owner->email }}</p>
                            @if($owner->email_verified_at)
                                <p class="text-xs text-emerald-600">Verified</p>
                            @else
                                <p class="text-xs text-amber-600">Not verified</p>
                            @endif
                        @else
                            <p class="text-sm text-slate-400 italic">Not specified</p>
                        @endif
                    </div>

                    <!-- Phone -->
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Phone</p>
                        @if($owner->phone)
                            <p class="font-semibold text-slate-800">{{ $owner->phone }}</p>
                            @if($owner->phone_verified_at)
                                <p class="text-xs text-emerald-600">Verified</p>
                            @else
                                <p class="text-xs text-amber-600">Not verified</p>
                            @endif
                        @else
                            <p class="text-sm text-slate-400 italic">Not specified</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links - 4 in a row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Documents -->
        <a href="{{ route('documents.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Documents</h3>
                <p class="text-xs text-slate-500 mt-1">View all your identity documents</p>
            </div>
        </a>

        <!-- Legal Documents -->
        <a href="{{ route('legal.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Legal Documents</h3>
                <p class="text-xs text-slate-500 mt-1">Wills, trusts & legal papers</p>
            </div>
        </a>

        <!-- Family Resources -->
        <a href="{{ route('family-resources.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Family Resources</h3>
                <p class="text-xs text-slate-500 mt-1">Recipes, traditions & more</p>
            </div>
        </a>

        <!-- Settings -->
        <a href="{{ route('settings.index') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Settings</h3>
                <p class="text-xs text-slate-500 mt-1">Edit profile & preferences</p>
            </div>
        </a>
    </div>

    <!-- Insurance, Tax Returns & Assets Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Insurance Policies Card -->
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Insurance Policies</h3>
                    </div>
                    <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost btn-xs text-blue-600">
                        View All
                    </a>
                </div>

                @if($insurancePolicies && $insurancePolicies->count() > 0)
                    <div class="space-y-2">
                        @foreach($insurancePolicies->take(3) as $policy)
                            <a href="{{ route('documents.insurance.show', $policy) }}" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <span class="{{ $policy->getTypeIcon() }} size-4 text-blue-600"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $policy->provider_name }}</p>
                                        <p class="text-xs text-slate-400">{{ \App\Models\InsurancePolicy::INSURANCE_TYPES[$policy->insurance_type] ?? $policy->insurance_type }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm badge-{{ $policy->getStatusColor() }}">{{ \App\Models\InsurancePolicy::STATUSES[$policy->status] ?? $policy->status }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                                </div>
                            </a>
                        @endforeach
                        @if($insurancePolicies->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $insurancePolicies->count() - 3 }} more policies</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No insurance policies linked</p>
                        <a href="{{ route('documents.insurance.create') }}" class="btn btn-xs btn-primary gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add Insurance
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tax Returns Card -->
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Tax Returns</h3>
                    </div>
                    <a href="{{ route('documents.index', ['tab' => 'tax-returns']) }}" class="btn btn-ghost btn-xs text-emerald-600">
                        View All
                    </a>
                </div>

                @if($taxReturns && $taxReturns->count() > 0)
                    <div class="space-y-2">
                        @foreach($taxReturns->sortByDesc('tax_year')->take(3) as $taxReturn)
                            <a href="{{ route('documents.tax-returns.show', $taxReturn) }}" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <span class="text-emerald-700 font-bold text-xs">{{ substr($taxReturn->tax_year, -2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $taxReturn->tax_year }} Tax Return</p>
                                        <p class="text-xs text-slate-400">{{ \App\Models\TaxReturn::JURISDICTIONS[$taxReturn->tax_jurisdiction] ?? $taxReturn->tax_jurisdiction }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm badge-{{ $taxReturn->getStatusColor() }}">{{ \App\Models\TaxReturn::STATUSES[$taxReturn->status] ?? $taxReturn->status }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                                </div>
                            </a>
                        @endforeach
                        @if($taxReturns->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $taxReturns->count() - 3 }} more returns</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No tax returns linked</p>
                        <a href="{{ route('documents.tax-returns.create') }}" class="btn btn-xs btn-primary gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add Tax Return
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Assets Card -->
        <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Assets</h3>
                    </div>
                    <a href="{{ route('assets.index') }}" class="btn btn-ghost btn-xs text-amber-600">
                        View All
                    </a>
                </div>

                @if($assets && $assets->count() > 0)
                    <div class="space-y-2">
                        @foreach($assets->take(3) as $asset)
                            <a href="{{ route('assets.show', $asset) }}" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <span class="{{ $asset->getCategoryIcon() }} size-4 text-amber-600"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ Str::limit($asset->name, 20) }}</p>
                                        <p class="text-xs text-slate-400">{{ $asset->category_name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($asset->current_value)
                                        <span class="text-xs font-medium text-emerald-600">{{ $asset->formatted_current_value }}</span>
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 opacity-0 group-hover:opacity-100"><path d="m9 18 6-6-6-6"/></svg>
                                </div>
                            </a>
                        @endforeach
                        @if($assets->count() > 3)
                            <p class="text-xs text-slate-400 text-center pt-1">+{{ $assets->count() - 3 }} more assets</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-xs text-slate-400 mb-2">No assets linked</p>
                        <a href="{{ route('assets.create') }}" class="btn btn-xs btn-primary gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add Asset
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Account Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Security Status -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Security Status</h3>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Email Verification</span>
                        @if($owner->email_verified_at)
                            <span class="badge badge-sm badge-success">Verified</span>
                        @else
                            <span class="badge badge-sm badge-warning">Pending</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Phone Verification</span>
                        @if($owner->phone_verified_at)
                            <span class="badge badge-sm badge-success">Verified</span>
                        @else
                            <span class="badge badge-sm badge-warning">Not Set</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Two-Factor Auth</span>
                        @if($owner->mfa_enabled)
                            <span class="badge badge-sm badge-success">Enabled</span>
                        @else
                            <span class="badge badge-sm badge-ghost">Disabled</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Activity -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Account Activity</h3>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Account Created</span>
                        <span class="text-sm font-medium text-slate-800">{{ $owner->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($owner->last_login_at)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Last Login</span>
                        <span class="text-sm font-medium text-slate-800">{{ $owner->last_login_at->diffForHumans() }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="text-sm text-slate-600">Auth Method</span>
                        <span class="text-sm font-medium text-slate-800 capitalize">{{ $owner->auth_provider ?? 'Email' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
