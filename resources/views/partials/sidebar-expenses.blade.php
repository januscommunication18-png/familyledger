{{-- Expenses Mode Sidebar --}}
<nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-2">
    <ul class="space-y-1">
        {{-- Back to Main Navigation --}}
        <li class="mb-4">
            <form action="{{ route('expenses.exit-mode') }}" method="POST">
                @csrf
                <button type="submit" class="w-full group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800">
                    <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    </div>
                    <span class="nav-text">Back to Main</span>
                </button>
            </form>
        </li>

        <li class="my-2 border-t border-slate-800"></li>

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('expenses.dashboard') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.dashboard') || request()->routeIs('expenses.index')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </div>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        {{-- Transactions --}}
        <li>
            <a href="{{ route('expenses.transactions') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.transactions')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <span class="nav-text">Transactions</span>
            </a>
        </li>

        {{-- Categories/Envelopes --}}
        <li>
            <a href="{{ route('expenses.categories') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.categories')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <span class="nav-text">Categories</span>
            </a>
        </li>

        {{-- Import --}}
        <li>
            <a href="{{ route('expenses.import') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.import*')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                </div>
                <span class="nav-text">Import CSV</span>
            </a>
        </li>

        {{-- Reports --}}
        <li>
            <a href="{{ route('expenses.reports') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.reports')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                </div>
                <span class="nav-text">Reports</span>
            </a>
        </li>

        {{-- Share --}}
        <li>
            <a href="{{ route('expenses.share') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.share')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <span class="nav-text">Share</span>
            </a>
        </li>

        {{-- Alerts --}}
        <li>
            <a href="{{ route('expenses.alerts') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.alerts')) bg-gradient-to-r from-emerald-600 to-teal-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                </div>
                <span class="nav-text">Alerts</span>
            </a>
        </li>

        {{-- Bank Sync (Coming Soon) --}}
        <li>
            <a href="#" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium text-slate-400 opacity-60 cursor-not-allowed">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>
                </div>
                <span class="nav-text flex items-center gap-2">
                    Bank Sync
                    <span class="badge badge-xs badge-warning">Soon</span>
                </span>
            </a>
        </li>
    </ul>

    <div class="my-4 border-t border-slate-800"></div>

    {{-- Settings & Logout --}}
    <ul class="space-y-1">
        <li>
            <a href="{{ route('settings.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <span class="nav-text">Settings</span>
            </a>
        </li>
        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10">
                    <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    </div>
                    <span class="nav-text">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>
