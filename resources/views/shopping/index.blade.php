@extends('layouts.dashboard')

@section('title', 'Shopping Lists')
@section('page-name', 'Shopping Lists')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </li>
    <li aria-current="page">Shopping Lists</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Shopping Lists</h1>
                <p class="text-slate-500">{{ $lists->count() }} {{ Str::plural('list', $lists->count()) }}</p>
            </div>
        </div>
        <button onclick="toggleCreateModal()" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            New List
        </button>
    </div>

    <!-- Shopping Lists Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($lists as $list)
            <a href="{{ route('shopping.show', $list) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all border border-slate-200 hover:border-emerald-300 group">
                <div class="card-body p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $list->color_class }} flex items-center justify-center shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 text-lg flex items-center gap-2">
                                    {{ $list->name }}
                                    @if($list->is_shared ?? false)
                                        <span class="badge badge-sm badge-secondary gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                                            Shared
                                        </span>
                                    @endif
                                    @if($list->is_default)
                                        <span class="badge badge-sm badge-primary">Default</span>
                                    @endif
                                    @if($list->recurring)
                                        <span class="badge badge-sm badge-info">{{ $list->recurring_label }}</span>
                                    @endif
                                </h3>
                                @if($list->store_name)
                                    <p class="text-sm text-slate-500">{{ $list->store_name }}</p>
                                @endif
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 group-hover:text-emerald-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                    </div>

                    <div class="flex items-center gap-4 mt-4">
                        @if($list->unchecked_count > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <span class="text-amber-700 font-bold text-sm">{{ $list->unchecked_count }}</span>
                                </div>
                                <span class="text-sm text-slate-600">to buy</span>
                            </div>
                        @endif
                        @if($list->items_count - $list->unchecked_count > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <span class="text-sm text-slate-600">{{ $list->items_count - $list->unchecked_count }} done</span>
                            </div>
                        @endif
                        @if($list->items_count == 0)
                            <span class="text-sm text-slate-400">No items yet</span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if($lists->isEmpty())
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">No shopping lists yet</h3>
                <p class="text-slate-500 mb-6">Create your first shopping list to get started.</p>
                <button onclick="toggleCreateModal()" class="btn btn-primary gap-2 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Create Shopping List
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Create List Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="toggleCreateModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <form action="{{ route('shopping.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Create Shopping List</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">List Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g., Weekly Groceries, Costco Run"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Store (Optional)</label>
                            <select name="store" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 bg-white">
                                <option value="">No specific store</option>
                                @foreach($stores as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Recurring (Optional)</label>
                            <select name="recurring" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 bg-white">
                                @foreach(\App\Models\ShoppingList::RECURRING_FREQUENCIES as $key => $label)
                                    <option value="{{ $key === 'none' ? '' : $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($colors as $key => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $key }}" {{ $key === 'emerald' ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-8 h-8 rounded-lg bg-{{ $key }}-500 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-{{ $key }}-500 transition-all"></div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 p-6 pt-0">
                    <button type="button" onclick="toggleCreateModal()" class="flex-1 btn btn-ghost">Cancel</button>
                    <button type="submit" class="flex-1 btn btn-primary">Create List</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.toggle('hidden');
    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('createModal').classList.contains('hidden')) {
        toggleCreateModal();
    }
});
</script>
@endpush
