@extends('layouts.app')

@section('title', 'Setup Your Account')

@section('content')
<div class="min-h-screen bg-base-200 py-8">
    <div class="container mx-auto max-w-2xl px-4">
        <!-- Progress indicator -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-base-content/60">Step {{ $step }} of {{ $totalSteps }}</span>
                <span class="text-sm font-medium">{{ round(($step / $totalSteps) * 100) }}% complete</span>
            </div>
            <div class="w-full bg-base-300 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full transition-all duration-300" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>

        <!-- Step Cards -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">

                @if($step == 1)
                <!-- Step 1: Goals -->
                <h2 class="card-title text-2xl mb-2">Welcome! Let's get started</h2>
                <p class="text-base-content/60 mb-6">What's your primary goal for using this app?</p>

                <form action="/onboarding/step1" method="POST">
                    @csrf
                    @error('goals')
                        <div class="alert alert-error mb-4">{{ $message }}</div>
                    @enderror

                    <div class="space-y-3">
                        @foreach($goals as $key => $goal)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="goals[]" value="{{ $key }}" class="checkbox checkbox-primary mt-1">
                                <div class="ml-3">
                                    <div class="font-medium">{{ $goal['title'] }}</div>
                                    <div class="text-sm text-base-content/60">{{ $goal['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="/dashboard" class="btn btn-ghost">Skip for now</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>

                @elseif($step == 2)
                <!-- Step 2: Household Setup -->
                <h2 class="card-title text-2xl mb-2">Set up your household</h2>
                <p class="text-base-content/60 mb-6">Define your family unit and preferences</p>

                <form action="/onboarding/step2" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Household name *</span></label>
                            <input type="text" name="name" value="{{ old('name', $tenant['name'] ?? '') }}"
                                   placeholder="e.g., Smith Family" class="input input-bordered w-full" required>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Country *</span></label>
                            <select name="country" class="select select-bordered w-full" required>
                                <option value="">Select country</option>
                                @foreach($countries as $code => $name)
                                    <option value="{{ $code }}" {{ old('country', $tenant['country'] ?? '') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Timezone *</span></label>
                            <select name="timezone" class="select select-bordered w-full" required>
                                <option value="">Select timezone</option>
                                @foreach($timezones as $region => $zones)
                                    <optgroup label="{{ $region }}">
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone }}" {{ old('timezone', $tenant['timezone'] ?? '') == $zone ? 'selected' : '' }}>{{ $zone }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Family type (optional)</span></label>
                            <select name="family_type" class="select select-bordered w-full">
                                <option value="">Select family type</option>
                                @foreach($familyTypes as $key => $name)
                                    <option value="{{ $key }}" {{ old('family_type', $tenant['family_type'] ?? '') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 3)
                <!-- Step 3: Role Selection -->
                <h2 class="card-title text-2xl mb-2">What's your role?</h2>
                <p class="text-base-content/60 mb-6">This helps us set appropriate permissions</p>

                <form action="/onboarding/step3" method="POST">
                    @csrf
                    <div class="space-y-3">
                        @foreach($roles as $key => $role)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="role" value="{{ $key }}" class="radio radio-primary mt-1"
                                       {{ old('role', $user['role'] ?? 'parent') == $key ? 'checked' : '' }} required>
                                <div class="ml-3">
                                    <div class="font-medium">{{ $role['title'] }}</div>
                                    <div class="text-sm text-base-content/60">{{ $role['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 4)
                <!-- Step 4: Invite Members -->
                <h2 class="card-title text-2xl mb-2">Invite family members</h2>
                <p class="text-base-content/60 mb-6">You can skip this and add people later.</p>

                <form action="/onboarding/step4" method="POST">
                    @csrf
                    <div class="p-4 border border-base-300 rounded-lg">
                        <div class="grid gap-3">
                            <input type="email" name="members[0][email]" placeholder="Email address" class="input input-bordered w-full">
                            <select name="members[0][role]" class="select select-bordered w-full">
                                <option value="">Select role</option>
                                @foreach($roles as $key => $role)
                                    <option value="{{ $key }}">{{ $role['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <div class="flex gap-2">
                            <button type="submit" name="skip" value="1" class="btn btn-ghost">Skip</button>
                            <button type="submit" class="btn btn-primary">Continue</button>
                        </div>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 5)
                <!-- Step 5: Quick Setup -->
                <h2 class="card-title text-2xl mb-2">What do you want to set up first?</h2>
                <p class="text-base-content/60 mb-6">Select one or more to get started</p>

                <form action="/onboarding/step5" method="POST">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($quickSetup as $key => $item)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="quick_setup[]" value="{{ $key }}" class="checkbox checkbox-primary mt-1">
                                <div class="ml-3">
                                    <div class="font-medium text-sm">{{ $item['title'] }}</div>
                                    <div class="text-xs text-base-content/60">{{ $item['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @elseif($step == 6)
                <!-- Step 6: Finish -->
                <h2 class="card-title text-2xl mb-2">You're all set!</h2>
                <p class="text-base-content/60 mb-6">Your account is ready to use</p>

                <form action="/onboarding/step6" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="email_notifications" value="1" class="checkbox checkbox-primary" checked>
                            <span>Email notifications</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="enable_2fa" value="1" class="checkbox checkbox-primary">
                            <span>Enable two-factor authentication</span>
                        </label>
                    </div>

                    <div class="card-actions justify-between mt-8">
                        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
                        <button type="submit" class="btn btn-primary">Complete Setup</button>
                    </div>
                </form>
                <form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

                @else
                <!-- Fallback -->
                <p>Loading step {{ $step }}...</p>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
