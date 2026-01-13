@props(['id' => 'deleteConfirmModal'])

<!-- Delete Confirmation Modal -->
<div id="{{ $id }}" class="hidden fixed inset-0 z-50">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeDeleteModal('{{ $id }}')"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-4">
                        <span class="icon-[tabler--trash] shrink-0 size-8 text-error"></span>
                    </div>
                    <h3 class="font-bold text-lg mb-2" id="{{ $id }}_title">Delete Item?</h3>
                    <p class="text-slate-500 text-sm" id="{{ $id }}_message">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="flex justify-center gap-3 mt-6">
                    <button type="button" onclick="closeDeleteModal('{{ $id }}')" class="btn btn-ghost min-w-24">Cancel</button>
                    <form id="{{ $id }}_form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error min-w-24 gap-2">
                            <span class="icon-[tabler--trash] shrink-0 size-4"></span>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global delete modal functions
function confirmDelete(url, message = 'Are you sure you want to delete this item? This action cannot be undone.', title = 'Delete Item?', modalId = '{{ $id }}') {
    const modal = document.getElementById(modalId);
    const form = document.getElementById(modalId + '_form');
    const messageEl = document.getElementById(modalId + '_message');
    const titleEl = document.getElementById(modalId + '_title');

    if (form) form.action = url;
    if (messageEl) messageEl.textContent = message;
    if (titleEl) titleEl.textContent = title;

    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteModal(modalId = '{{ $id }}') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('{{ $id }}');
        if (modal && !modal.classList.contains('hidden')) {
            closeDeleteModal('{{ $id }}');
        }
    }
});
</script>
