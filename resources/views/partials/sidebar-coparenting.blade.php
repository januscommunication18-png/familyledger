{{-- Co-Parenting Mode Sidebar --}}
<nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-2">
    <ul class="space-y-1">
        {{-- Back to Main Navigation --}}
        <li class="mb-4">
            <form action="{{ route('coparenting.exit-mode') }}" method="POST">
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

        {{-- Co-parent (Dashboard) --}}
        <li>
            <a href="{{ route('coparenting.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.index') || request()->routeIs('coparenting.intro')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/><path d="m18 15-2-2"/><path d="m15 18-2-2"/></svg>
                </div>
                <span class="nav-text">Co-parent</span>
            </a>
        </li>

        {{-- Calendar --}}
        <li>
            <a href="{{ route('coparenting.calendar') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.calendar')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                </div>
                <span class="nav-text">Calendar</span>
            </a>
        </li>

        {{-- Activities --}}
        <li>
            <a href="{{ route('coparenting.activities') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.activities')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <span class="nav-text">Activities</span>
            </a>
        </li>

        {{-- Child Info --}}
        <li>
            <a href="{{ route('coparenting.child-info') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.child-info') || request()->routeIs('coparenting.children.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                </div>
                <span class="nav-text">Child Info</span>
            </a>
        </li>

        {{-- Messages --}}
        <li>
            <a href="{{ route('coparenting.messages') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.messages')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <span class="nav-text">Messages</span>
            </a>
        </li>

        {{-- Expenses --}}
        <li>
            <a href="{{ route('coparenting.expenses') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.expenses')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <span class="nav-text">Expenses</span>
            </a>
        </li>

        {{-- Parenting Plan (Coming Soon) --}}
        <li>
            <a href="{{ route('coparenting.parenting-plan') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.parenting-plan')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                </div>
                <span class="nav-text flex items-center gap-2">
                    Parenting Plan
                    <span class="badge badge-xs badge-warning">Soon</span>
                </span>
            </a>
        </li>

        {{-- Actual Time --}}
        <li>
            <a href="{{ route('coparenting.actual-time') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.actual-time')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span class="nav-text">Actual Time</span>
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
