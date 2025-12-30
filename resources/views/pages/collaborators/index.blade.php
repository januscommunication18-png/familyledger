@extends('layouts.dashboard')

@section('title', 'Collaborators')
@section('page-name', 'Collaborators')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Collaborators</li>
@endsection

@section('page-title', 'Collaborators')
@section('page-description', 'Manage trusted advisors and professionals who help your family.')

@section('content')
<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="card-title">Trusted Advisors</h2>
                <p class="text-sm text-base-content/60">CPAs, lawyers, caregivers, and other professionals</p>
            </div>
            <button class="btn btn-primary">
                <span class="icon-[tabler--user-plus] size-5"></span>
                Add Collaborator
            </button>
        </div>

        <div class="text-center py-12 text-base-content/60">
            <span class="icon-[tabler--users-group] size-16 opacity-30"></span>
            <p class="mt-4 text-lg font-medium">No collaborators yet</p>
            <p class="text-sm">Add trusted professionals to help manage your family's affairs</p>
            <button class="btn btn-primary mt-4">
                <span class="icon-[tabler--user-plus] size-4"></span>
                Add Your First Collaborator
            </button>
        </div>
    </div>
</div>
@endsection
