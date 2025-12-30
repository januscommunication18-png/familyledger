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
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Settings Navigation -->
    <div class="lg:col-span-1">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-2">
                <ul class="menu">
                    <li><a class="active"><span class="icon-[tabler--user] size-4"></span>Profile</a></li>
                    <li><a><span class="icon-[tabler--lock] size-4"></span>Security</a></li>
                    <li><a><span class="icon-[tabler--bell] size-4"></span>Notifications</a></li>
                    <li><a><span class="icon-[tabler--palette] size-4"></span>Appearance</a></li>
                    <li><a><span class="icon-[tabler--users] size-4"></span>Family Settings</a></li>
                    <li><a><span class="icon-[tabler--credit-card] size-4"></span>Billing</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="lg:col-span-3">
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
