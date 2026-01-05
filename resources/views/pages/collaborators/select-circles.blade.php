@extends('layouts.dashboard')

@section('title', 'Select Family Circles')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li><a href="{{ route('collaborators.index') }}">Collaborators</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Select Family Circles</li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto" x-data="circleSelector()">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Invite a Collaborator</h1>
        <p class="text-slate-500 mt-2">Step 1: Select which family circles you want to share</p>
    </div>

    <!-- Progress Steps -->
    <div class="flex items-center justify-center gap-4 mb-8">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">1</span>
            <span class="text-sm font-medium text-primary">Select Circles</span>
        </div>
        <div class="w-12 h-0.5 bg-base-300"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-base-300 text-slate-500 flex items-center justify-center text-sm font-bold">2</span>
            <span class="text-sm text-slate-500">Invite Details</span>
        </div>
    </div>

    <!-- Circle Selection Card -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Your Family Circles</h3>
                <button type="button" @click="toggleAll()" class="btn btn-ghost btn-sm">
                    <span x-text="allSelected ? 'Deselect All' : 'Select All'"></span>
                </button>
            </div>

            <p class="text-sm text-slate-500 mb-6">Choose one or more family circles to share with your collaborator. They will be able to view the family members in the selected circles.</p>

            <div class="space-y-3">
                @foreach($familyCircles as $circle)
                    <label class="cursor-pointer block">
                        <input type="checkbox" value="{{ $circle->id }}" x-model="selectedCircles" class="hidden peer">
                        <div class="flex items-center gap-4 p-4 rounded-xl border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary/5 transition-all hover:bg-base-200">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-800">{{ $circle->name }}</div>
                                <div class="text-sm text-slate-500">{{ $circle->members_count }} member{{ $circle->members_count != 1 ? 's' : '' }}</div>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center transition-all"
                                 :class="selectedCircles.includes('{{ $circle->id }}') ? 'border-primary bg-primary' : 'border-base-300'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                     x-show="selectedCircles.includes('{{ $circle->id }}')" x-cloak><path d="M20 6 9 17l-5-5"/></svg>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            @if($familyCircles->isEmpty())
                <div class="text-center py-8">
                    <p class="text-slate-500">No family circles found.</p>
                    <a href="{{ route('family-circle.index') }}" class="btn btn-primary btn-sm mt-4">Create Family Circle</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Selected Summary -->
    <div class="bg-base-200/50 rounded-xl p-4 mb-6" x-show="selectedCircles.length > 0" x-cloak>
        <div class="flex items-center gap-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M20 6 9 17l-5-5"/></svg>
            <span class="text-slate-600"><span class="font-medium" x-text="selectedCircles.length"></span> circle<span x-show="selectedCircles.length !== 1">s</span> selected</span>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('collaborators.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="button" @click="proceedToInvite()" class="btn btn-primary gap-2" :disabled="selectedCircles.length === 0">
            Continue
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    </div>
</div>

<script>
function circleSelector() {
    return {
        selectedCircles: [],
        allCircleIds: [@foreach($familyCircles as $circle)'{{ $circle->id }}',@endforeach],

        get allSelected() {
            return this.selectedCircles.length === this.allCircleIds.length;
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedCircles = [];
            } else {
                this.selectedCircles = [...this.allCircleIds];
            }
        },

        proceedToInvite() {
            if (this.selectedCircles.length === 0) {
                alert('Please select at least one family circle');
                return;
            }

            // Build URL with selected circles
            const params = this.selectedCircles.map(id => `circles[]=${id}`).join('&');
            window.location.href = `{{ route('collaborators.create') }}?${params}`;
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
