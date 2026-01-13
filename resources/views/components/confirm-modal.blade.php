@props(['id' => 'confirmModal'])

<!-- Generic Confirmation Modal -->
<div id="{{ $id }}" class="hidden fixed inset-0 z-50">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal('{{ $id }}')"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <div class="flex flex-col items-center text-center">
                    <div id="{{ $id }}_iconWrapper" class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <span id="{{ $id }}_icon" class="icon-[tabler--alert-circle] shrink-0 size-8 text-primary"></span>
                    </div>
                    <h3 class="font-bold text-lg mb-2" id="{{ $id }}_title">Confirm Action</h3>
                    <p class="text-slate-500 text-sm" id="{{ $id }}_message">Are you sure you want to proceed?</p>
                </div>
                <div class="flex justify-center gap-3 mt-6">
                    <button type="button" onclick="closeConfirmModal('{{ $id }}')" class="btn btn-ghost min-w-24">Cancel</button>
                    <button type="button" id="{{ $id }}_confirmBtn" onclick="" class="btn btn-primary min-w-24 gap-2">
                        <span id="{{ $id }}_btnIcon" class="icon-[tabler--check] shrink-0 size-4"></span>
                        <span id="{{ $id }}_btnText">Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generic confirm modal functions
function showConfirmModal(options = {}) {
    const modalId = options.modalId || '{{ $id }}';
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Set title
    const titleEl = document.getElementById(modalId + '_title');
    if (titleEl) titleEl.textContent = options.title || 'Confirm Action';

    // Set message
    const messageEl = document.getElementById(modalId + '_message');
    if (messageEl) messageEl.textContent = options.message || 'Are you sure you want to proceed?';

    // Set icon and colors
    const iconWrapper = document.getElementById(modalId + '_iconWrapper');
    const iconEl = document.getElementById(modalId + '_icon');
    const confirmBtn = document.getElementById(modalId + '_confirmBtn');
    const btnIcon = document.getElementById(modalId + '_btnIcon');
    const btnText = document.getElementById(modalId + '_btnText');

    // Icon options: pause, play, warning, info, etc.
    const iconConfig = {
        pause: { icon: 'icon-[tabler--player-pause]', wrapperClass: 'bg-amber-100', iconClass: 'text-amber-600', btnClass: 'btn-warning' },
        play: { icon: 'icon-[tabler--player-play]', wrapperClass: 'bg-emerald-100', iconClass: 'text-emerald-600', btnClass: 'btn-success' },
        warning: { icon: 'icon-[tabler--alert-triangle]', wrapperClass: 'bg-amber-100', iconClass: 'text-amber-600', btnClass: 'btn-warning' },
        info: { icon: 'icon-[tabler--info-circle]', wrapperClass: 'bg-blue-100', iconClass: 'text-blue-600', btnClass: 'btn-primary' },
        danger: { icon: 'icon-[tabler--alert-circle]', wrapperClass: 'bg-error/10', iconClass: 'text-error', btnClass: 'btn-error' }
    };

    const config = iconConfig[options.type] || iconConfig.info;

    if (iconWrapper) {
        iconWrapper.className = 'w-16 h-16 rounded-full flex items-center justify-center mb-4 ' + config.wrapperClass;
    }
    if (iconEl) {
        iconEl.className = 'shrink-0 size-8 ' + config.icon + ' ' + config.iconClass;
    }
    if (confirmBtn) {
        confirmBtn.className = 'btn min-w-24 gap-2 ' + config.btnClass;
        confirmBtn.onclick = function() {
            closeConfirmModal(modalId);
            if (options.onConfirm) options.onConfirm();
        };
    }
    if (btnIcon && options.btnIcon) {
        btnIcon.className = 'shrink-0 size-4 ' + options.btnIcon;
    }
    if (btnText) {
        btnText.textContent = options.btnText || 'Confirm';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal(modalId = '{{ $id }}') {
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
            closeConfirmModal('{{ $id }}');
        }
    }
});
</script>
