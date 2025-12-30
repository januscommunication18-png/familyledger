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

        <div>
            <onboarding-wizard
                :initial-step="{{ $step }}"
                :total-steps="{{ $totalSteps }}"
                :goals='@json($goals)'
                :countries='@json($countries)'
                :family-types='@json($familyTypes)'
                :roles='@json($roles)'
                :quick-setup='@json($quickSetup)'
                :timezones='@json($timezones)'
                :tenant='@json($tenant)'
                :user='@json($user)'
            ></onboarding-wizard>
        </div>
    </div>
</div>
@endsection
