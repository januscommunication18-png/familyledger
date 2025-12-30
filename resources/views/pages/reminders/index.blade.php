@extends('layouts.dashboard')

@section('title', 'Reminders')
@section('page-name', 'Reminders')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Reminders</li>
@endsection

@section('page-title', 'Reminders')
@section('page-description', 'Never miss important dates, renewals, and deadlines.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <h2 class="card-title">Upcoming Reminders</h2>
                    <button class="btn btn-primary">
                        <span class="icon-[tabler--bell-plus] size-5"></span>
                        Add Reminder
                    </button>
                </div>

                <div class="text-center py-12 text-base-content/60">
                    <span class="icon-[tabler--bell] size-16 opacity-30"></span>
                    <p class="mt-4 text-lg font-medium">No reminders set</p>
                    <p class="text-sm">Create reminders for important dates and deadlines</p>
                    <button class="btn btn-primary mt-4">
                        <span class="icon-[tabler--bell-plus] size-4"></span>
                        Create Your First Reminder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Add</h2>
                <div class="space-y-2">
                    <button class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--cake] size-4"></span>
                        Birthday
                    </button>
                    <button class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--vaccine] size-4"></span>
                        Medical Appointment
                    </button>
                    <button class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--file-certificate] size-4"></span>
                        Document Renewal
                    </button>
                    <button class="btn btn-ghost btn-sm btn-block justify-start">
                        <span class="icon-[tabler--receipt] size-4"></span>
                        Bill Payment
                    </button>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Calendar</h2>
                <div class="text-center py-4 text-base-content/60">
                    <span class="icon-[tabler--calendar] size-10 opacity-30"></span>
                    <p class="mt-2 text-sm">Calendar view coming soon</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
