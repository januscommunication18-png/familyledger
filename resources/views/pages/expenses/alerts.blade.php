@extends('layouts.dashboard')

@section('page-name', 'Budget Alerts')

@section('content')
<div class="p-4 lg:p-6 max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Budget Alerts</h1>
            <p class="text-sm text-slate-500">Get notified when spending approaches your limits</p>
        </div>
    </div>

    {{-- Create Alert Form --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Create New Alert</h3>

            <form action="{{ route('expenses.alerts.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Alert Type</span></label>
                        <select name="type" id="alertType" class="select select-bordered" required onchange="toggleThresholdLabel()">
                            <option value="percentage">Percentage of Budget</option>
                            <option value="amount">Fixed Amount</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Category (Optional)</span></label>
                        <select name="category_id" class="select select-bordered">
                            <option value="">Overall Budget</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->display_icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-slate-500">Leave empty to track total budget</span>
                        </label>
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-medium">Threshold</span>
                            <span class="label-text-alt text-slate-500" id="thresholdHint">Alert when spending reaches this % of budget</span>
                        </label>
                        <div class="join">
                            <input type="number" name="threshold" class="input input-bordered join-item flex-1" placeholder="80" min="1" required>
                            <span class="join-item bg-base-200 px-4 flex items-center font-medium" id="thresholdUnit">%</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    Create Alert
                </button>
            </form>
        </div>
    </div>

    {{-- Active Alerts --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-4">Active Alerts</h3>

            @if($alerts->count() > 0)
            <div class="space-y-3">
                @foreach($alerts as $alert)
                @php
                    $isTriggered = $alert->shouldTrigger();
                    $progress = $alert->getProgressPercentage();
                @endphp
                <div class="flex items-start gap-3 p-4 rounded-lg {{ $isTriggered ? 'bg-red-50 border border-red-200' : 'bg-base-200' }}">
                    {{-- Icon --}}
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $isTriggered ? 'bg-red-100' : 'bg-base-300' }}">
                        @if($isTriggered)
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(239 68 68)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(100 116 139)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        @endif
                    </div>

                    {{-- Alert Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <p class="font-medium {{ $isTriggered ? 'text-red-800' : 'text-slate-800' }}">
                                    @if($alert->category)
                                        {{ $alert->category->display_icon }} {{ $alert->category->name }}
                                    @else
                                        Overall Budget
                                    @endif
                                </p>
                                <p class="text-sm {{ $isTriggered ? 'text-red-600' : 'text-slate-500' }}">
                                    Alert at {{ $alert->type === 'percentage' ? $alert->threshold . '%' : '$' . number_format($alert->threshold, 2) }}
                                </p>
                            </div>
                            <form action="{{ route('expenses.alerts.delete', $alert) }}" method="POST" onsubmit="return confirm('Delete this alert?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs text-slate-400 hover:text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-base-300 rounded-full h-2 mb-1">
                            <div class="h-2 rounded-full transition-all {{ $isTriggered ? 'bg-red-500' : 'bg-emerald-500' }}"
                                 style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs {{ $isTriggered ? 'text-red-600' : 'text-slate-500' }}">
                            <span>{{ round($progress, 1) }}% spent</span>
                            @if($isTriggered)
                            <span class="font-medium">Alert triggered!</span>
                            @else
                            <span>{{ round($alert->threshold - $progress, 1) }}% until alert</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">No Alerts Set</h3>
                <p class="text-slate-500 mb-4">Create alerts to get notified when you're approaching your budget limits.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Alert Tips --}}
    <div class="card bg-amber-50 border border-amber-200 mt-6">
        <div class="card-body py-4">
            <div class="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(217 119 6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <div>
                    <p class="font-medium text-amber-800 mb-1">Pro Tips</p>
                    <ul class="text-sm text-amber-700 space-y-1">
                        <li>Set alerts at 80% to give yourself time to adjust spending</li>
                        <li>Create category-specific alerts for areas where you tend to overspend</li>
                        <li>Use fixed amount alerts when tracking specific savings goals</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleThresholdLabel() {
    const type = document.getElementById('alertType').value;
    const hint = document.getElementById('thresholdHint');
    const unit = document.getElementById('thresholdUnit');

    if (type === 'percentage') {
        hint.textContent = 'Alert when spending reaches this % of budget';
        unit.textContent = '%';
    } else {
        hint.textContent = 'Alert when spending reaches this amount';
        unit.textContent = '$';
    }
}
</script>
@endsection
