@extends('layouts.dashboard')

@section('title', 'Insurance Policy Details')
@section('page-name', 'Documents')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="hover:text-primary">Documents</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $insurance->provider_name }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $insurance->provider_name }}</h1>
                    <p class="text-slate-500">{{ $insuranceTypes[$insurance->insurance_type] ?? $insurance->insurance_type }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('documents.insurance.edit', $insurance) }}" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center gap-3">
        <span class="badge badge-lg badge-{{ $insurance->getStatusColor() }}">{{ $statuses[$insurance->status] ?? $insurance->status }}</span>
        @if($insurance->isExpiringSoon())
            <span class="badge badge-lg badge-warning gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                Expiring Soon
            </span>
        @endif
    </div>

    <!-- Policy Information -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Policy Information</h2>
                    <p class="text-xs text-slate-400">Basic insurance policy details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Insurance Type</label>
                    <p class="text-slate-900">{{ $insuranceTypes[$insurance->insurance_type] ?? $insurance->insurance_type }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Provider Name</label>
                    <p class="text-slate-900">{{ $insurance->provider_name }}</p>
                </div>

                @if($insurance->policy_number)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Policy Number</label>
                    <p class="text-slate-900 font-mono">{{ $insurance->policy_number }}</p>
                </div>
                @endif

                @if($insurance->group_number)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Group Number</label>
                    <p class="text-slate-900 font-mono">{{ $insurance->group_number }}</p>
                </div>
                @endif

                @if($insurance->plan_name)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Plan Name</label>
                    <p class="text-slate-900">{{ $insurance->plan_name }}</p>
                </div>
                @endif

                @if($insurance->policyholders->count() > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Policyholder(s)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($insurance->policyholders as $policyholder)
                            <span class="badge badge-outline">{{ $policyholder->first_name }} {{ $policyholder->last_name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($insurance->coveredMembers->count() > 0)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-500 mb-1">Covered Members</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($insurance->coveredMembers as $member)
                            <span class="badge badge-outline">{{ $member->first_name }} {{ $member->last_name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Dates & Payments -->
    @if($insurance->effective_date || $insurance->expiration_date || $insurance->premium_amount)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Dates & Payments</h2>
                    <p class="text-xs text-slate-400">Coverage period and premium information</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($insurance->effective_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Effective Date</label>
                    <p class="text-slate-900">{{ $insurance->effective_date->format('F j, Y') }}</p>
                </div>
                @endif

                @if($insurance->expiration_date)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Expiration Date</label>
                    <p class="text-slate-900 {{ $insurance->isExpiringSoon() ? 'text-warning font-semibold' : '' }}">
                        {{ $insurance->expiration_date->format('F j, Y') }}
                        @if($insurance->isExpiringSoon())
                            <span class="text-warning text-sm">(Expiring soon)</span>
                        @endif
                    </p>
                </div>
                @endif

                @if($insurance->premium_amount)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Premium Amount</label>
                    <p class="text-slate-900 text-lg font-semibold">${{ number_format($insurance->premium_amount, 2) }}</p>
                </div>
                @endif

                @if($insurance->payment_frequency)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Payment Frequency</label>
                    <p class="text-slate-900">{{ $paymentFrequencies[$insurance->payment_frequency] ?? $insurance->payment_frequency }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Agent / Contact Information -->
    @if($insurance->agent_name || $insurance->agent_phone || $insurance->agent_email || $insurance->claims_phone)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Contact Information</h2>
                    <p class="text-xs text-slate-400">Agent and claims contact details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($insurance->agent_name)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Agent Name</label>
                    <p class="text-slate-900">{{ $insurance->agent_name }}</p>
                </div>
                @endif

                @if($insurance->agent_phone)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Agent Phone</label>
                    <a href="tel:{{ $insurance->agent_phone }}" class="text-primary hover:underline">{{ $insurance->agent_phone }}</a>
                </div>
                @endif

                @if($insurance->agent_email)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Agent Email</label>
                    <a href="mailto:{{ $insurance->agent_email }}" class="text-primary hover:underline">{{ $insurance->agent_email }}</a>
                </div>
                @endif

                @if($insurance->claims_phone)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Claims Phone</label>
                    <a href="tel:{{ $insurance->claims_phone }}" class="text-primary hover:underline">{{ $insurance->claims_phone }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Insurance Card Images -->
    @if($insurance->card_front_image || $insurance->card_back_image)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Insurance Card</h2>
                    <p class="text-xs text-slate-400">Your insurance card images</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($insurance->card_front_image)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Front of Card</label>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <x-protected-image
                            :src="route('documents.insurance.card', [$insurance, 'front'])"
                            alt="Insurance Card Front"
                            class="max-w-full rounded cursor-pointer hover:opacity-90 transition-opacity"
                            container-class="w-full"
                        />
                    </div>
                </div>
                @endif

                @if($insurance->card_back_image)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-2">Back of Card</label>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <x-protected-image
                            :src="route('documents.insurance.card', [$insurance, 'back'])"
                            alt="Insurance Card Back"
                            class="max-w-full rounded cursor-pointer hover:opacity-90 transition-opacity"
                            container-class="w-full"
                        />
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Information -->
    @if($insurance->coverage_details || $insurance->notes)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/><path d="M15 3v4a2 2 0 0 0 2 2h4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Additional Information</h2>
                    <p class="text-xs text-slate-400">Coverage details and notes</p>
                </div>
            </div>

            <div class="space-y-4">
                @if($insurance->coverage_details)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Coverage Details</label>
                    <div class="p-3 bg-slate-50 rounded-lg text-slate-700 whitespace-pre-wrap">{{ $insurance->coverage_details }}</div>
                </div>
                @endif

                @if($insurance->notes)
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Notes</label>
                    <div class="p-3 bg-slate-50 rounded-lg text-slate-700 whitespace-pre-wrap">{{ $insurance->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex justify-start gap-3">
        <a href="{{ route('documents.insurance.edit', $insurance) }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            Edit Policy
        </a>
        <a href="{{ route('documents.index', ['tab' => 'insurance']) }}" class="btn btn-ghost">Back to Documents</a>
    </div>
</div>

<!-- Image Modal -->
<dialog id="imageModal" class="modal">
    <div class="modal-box max-w-4xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4" id="modalTitle">Insurance Card</h3>
        <img id="modalImage" src="" alt="Insurance Card" class="w-full rounded-lg" />
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openImageModal(src, title) {
        document.getElementById('modalImage').src = src;
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('imageModal').showModal();
    }
</script>
@endsection
