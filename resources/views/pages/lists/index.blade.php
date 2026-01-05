@extends('layouts.dashboard')

@section('title', 'Family Lists')
@section('page-name', 'Lists')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Lists</li>
@endsection

@section('page-title', 'Family Lists')
@section('page-description', 'Everyone knows what needs to be done - no reminders, no confusion.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="border-b border-base-200 mb-6">
                <nav class="-mb-px flex gap-6">
                    <a href="{{ route('lists.index') }}"
                       class="pb-3 px-1 border-b-2 font-medium text-sm border-primary text-primary">
                        <span class="icon-[tabler--checkbox] size-5 inline-block align-middle mr-2"></span>
                        To-Do List
                        @php
                            $pendingCount = $todoLists->sum(fn($list) => $list->items->where('status', '!=', 'completed')->count());
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge badge-sm badge-primary ml-2">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            @include('pages.lists.partials.todos-tab')
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<dialog id="deleteConfirmModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Confirm Delete</h3>
        <p class="py-4" id="deleteMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function confirmDelete(url, message = 'Are you sure you want to delete this item? This action cannot be undone.') {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteMessage').textContent = message;
    document.getElementById('deleteConfirmModal').showModal();
}

// Toggle todo item completion
function toggleTodoItem(itemId) {
    fetch(`{{ url('/lists/todos/items') }}/${itemId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

</script>
@endsection
