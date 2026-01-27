{{-- Reusable Confirmation Modal --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full transform transition-all">
            {{-- Header --}}
            <div class="p-6 pb-0">
                <div class="flex items-center gap-3">
                    <div id="confirmModalIcon" class="w-12 h-12 rounded-full flex items-center justify-center">
                        {{-- Icon will be inserted by JS --}}
                    </div>
                    <h3 id="confirmModalTitle" class="text-xl font-bold text-slate-800"></h3>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p id="confirmModalMessage" class="text-slate-600"></p>

                {{-- Optional textarea for notes (hidden by default) --}}
                <div id="confirmModalNotesWrapper" class="hidden mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <span id="confirmModalNotesLabel">Notes (optional)</span>
                    </label>
                    <textarea id="confirmModalNotes" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-none" rows="3" placeholder="Enter notes..."></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 p-6 pt-0">
                <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium rounded-lg hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirmModalBtn" class="px-4 py-2 font-medium rounded-lg transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let confirmModalCallback = null;
let confirmModalRequiresNotes = false;

function showConfirmModal(options) {
    const modal = document.getElementById('confirmModal');
    const iconEl = document.getElementById('confirmModalIcon');
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const btnEl = document.getElementById('confirmModalBtn');
    const notesWrapper = document.getElementById('confirmModalNotesWrapper');
    const notesInput = document.getElementById('confirmModalNotes');
    const notesLabel = document.getElementById('confirmModalNotesLabel');

    // Set content
    titleEl.textContent = options.title || 'Confirm';
    messageEl.textContent = options.message || 'Are you sure?';
    btnEl.textContent = options.confirmText || 'Confirm';

    // Set type styling
    const type = options.type || 'info';
    iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center';
    btnEl.className = 'px-4 py-2 font-medium rounded-lg transition-colors text-white';

    if (type === 'success') {
        iconEl.classList.add('bg-emerald-100');
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><polyline points="20 6 9 17 4 12"/></svg>';
        btnEl.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
    } else if (type === 'danger') {
        iconEl.classList.add('bg-red-100');
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
        btnEl.classList.add('bg-red-600', 'hover:bg-red-700');
    } else {
        iconEl.classList.add('bg-violet-100');
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-600"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>';
        btnEl.classList.add('bg-violet-600', 'hover:bg-violet-700');
    }

    // Handle notes field
    confirmModalRequiresNotes = options.showNotes || false;
    if (confirmModalRequiresNotes) {
        notesWrapper.classList.remove('hidden');
        notesLabel.textContent = options.notesLabel || 'Notes (optional)';
        notesInput.value = '';
        notesInput.placeholder = options.notesPlaceholder || 'Enter notes...';
    } else {
        notesWrapper.classList.add('hidden');
    }

    // Store callback
    confirmModalCallback = options.onConfirm || null;

    // Show modal
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    confirmModalCallback = null;
}

document.getElementById('confirmModalBtn').addEventListener('click', function() {
    if (confirmModalCallback) {
        const notes = confirmModalRequiresNotes ? document.getElementById('confirmModalNotes').value : null;
        confirmModalCallback(notes);
    }
    closeConfirmModal();
});

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmModal();
    }
});
</script>
