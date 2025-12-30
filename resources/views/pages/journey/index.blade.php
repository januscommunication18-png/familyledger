@extends('layouts.dashboard')

@section('title', 'Journey')
@section('page-name', 'Journey')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Journey</li>
@endsection

@section('page-title', 'Journey')
@section('page-description', 'Track your family\'s milestones, memories, and achievements.')

@section('content')
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <h2 class="card-title">Family Timeline</h2>
            <button class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Milestone
            </button>
        </div>

        <div class="text-center py-12 text-base-content/60">
            <span class="icon-[tabler--timeline] size-16 opacity-30"></span>
            <p class="mt-4 text-lg font-medium">Your family journey starts here</p>
            <p class="text-sm">Document important milestones, memories, and achievements</p>
            <button class="btn btn-primary mt-4">
                <span class="icon-[tabler--plus] size-4"></span>
                Add Your First Milestone
            </button>
        </div>

        <!-- Timeline placeholder -->
        <div class="hidden">
            <ul class="timeline timeline-vertical">
                <li>
                    <div class="timeline-start timeline-box">First milestone</div>
                    <div class="timeline-middle">
                        <span class="icon-[tabler--circle-check-filled] size-5 text-primary"></span>
                    </div>
                    <hr class="bg-primary"/>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
