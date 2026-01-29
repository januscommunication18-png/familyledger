{{-- Toastr Notifications Component --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<div id="toastr-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 max-w-sm">
    @if(session('success'))
    <div class="toastr-notification toastr-success animate-slide-in" role="alert">
        <div class="flex items-start gap-3 p-4 rounded-lg bg-green-100 border border-green-300 shadow-lg">
            <div class="shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-green-800">Success</p>
                <p class="text-sm text-green-700 mt-0.5 font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.closest('.toastr-notification').remove()" class="shrink-0 text-green-600 hover:text-green-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="toastr-notification toastr-error animate-slide-in" role="alert">
        <div class="flex items-start gap-3 p-4 rounded-lg bg-red-100 border border-red-300 shadow-lg">
            <div class="shrink-0 w-6 h-6 rounded-full bg-red-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-800">Error</p>
                <p class="text-sm text-red-700 mt-0.5 font-medium">{{ session('error') }}</p>
            </div>
            <button type="button" onclick="this.closest('.toastr-notification').remove()" class="shrink-0 text-red-600 hover:text-red-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('warning'))
    <div class="toastr-notification toastr-warning animate-slide-in" role="alert">
        <div class="flex items-start gap-3 p-4 rounded-lg bg-amber-100 border border-amber-300 shadow-lg">
            <div class="shrink-0 w-6 h-6 rounded-full bg-amber-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">Warning</p>
                <p class="text-sm text-amber-700 mt-0.5 font-medium">{{ session('warning') }}</p>
            </div>
            <button type="button" onclick="this.closest('.toastr-notification').remove()" class="shrink-0 text-amber-600 hover:text-amber-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div class="toastr-notification toastr-info animate-slide-in" role="alert">
        <div class="flex items-start gap-3 p-4 rounded-lg bg-blue-100 border border-blue-300 shadow-lg">
            <div class="shrink-0 w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-blue-800">Info</p>
                <p class="text-sm text-blue-700 mt-0.5 font-medium">{{ session('info') }}</p>
            </div>
            <button type="button" onclick="this.closest('.toastr-notification').remove()" class="shrink-0 text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>
    @endif
</div>

<style>
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out forwards;
}

.animate-slide-out {
    animation: slide-out 0.3s ease-in forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss toastr notifications after 5 seconds
    const notifications = document.querySelectorAll('.toastr-notification');
    notifications.forEach(function(notification) {
        setTimeout(function() {
            notification.classList.remove('animate-slide-in');
            notification.classList.add('animate-slide-out');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    });
});
</script>
@endif
