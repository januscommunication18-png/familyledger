@extends('layouts.dashboard')

@section('title', $person ? 'Edit ' . $person->full_name : 'Add Contact')
@section('page-name', 'People Directory')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('people.index') }}" class="hover:text-primary">People Directory</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">{{ $person ? 'Edit Contact' : 'Add Contact' }}</li>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ $person ? route('people.show', $person) : route('people.index') }}" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $person ? 'Edit Contact' : 'Add New Contact' }}</h1>
                <p class="text-slate-500">Store contact information for people in your network</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ $person ? route('people.update', $person) : route('people.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($person)
            @method('PUT')
        @endif

        <!-- Basic Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Basic Information</h2>
                        <p class="text-xs text-slate-400">Personal details</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $person?->full_name) }}" required
                               class="input w-full" placeholder="e.g., John Smith" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nickname</label>
                        <input type="text" name="nickname" value="{{ old('nickname', $person?->nickname) }}"
                               class="input w-full" placeholder="e.g., Johnny" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Relationship <span class="text-rose-500">*</span></label>
                        <select name="relationship" id="relationship_select" required data-select='{
                            "placeholder": "Select relationship...",
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:select-active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }' class="hidden">
                            <option value="">Choose relationship</option>
                            @foreach($relationships as $key => $label)
                                <option value="{{ $key }}" {{ old('relationship', $person?->relationship) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                        <input type="text" name="company" value="{{ old('company', $person?->company) }}"
                               class="input w-full" placeholder="e.g., ABC Company" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Job Title</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $person?->job_title) }}"
                               class="input w-full" placeholder="e.g., Manager" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Birthday</label>
                        <div class="relative">
                            <input type="text" name="birthday" id="birthday"
                                   value="{{ old('birthday', $person?->birthday?->format('m/d/Y')) }}"
                                   class="input w-full" placeholder="MM/DD/YYYY" readonly />
                            <span class="absolute top-1/2 end-3 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">How We Know Each Other</label>
                        <input type="text" name="how_we_know" value="{{ old('how_we_know', $person?->how_we_know) }}"
                               class="input w-full" placeholder="e.g., Met at church, Kids' school" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tags</label>
                        <input type="text" name="tags_input"
                               value="{{ old('tags_input', $person?->tags ? implode(', ', $person->tags) : '') }}"
                               class="input w-full" placeholder="e.g., VIP, Neighbor, Emergency (comma separated)" />
                        <p class="text-xs text-slate-400 mt-1">Separate tags with commas</p>
                    </div>

                    <!-- Profile Photo -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Profile Photo</label>
                        @if($person?->profile_image_url)
                            <div id="profile-preview" class="mb-3 p-2 bg-slate-50 rounded-lg border border-slate-200">
                                <img src="{{ $person->profile_image_url }}" alt="Profile" class="max-h-32 rounded mx-auto" id="profile-preview-img" />
                            </div>
                        @else
                            <div id="profile-preview" class="mb-3 p-2 bg-slate-50 rounded-lg border border-slate-200 hidden">
                                <img src="" alt="Profile" class="max-h-32 rounded mx-auto" id="profile-preview-img" />
                            </div>
                        @endif
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 hover:border-violet-400 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <p class="text-sm text-slate-500"><span class="font-medium text-violet-600">Click to upload</span></p>
                                <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                            </div>
                            <input type="file" name="profile_image" id="profile_image_input" accept="image/*" class="hidden" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Contact Information</h2>
                        <p class="text-xs text-slate-400">Email addresses and phone numbers</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Emails -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-slate-700">Email Addresses</label>
                            <button type="button" onclick="addEmail()" class="btn btn-xs btn-ghost text-primary gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add
                            </button>
                        </div>
                        <div id="emails-container" class="space-y-2">
                            @if($person && $person->emails->count() > 0)
                                @foreach($person->emails as $index => $email)
                                    <div class="email-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                        <button type="button" onclick="this.closest('.email-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                        <input type="email" name="emails[{{ $index }}][email]" value="{{ $email->email }}"
                                               class="input input-sm w-full" placeholder="email@example.com">
                                        <select name="emails[{{ $index }}][label]" class="select select-sm w-full">
                                            @foreach($emailLabels as $key => $label)
                                                <option value="{{ $key }}" {{ $email->label === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            @else
                                <div class="email-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                    <button type="button" onclick="this.closest('.email-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                    <input type="email" name="emails[0][email]" class="input input-sm w-full" placeholder="email@example.com">
                                    <select name="emails[0][label]" class="select select-sm w-full">
                                        @foreach($emailLabels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Phones -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-slate-700">Phone Numbers</label>
                            <button type="button" onclick="addPhone()" class="btn btn-xs btn-ghost text-primary gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                Add
                            </button>
                        </div>
                        <div id="phones-container" class="space-y-2">
                            @if($person && $person->phones->count() > 0)
                                @foreach($person->phones as $index => $phone)
                                    <div class="phone-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                        <button type="button" onclick="this.closest('.phone-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                        <input type="tel" name="phones[{{ $index }}][phone]" value="{{ $phone->phone }}"
                                               class="input input-sm w-full" placeholder="(555) 123-4567">
                                        <select name="phones[{{ $index }}][label]" class="select select-sm w-full">
                                            @foreach($phoneLabels as $key => $label)
                                                <option value="{{ $key }}" {{ $phone->label === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            @else
                                <div class="phone-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                    <button type="button" onclick="this.closest('.phone-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                    <input type="tel" name="phones[0][phone]" class="input input-sm w-full" placeholder="(555) 123-4567">
                                    <select name="phones[0][label]" class="select select-sm w-full">
                                        @foreach($phoneLabels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Addresses</h2>
                            <p class="text-xs text-slate-400">Physical addresses</p>
                        </div>
                    </div>
                    <button type="button" onclick="addAddress()" class="btn btn-xs btn-ghost text-primary gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add
                    </button>
                </div>

                <div id="addresses-container" class="space-y-4">
                    @if($person && $person->addresses->count() > 0)
                        @foreach($person->addresses as $index => $address)
                            <div class="address-row p-3 bg-slate-50 rounded-lg relative">
                                <button type="button" onclick="this.closest('.address-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="col-span-2">
                                        <select name="addresses[{{ $index }}][label]" class="select select-sm w-full">
                                            @foreach($addressLabels as $key => $label)
                                                <option value="{{ $key }}" {{ $address->label === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="text" name="addresses[{{ $index }}][street_address]" value="{{ $address->street_address }}" class="input input-sm w-full" placeholder="Street address">
                                    </div>
                                    <input type="text" name="addresses[{{ $index }}][city]" value="{{ $address->city }}" class="input input-sm" placeholder="City">
                                    <input type="text" name="addresses[{{ $index }}][state]" value="{{ $address->state }}" class="input input-sm" placeholder="State">
                                    <input type="text" name="addresses[{{ $index }}][zip_code]" value="{{ $address->zip_code }}" class="input input-sm" placeholder="ZIP">
                                    <input type="text" name="addresses[{{ $index }}][country]" value="{{ $address->country }}" class="input input-sm" placeholder="Country">
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-600"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Links</h2>
                            <p class="text-xs text-slate-400">Website, social media</p>
                        </div>
                    </div>
                    <button type="button" onclick="addLink()" class="btn btn-xs btn-ghost text-primary gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add
                    </button>
                </div>

                <div id="links-container" class="space-y-2">
                    @if($person && $person->links->count() > 0)
                        @foreach($person->links as $index => $link)
                            <div class="link-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                <button type="button" onclick="this.closest('.link-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                                <select name="links[{{ $index }}][label]" class="select select-sm w-full">
                                    @foreach($linkLabels as $key => $label)
                                        <option value="{{ $key }}" {{ $link->label === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="url" name="links[{{ $index }}][url]" value="{{ $link->url }}"
                                       class="input input-sm w-full" placeholder="https://example.com">
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Important Dates -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Important Dates</h2>
                            <p class="text-xs text-slate-400">Anniversaries, renewals, etc.</p>
                        </div>
                    </div>
                    <button type="button" onclick="addImportantDate()" class="btn btn-xs btn-ghost text-primary gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add
                    </button>
                </div>

                <div id="important-dates-container" class="space-y-2">
                    @if($person && $person->importantDates->count() > 0)
                        @foreach($person->importantDates as $index => $date)
                            <div class="date-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                                <button type="button" onclick="this.closest('.date-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                                <input type="text" name="important_dates[{{ $index }}][label]" value="{{ $date->label }}"
                                       class="input input-sm w-full" placeholder="Label (e.g., Anniversary)">
                                <input type="text" name="important_dates[{{ $index }}][date]" value="{{ $date->date->format('m/d/Y') }}"
                                       class="input input-sm w-full date-picker" placeholder="MM/DD/YYYY" readonly>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="important_dates[{{ $index }}][recurring_yearly]" value="1"
                                           class="checkbox checkbox-sm checkbox-primary" {{ $date->recurring_yearly ? 'checked' : '' }}>
                                    <span class="text-sm text-slate-600">Recurring yearly</span>
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Notes</h2>
                        <p class="text-xs text-slate-400">Additional information</p>
                    </div>
                </div>

                <textarea name="notes" rows="3" class="textarea textarea-bordered w-full"
                          placeholder="Any additional notes about this person...">{{ old('notes', $person?->notes) }}</textarea>
            </div>
        </div>

        <!-- Privacy -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Privacy</h2>
                        <p class="text-xs text-slate-400">Control who can see this contact</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($visibilities as $key => $label)
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors">
                            <input type="radio" name="visibility" value="{{ $key }}"
                                   class="radio radio-sm radio-primary visibility-radio"
                                   {{ old('visibility', $person?->visibility ?? 'family') === $key ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium text-slate-800 text-sm">{{ $label }}</span>
                                <p class="text-xs text-slate-500">
                                    @if($key === 'family')
                                        All family members can see
                                    @elseif($key === 'specific')
                                        Only selected members
                                    @else
                                        Only you
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <!-- Family Members Selection -->
                <div id="specificMembersContainer" class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 {{ old('visibility', $person?->visibility ?? 'family') === 'specific' ? '' : 'hidden' }}">
                    <p class="text-sm font-medium text-slate-700 mb-2">Select members:</p>
                    @if($familyMembers->count() > 0)
                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                            @php
                                $selectedMembers = old('visible_to_members', $person?->visible_to_members ?? []);
                                if (is_string($selectedMembers)) {
                                    $selectedMembers = json_decode($selectedMembers, true) ?? [];
                                }
                            @endphp
                            @foreach($familyMembers as $member)
                                <label class="flex items-center gap-2 p-2 bg-white rounded border border-slate-200 hover:border-primary cursor-pointer text-sm">
                                    <input type="checkbox" name="visible_to_members[]" value="{{ $member->id }}"
                                           class="checkbox checkbox-xs checkbox-primary"
                                           {{ in_array($member->id, $selectedMembers) ? 'checked' : '' }}>
                                    <span class="truncate">{{ $member->first_name }} {{ $member->last_name }}</span>
                                    @if($member->familyCircle)
                                        <span class="text-xs text-slate-400 truncate">({{ $member->familyCircle->name }})</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500 text-center py-2">No family members found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attachments -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-600"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Attachments</h2>
                        <p class="text-xs text-slate-400">Business cards, documents, vCards</p>
                    </div>
                </div>

                <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer bg-slate-50 hover:bg-orange-50 hover:border-orange-400 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-2 pb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-400 mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <p class="text-sm text-slate-500"><span class="font-medium text-orange-600">Click to upload</span></p>
                        <p class="text-xs text-slate-400">PDF, JPG, PNG, VCF (max 10MB)</p>
                    </div>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.vcf" class="hidden" />
                </label>

                @if($person && $person->attachments->count() > 0)
                    <div class="mt-4 space-y-2">
                        <p class="text-sm font-medium text-slate-700">Existing:</p>
                        @foreach($person->attachments as $attachment)
                            <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <span class="text-sm truncate">{{ $attachment->original_filename }}</span>
                                    <span class="badge badge-xs">{{ $attachment->formatted_file_size }}</span>
                                </div>
                                <form action="{{ route('people.attachments.delete', [$person, $attachment]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error" onclick="return confirm('Delete?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-start gap-3">
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $person ? 'Update Contact' : 'Save Contact' }}
            </button>
            <a href="{{ $person ? route('people.show', $person) : route('people.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    let emailIndex = {{ $person ? $person->emails->count() : 1 }};
    let phoneIndex = {{ $person ? $person->phones->count() : 1 }};
    let addressIndex = {{ $person ? $person->addresses->count() : 0 }};
    let linkIndex = {{ $person ? $person->links->count() : 0 }};
    let dateIndex = {{ $person ? $person->importantDates->count() : 0 }};

    const emailLabels = @json($emailLabels);
    const phoneLabels = @json($phoneLabels);
    const addressLabels = @json($addressLabels);
    const linkLabels = @json($linkLabels);

    function addEmail() {
        const container = document.getElementById('emails-container');
        const html = `
            <div class="email-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                <button type="button" onclick="this.closest('.email-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
                <input type="email" name="emails[${emailIndex}][email]" class="input input-sm w-full" placeholder="email@example.com">
                <select name="emails[${emailIndex}][label]" class="select select-sm w-full">
                    ${Object.entries(emailLabels).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                </select>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        emailIndex++;
    }

    function addPhone() {
        const container = document.getElementById('phones-container');
        const html = `
            <div class="phone-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                <button type="button" onclick="this.closest('.phone-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
                <input type="tel" name="phones[${phoneIndex}][phone]" class="input input-sm w-full" placeholder="(555) 123-4567">
                <select name="phones[${phoneIndex}][label]" class="select select-sm w-full">
                    ${Object.entries(phoneLabels).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                </select>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        phoneIndex++;
    }

    function addAddress() {
        const container = document.getElementById('addresses-container');
        const html = `
            <div class="address-row p-3 bg-slate-50 rounded-lg relative">
                <button type="button" onclick="this.closest('.address-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-2">
                        <select name="addresses[${addressIndex}][label]" class="select select-sm w-full">
                            ${Object.entries(addressLabels).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="addresses[${addressIndex}][street_address]" class="input input-sm w-full" placeholder="Street address">
                    </div>
                    <input type="text" name="addresses[${addressIndex}][city]" class="input input-sm" placeholder="City">
                    <input type="text" name="addresses[${addressIndex}][state]" class="input input-sm" placeholder="State">
                    <input type="text" name="addresses[${addressIndex}][zip_code]" class="input input-sm" placeholder="ZIP">
                    <input type="text" name="addresses[${addressIndex}][country]" class="input input-sm" placeholder="Country">
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        addressIndex++;
    }

    function addLink() {
        const container = document.getElementById('links-container');
        const html = `
            <div class="link-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                <button type="button" onclick="this.closest('.link-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
                <select name="links[${linkIndex}][label]" class="select select-sm w-full">
                    ${Object.entries(linkLabels).map(([k, v]) => `<option value="${k}">${v}</option>`).join('')}
                </select>
                <input type="url" name="links[${linkIndex}][url]" class="input input-sm w-full" placeholder="https://example.com">
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        linkIndex++;
    }

    function addImportantDate() {
        const container = document.getElementById('important-dates-container');
        const html = `
            <div class="date-row space-y-2 p-3 bg-slate-50 rounded-lg relative">
                <button type="button" onclick="this.closest('.date-row').remove()" class="absolute top-2 right-2 btn btn-ghost btn-xs btn-square text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
                <input type="text" name="important_dates[${dateIndex}][label]" class="input input-sm w-full" placeholder="Label (e.g., Anniversary)">
                <input type="text" name="important_dates[${dateIndex}][date]" class="input input-sm w-full date-picker" placeholder="MM/DD/YYYY" readonly>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="important_dates[${dateIndex}][recurring_yearly]" value="1" class="checkbox checkbox-sm checkbox-primary">
                    <span class="text-sm text-slate-600">Recurring yearly</span>
                </label>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        dateIndex++;
        initDatePickers();
    }

    // Initialize Flatpickr
    document.addEventListener('DOMContentLoaded', function() {
        const dateConfig = {
            dateFormat: 'm/d/Y',
            allowInput: true,
            disableMobile: true
        };

        // Birthday
        flatpickr('#birthday', dateConfig);

        // Important dates
        initDatePickers();

        // Profile image preview
        const profileInput = document.getElementById('profile_image_input');
        if (profileInput) {
            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profile-preview-img').src = e.target.result;
                        document.getElementById('profile-preview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Visibility toggle
        const visibilityRadios = document.querySelectorAll('.visibility-radio');
        const specificContainer = document.getElementById('specificMembersContainer');

        visibilityRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'specific') {
                    specificContainer.classList.remove('hidden');
                } else {
                    specificContainer.classList.add('hidden');
                }
            });
        });
    });

    function initDatePickers() {
        document.querySelectorAll('.date-picker').forEach(el => {
            if (!el._flatpickr) {
                flatpickr(el, {
                    dateFormat: 'm/d/Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    }
</script>
@endsection
