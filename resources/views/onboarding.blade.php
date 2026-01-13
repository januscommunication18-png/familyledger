@extends('layouts.app')

@section('title', 'Welcome to Family Ledger')

@section('content')
<div class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="max-w-2xl w-full">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-primary">Welcome to Family Ledger!</h1>
            <p class="text-base-content/60 mt-2">Let's set up your family circle in just a few steps.</p>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Progress Steps -->
                <ul class="steps steps-horizontal w-full mb-8">
                    <li class="step step-primary">Profile</li>
                    <li class="step">Family</li>
                    <li class="step">Security</li>
                    <li class="step">Done</li>
                </ul>

                <!-- Step 1: Profile -->
                <div id="step-1">
                    <h2 class="text-2xl font-semibold mb-4">Complete Your Profile</h2>

                    <form class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Full Name</span>
                            </label>
                            <input type="text" value="{{ auth()->user()->name }}" class="input input-bordered w-full">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Phone Number (optional)</span>
                            </label>
                            <input type="tel" placeholder="+1 (555) 123-4567" class="input input-bordered w-full">
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">For SMS notifications and 2FA</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Your Role</span>
                            </label>
                            <select class="select select-bordered w-full">
                                <option value="parent" selected>Parent</option>
                                <option value="coparent">Co-Parent</option>
                                <option value="guardian">Guardian</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="card-actions justify-between mt-8">
                    <form action="{{ route('onboarding.skip') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost">Skip for now</button>
                    </form>
                    <button class="btn btn-primary">Continue</button>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-base-content/50 mt-6">
            You can always update these settings later.
        </p>
    </div>
</div>
@endsection
