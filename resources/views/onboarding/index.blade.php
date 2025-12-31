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
                    @include('onboarding.steps.step1')
                @elseif($step == 2)
                    @include('onboarding.steps.step2')
                @elseif($step == 3)
                    @include('onboarding.steps.step3')
                @elseif($step == 4)
                    @include('onboarding.steps.step4')
                @elseif($step == 5)
                    @include('onboarding.steps.step5')
                @elseif($step == 6)
                    @include('onboarding.steps.step6')
                @else
                    @include('onboarding.steps.step1')
                @endif
            </div>
        </div>

        <p class="text-center text-sm text-base-content/50 mt-6">
            You can always update these settings later.
        </p>
    </div>
</div>
@endsection
