@extends('layouts.dashboard')

@section('title', 'Settings')
@section('page-name', 'Settings')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Settings</li>
@endsection

@section('page-title', 'Settings')
@section('page-description', 'Manage your account and application preferences.')

@section('content')
<div class="space-y-6">
    <!-- Settings Navigation Tabs -->
    <div class="bg-base-100 rounded-xl shadow-sm">
        <div class="border-b border-base-200">
            <ul class="flex flex-wrap gap-1 px-4 -mb-px overflow-x-auto">
                <li>
                    <a href="#profile" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="#security" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Security
                    </a>
                </li>
                <li>
                    <a href="#notifications" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        Notifications
                    </a>
                </li>
                <li>
                    <a href="#appearance" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                        Appearance
                    </a>
                </li>
                <li>
                    <a href="#family" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Family
                    </a>
                </li>
                <li>
                    <a href="#billing" class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-base-content/60 hover:text-base-content hover:border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        Billing
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div>
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-6">Profile Settings</h2>

                <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="flex items-center gap-6">
                        <div class="avatar {{ auth()->user()->avatar ? '' : 'placeholder' }}">
                            @if(auth()->user()->avatar)
                                <div class="w-20 rounded-full">
                                    <img src="{{ Storage::disk('do_spaces')->url(auth()->user()->avatar) }}" alt="Avatar" />
                                </div>
                            @else
                                <div class="w-20 rounded-full bg-primary text-primary-content">
                                    <span class="text-2xl">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="btn btn-outline btn-sm cursor-pointer">
                                <span class="icon-[tabler--upload] size-4"></span>
                                Upload Photo
                                <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden" onchange="previewAvatar(this)" />
                            </label>
                            <p class="text-sm text-base-content/60 mt-1">JPG, PNG, or GIF. Max 2MB.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Full Name</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="input input-bordered" required />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" value="{{ auth()->user()->email ?? '' }}" class="input input-bordered bg-base-200" disabled />
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Email cannot be changed</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Phone Number</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="+1 (555) 000-0000" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Timezone</span>
                            </label>
                            <select class="select select-bordered" disabled>
                                <option>America/New_York</option>
                                <option>America/Los_Angeles</option>
                                <option>Europe/London</option>
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Coming soon</span>
                            </label>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = input.closest('form').querySelector('.avatar');
            avatarContainer.classList.remove('placeholder');
            avatarContainer.innerHTML = `
                <div class="w-20 rounded-full">
                    <img src="${e.target.result}" alt="Avatar Preview" />
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
