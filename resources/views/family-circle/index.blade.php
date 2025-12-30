@extends('layouts.dashboard')

@section('title', 'Family Circle')
@section('page-name', 'Family Circle')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Family Circle</li>
@endsection

@section('page-title', 'Family Circle')
@section('page-description', 'Create and manage family circles to organize your family members.')

@section('content')
<div id="family-circle-app">
    @if($circles->isEmpty())
        <!-- Empty State -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-16">
                <div class="text-center max-w-lg mx-auto">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-3">Welcome to Family Circle</h2>
                    <p class="text-slate-500 mb-8">
                        Family Circles help you organize and manage your family members. Create a circle for your immediate family, extended family, or any group you'd like to track.
                    </p>
                    <button type="button" onclick="openCreateModal()" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                        Create Your First Family Circle
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Family Circles Grid -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-slate-500">You have {{ $circles->count() }} family circle{{ $circles->count() > 1 ? 's' : '' }}</p>
            </div>
            <button type="button" onclick="openCreateModal()" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                Create Family Circle
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($circles as $circle)
                <a href="{{ route('family-circle.show', $circle) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 shrink-0 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900 group-hover:text-violet-600 transition-colors truncate">{{ $circle->name }}</h3>
                                @if($circle->description)
                                    <p class="text-sm text-slate-500 line-clamp-2 mt-1">{{ $circle->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <span>{{ $circle->members_count }} member{{ $circle->members_count != 1 ? 's' : '' }}</span>
                            </div>
                            <div class="text-sm text-slate-400">
                                {{ $circle->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Create Family Circle Modal -->
<div id="createCircleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <!-- Backdrop -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"></div>
    <!-- Modal -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; overflow-y: auto;">
        <div style="display: flex; min-height: 100%; align-items: center; justify-content: center; padding: 1rem;">
            <div style="position: relative; width: 100%; max-width: 28rem; background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Create Family Circle</h3>
                    <button type="button" onclick="closeCreateModal()" style="padding: 0.25rem; border-radius: 0.5rem; color: #94a3b8; background: transparent; border: none; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <!-- Body -->
                <form id="createCircleForm" action="{{ route('family-circle.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Circle Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" placeholder="e.g., Johnson Family" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20" required maxlength="255">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Description <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <textarea name="description" placeholder="A brief description of this family circle..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 h-24 resize-none" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div style="display: flex; justify-content: flex-start; gap: 0.75rem; border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; background: #f8fafc; border-radius: 0 0 1rem 1rem;">
                        <button type="submit" style="padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 500; color: white; background: #7c3aed; border: none; border-radius: 0.5rem; cursor: pointer;">Create Family Circle</button>
                        <button type="button" onclick="closeCreateModal()" style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #334155; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    const modal = document.getElementById('createCircleModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeCreateModal() {
    const modal = document.getElementById('createCircleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('createCircleModal');
    const backdrop = modal ? modal.querySelector('div:first-child') : null;

    if (backdrop) {
        // Close on backdrop click
        backdrop.addEventListener('click', function(e) {
            closeCreateModal();
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
        }
    });
});
</script>
@endsection
