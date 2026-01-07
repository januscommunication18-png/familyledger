@extends('layouts.dashboard')

@section('page-name', 'Categories')

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $budget->is_envelope ? 'Envelopes' : 'Categories' }}</h1>
            <p class="text-sm text-slate-500">Organize your spending into categories</p>
        </div>
        <button onclick="openAddModal()" class="btn btn-primary gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Add Category
        </button>
    </div>

    {{-- Budget Summary --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm text-slate-500">Total Budget</p>
                    <p class="text-xl font-bold text-slate-800">${{ number_format($budget->total_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Allocated</p>
                    <p class="text-xl font-bold text-emerald-600">${{ number_format($budget->getTotalAllocated(), 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Unallocated</p>
                    @php $unallocated = $budget->getUnallocatedAmount(); @endphp
                    <p class="text-xl font-bold {{ $unallocated >= 0 ? 'text-slate-600' : 'text-red-600' }}">${{ number_format(abs($unallocated), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories List --}}
    <div class="space-y-3">
        @forelse($categories as $category)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background-color: {{ $category->color }}20">
                        {{ $category->display_icon }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $category->name }}</h3>
                            <span class="text-sm text-slate-600">
                                ${{ number_format($category->getSpentAmount(), 2) }} / ${{ number_format($category->allocated_amount, 2) }}
                            </span>
                        </div>
                        @php $progress = $category->getProgressPercentage(); @endphp
                        <div class="flex items-center gap-2">
                            <progress class="progress w-full h-3 {{ $progress >= 100 ? 'progress-error' : ($progress >= 80 ? 'progress-warning' : 'progress-success') }}" value="{{ min($progress, 100) }}" max="100"></progress>
                            <span class="text-sm font-medium text-slate-600 w-14 text-right">{{ $progress }}%</span>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs text-slate-500">
                            <span>{{ $category->getTransactionCount() }} transactions</span>
                            <span class="{{ $category->isOverBudget() ? 'text-red-600' : 'text-emerald-600' }}">
                                ${{ number_format(abs($category->getRemainingAmount()), 2) }} {{ $category->isOverBudget() ? 'over' : 'remaining' }}
                            </span>
                        </div>
                    </div>
                    <div class="relative">
                        <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="toggleDropdown('cat-dropdown-{{ $category->id }}', event)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                        </button>
                        <ul id="cat-dropdown-{{ $category->id }}" class="hidden absolute right-0 top-full mt-1 z-50 menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-slate-200">
                            <li><a href="#" onclick="editCategory({{ $category->id }}, {{ json_encode($category) }}); closeAllDropdowns();">Edit</a></li>
                            <li>
                                <form action="{{ route('expenses.categories.delete', $category) }}" method="POST" onsubmit="return confirm('Delete this category? Transactions will become uncategorized.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-error w-full text-left">Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-12">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h3 class="font-semibold text-slate-800 mb-1">No categories yet</h3>
                <p class="text-sm text-slate-500 mb-4">Create categories to organize your spending.</p>
                <button onclick="openAddModal()" class="btn btn-primary btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Add Category
                </button>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Add/Edit Category Modal --}}
<div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-slate-800">Add Category</h3>
                <button onclick="closeModal()" class="btn btn-ghost btn-sm btn-square">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <form id="categoryForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-4">
                    {{-- Icon --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Icon</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($defaultIcons as $key => $icon)
                            <label class="cursor-pointer">
                                <input type="radio" name="icon" value="{{ $icon }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-lg border-2 border-transparent peer-checked:border-primary flex items-center justify-center text-xl bg-base-200 hover:bg-base-300">
                                    {{ $icon }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Name</span></label>
                        <input type="text" name="name" class="input input-bordered" placeholder="e.g., Groceries" required>
                    </div>

                    {{-- Allocated Amount --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Budget Amount</span></label>
                        <label class="input-group">
                            <span class="bg-base-200">$</span>
                            <input type="number" name="allocated_amount" step="0.01" min="0" class="input input-bordered flex-1" placeholder="0.00" required>
                        </label>
                    </div>

                    {{-- Color --}}
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Color</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($defaultColors as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                                <div class="w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-slate-800 peer-checked:ring-2 peer-checked:ring-offset-2" style="background-color: {{ $color }}"></div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-ghost flex-1">Cancel</button>
                    <button type="submit" class="btn btn-primary flex-1">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDropdown(id, event) {
    event.stopPropagation();
    const dropdown = document.getElementById(id);
    const isHidden = dropdown.classList.contains('hidden');
    closeAllDropdowns();
    if (isHidden) {
        dropdown.classList.remove('hidden');
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('[id^="cat-dropdown-"]').forEach(el => el.classList.add('hidden'));
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="cat-dropdown-"]')) {
        closeAllDropdowns();
    }
});

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').action = '{{ route('expenses.categories.store') }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryModal').classList.remove('hidden');
}

function editCategory(id, data) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryForm').action = '/expenses/categories/' + id;
    document.getElementById('formMethod').value = 'PUT';

    document.querySelector('input[name="name"]').value = data.name;
    document.querySelector('input[name="allocated_amount"]').value = data.allocated_amount;

    // Select icon
    const iconInput = document.querySelector('input[name="icon"][value="' + data.icon + '"]');
    if (iconInput) iconInput.checked = true;

    // Select color
    const colorInput = document.querySelector('input[name="color"][value="' + data.color + '"]');
    if (colorInput) colorInput.checked = true;

    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
