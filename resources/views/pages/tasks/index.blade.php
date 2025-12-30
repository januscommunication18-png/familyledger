@extends('layouts.dashboard')

@section('title', 'To Do List')
@section('page-name', 'To Do List')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">To Do List</li>
@endsection

@section('page-title', 'To Do List')
@section('page-description', 'Keep track of family tasks and responsibilities.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- To Do Column -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--circle] size-4 text-base-content/40"></span>
                    To Do
                </h2>
                <span class="badge badge-ghost">0</span>
            </div>
            <div class="text-center py-8 text-base-content/60">
                <p class="text-sm">No tasks</p>
            </div>
            <button class="btn btn-ghost btn-sm btn-block border-dashed border-2 border-base-content/20">
                <span class="icon-[tabler--plus] size-4"></span>
                Add Task
            </button>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--clock] size-4 text-warning"></span>
                    In Progress
                </h2>
                <span class="badge badge-ghost">0</span>
            </div>
            <div class="text-center py-8 text-base-content/60">
                <p class="text-sm">No tasks in progress</p>
            </div>
        </div>
    </div>

    <!-- Completed Column -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--circle-check] size-4 text-success"></span>
                    Completed
                </h2>
                <span class="badge badge-ghost">0</span>
            </div>
            <div class="text-center py-8 text-base-content/60">
                <p class="text-sm">No completed tasks</p>
            </div>
        </div>
    </div>
</div>
@endsection
