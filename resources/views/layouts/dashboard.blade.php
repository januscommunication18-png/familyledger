<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Family Ledger') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Pushed Styles -->
    @stack('styles')

    <style>
        /* Hide elements with x-cloak until Alpine initializes */
        [x-cloak] { display: none !important; }

        /* Sidebar collapsed/expanded states */
        .sidebar {
            width: 72px;
            transition: width 0.2s ease;
        }
        .sidebar:hover,
        .sidebar.expanded {
            width: 260px;
        }
        .sidebar .nav-text,
        .sidebar .profile-info,
        .sidebar .logo-text,
        .sidebar .storage-info {
            opacity: 0;
            white-space: nowrap;
            transition: opacity 0.15s ease;
        }
        .sidebar:hover .nav-text,
        .sidebar:hover .profile-info,
        .sidebar:hover .logo-text,
        .sidebar:hover .storage-info,
        .sidebar.expanded .nav-text,
        .sidebar.expanded .profile-info,
        .sidebar.expanded .logo-text,
        .sidebar.expanded .storage-info {
            opacity: 1;
        }
        .sidebar .toggle-btn {
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        .sidebar:hover .toggle-btn,
        .sidebar.expanded .toggle-btn {
            opacity: 1;
        }
        .main-content {
            margin-left: 72px;
            transition: margin-left 0.2s ease;
        }
        .sidebar.expanded ~ .main-content {
            margin-left: 260px;
        }
        /* Mobile */
        @media (max-width: 1023px) {
            .sidebar {
                width: 260px;
                transform: translateX(-100%);
            }
            .sidebar-open .sidebar {
                transform: translateX(0);
            }
            .sidebar .nav-text,
            .sidebar .profile-info,
            .sidebar .logo-text,
            .sidebar .storage-info {
                opacity: 1;
            }
            .main-content {
                margin-left: 0;
            }
        }
        .sidebar-open .sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        /* Protected Image Styles */
        .protected-image-container {
            position: relative;
            overflow: hidden;
        }
        .protected-image {
            filter: blur(10px);
            transition: filter 0.3s ease;
        }
        .protected-image.blur-none {
            filter: blur(0);
        }
        .protected-image-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            cursor: pointer;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        .protected-image-container.verified .protected-image-overlay {
            opacity: 0;
            pointer-events: none;
        }
        .protected-image-container.verified .protected-image {
            filter: blur(0);
        }

        /* Alert Text Color Fixes for Better Visibility */
        .alert-success {
            background-color: #dcfce7 !important;
            border-color: #86efac !important;
            color: #166534 !important;
        }
        .alert-success * {
            color: #166534 !important;
        }
        .alert-error {
            background-color: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #991b1b !important;
        }
        .alert-error * {
            color: #991b1b !important;
        }
        .alert-warning {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
            color: #92400e !important;
        }
        .alert-warning * {
            color: #92400e !important;
        }
        .alert-info {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1e40af !important;
        }
        .alert-info * {
            color: #1e40af !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100">
    {{-- Toastr Notifications --}}
    <x-toastr />

    <div id="app" class="min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none lg:hidden" onclick="document.body.classList.remove('sidebar-open')"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar expanded fixed top-0 left-0 z-50 h-screen bg-slate-900 overflow-hidden">
            <div class="flex flex-col h-full">
                <!-- Logo/Brand -->
                <div class="flex items-center h-16 px-4 border-b border-slate-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <span class="logo-text text-lg font-bold text-white">Family Ledger</span>
                    </a>
                </div>

                <!-- Toggle Button -->
                <button onclick="toggleSidebar()" class="toggle-btn absolute top-4 right-2 w-6 h-6 rounded-md bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white hidden lg:flex">
                    <svg id="expand-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden"><path d="m9 18 6-6-6-6"/></svg>
                    <svg id="collapse-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>

                <!-- Close button (mobile) -->
                <button class="lg:hidden absolute top-4 right-3 w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400" onclick="document.body.classList.remove('sidebar-open')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <!-- Profile Card -->
                <a href="{{ route('settings.index') }}" class="mx-3 mt-4 mb-2 flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-800">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::disk('do_spaces')->url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 shrink-0 rounded-lg object-cover">
                    @else
                        <div class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="profile-info flex-1 min-w-0 overflow-hidden">
                        <p class="font-medium text-sm text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->role_name ?? 'Member' }}</p>
                    </div>
                </a>

                <!-- Navigation Menu -->
                @if(session('expenses_mode'))
                    @include('partials.sidebar-expenses')
                @elseif(session('coparenting_mode'))
                    @include('partials.sidebar-coparenting')
                @else
                <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-2">
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('dashboard')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                </div>
                                <span class="nav-text">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('family-circle.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('family-circle.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <span class="nav-text">Family Circle</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pets.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('pets.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center text-lg">
                                    🐾
                                </div>
                                <span class="nav-text">Pets</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('assets.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('assets.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                </div>
                                <span class="nav-text">Assets</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('documents.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('documents.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                </div>
                                <span class="nav-text">Documents</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('legal.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('legal.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14 14 2 2"/><path d="M16 4a2 2 0 1 1 4 0v2a2 2 0 0 1-2 2h-2V4Z"/><path d="M11 6 7 10"/><path d="m7 6 4 4"/><rect x="2" y="2" width="8" height="8" rx="2"/><path d="M11 18H5a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h6"/><path d="m14 14 6 6"/></svg>
                                </div>
                                <span class="nav-text">Legal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('family-resources.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('family-resources.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/><path d="M12 10v6"/><path d="m9 13 3-3 3 3"/></svg>
                                </div>
                                <span class="nav-text">Family Resources</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('goals-todo.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('goals-todo.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                </div>
                                <span class="nav-text">Goals & To-Do</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shopping.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('shopping.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                </div>
                                <span class="nav-text">Shopping List</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('journal.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('journal.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center text-lg">
                                    📔
                                </div>
                                <span class="nav-text">Journal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('collaborators.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('collaborators.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                </div>
                                <span class="nav-text">Collaborators</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('coparenting.index') }}"
                               onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('coparent-navigate', { detail: { url: '{{ route('coparenting.index') }}' } }));"
                               class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('coparenting.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/><path d="m18 15-2-2"/><path d="m15 18-2-2"/></svg>
                                </div>
                                <span class="nav-text">Co-parenting</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reminders.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('reminders.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                </div>
                                <span class="nav-text">Reminders</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('expenses.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('expenses.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                                </div>
                                <span class="nav-text">Expenses</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('people.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('people.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <span class="nav-text">People Directory</span>
                            </a>
                        </li>
                    </ul>

                    <div class="my-4 border-t border-slate-800"></div>

                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('settings.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('settings.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
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
                @endif
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content min-h-screen flex flex-col lg:ml-[72px]">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200/80">
                <div class="flex items-center h-16 px-4 lg:px-6">
                    <!-- Left Side - Page Title -->
                    <div class="flex items-center gap-3 min-w-0 flex-shrink-0">
                        <!-- Mobile Menu Toggle -->
                        <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden" onclick="document.body.classList.add('sidebar-open')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                        </button>
                        <!-- Current Page Name -->
                        <h1 class="text-lg font-semibold text-slate-800 truncate">@yield('page-name', 'Home')</h1>
                    </div>

                    <!-- Center - Search Bar -->
                    <div class="flex-1 flex justify-center px-4">
                        <div class="relative w-full max-w-md" x-data="globalSearch()" @click.outside="closeSearch()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 z-10"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text"
                                   x-model="query"
                                   @input.debounce.300ms="search()"
                                   @focus="showResults = query.length >= 2 && results.length > 0"
                                   @keydown.escape="closeSearch()"
                                   @keydown.arrow-down.prevent="navigateDown()"
                                   @keydown.arrow-up.prevent="navigateUp()"
                                   @keydown.enter.prevent="selectResult()"
                                   placeholder="Search members, documents, goals..."
                                   class="w-full h-10 pl-10 pr-4 rounded-xl bg-slate-100 border-0 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:bg-white">

                            <!-- Loading indicator -->
                            <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-4 w-4 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div x-show="showResults"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-slate-200 max-h-96 overflow-y-auto z-50">

                                <!-- No results -->
                                <div x-show="!loading && results.length === 0 && query.length >= 2" class="p-4 text-center text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    <p class="text-sm">No results found for "<span x-text="query" class="font-medium"></span>"</p>
                                </div>

                                <!-- Results grouped by category -->
                                <template x-for="(group, category) in groupedResults" :key="category">
                                    <div class="border-b border-slate-100 last:border-0">
                                        <div class="px-3 py-2 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider" x-text="category"></div>
                                        <template x-for="(result, index) in group" :key="result.url">
                                            <a :href="result.url"
                                               @mouseenter="selectedIndex = getGlobalIndex(category, index)"
                                               :class="{ 'bg-violet-50': selectedIndex === getGlobalIndex(category, index) }"
                                               class="flex items-center gap-3 px-3 py-2.5 hover:bg-violet-50 transition-colors cursor-pointer">
                                                <!-- Icon -->
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                                     :class="{
                                                         'bg-violet-100 text-violet-600': result.type === 'member',
                                                         'bg-blue-100 text-blue-600': result.type === 'circle',
                                                         'bg-emerald-100 text-emerald-600': result.type === 'person',
                                                         'bg-amber-100 text-amber-600': result.type === 'pet',
                                                         'bg-rose-100 text-rose-600': result.type === 'goal',
                                                         'bg-cyan-100 text-cyan-600': result.type === 'todo',
                                                         'bg-purple-100 text-purple-600': result.type === 'legal',
                                                         'bg-teal-100 text-teal-600': result.type === 'asset',
                                                         'bg-orange-100 text-orange-600': result.type === 'resource',
                                                         'bg-pink-100 text-pink-600': result.type === 'journal',
                                                         'bg-indigo-100 text-indigo-600': result.type === 'insurance',
                                                         'bg-lime-100 text-lime-600': result.type === 'tax',
                                                         'bg-fuchsia-100 text-fuchsia-600': result.type === 'shopping',
                                                         'bg-sky-100 text-sky-600': result.type === 'todolist',
                                                         'bg-green-100 text-green-600': result.type === 'expense'
                                                     }">
                                                    <template x-if="result.icon === 'user'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'users'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'address-book'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4z"/><path d="M4 10h4"/><path d="M4 14h4"/><path d="M12 10h4"/><path d="M12 14h4"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'paw'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="4" cy="8" r="2"/><path d="M9 14c0-2.5 2-4 3-4s3 1.5 3 4c0 3-3 5-3 5s-3-2-3-5Z"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'target'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'checkbox'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'scale'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'building-bank'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 10h18"/><path d="M5 6l7-3 7 3"/><path d="M4 10v11"/><path d="M20 10v11"/><path d="M8 14v3"/><path d="M12 14v3"/><path d="M16 14v3"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'folder'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'notebook'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 6h4"/><path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M16 2v20"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'shield'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'receipt'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'shopping-cart'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'wallet'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
                                                    </template>
                                                    <template x-if="result.icon === 'list'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
                                                    </template>
                                                </div>
                                                <!-- Content -->
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 truncate" x-text="result.title"></p>
                                                    <p class="text-xs text-slate-500 truncate" x-text="result.subtitle"></p>
                                                </div>
                                                <!-- Arrow -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="m9 18 6-6-6-6"/></svg>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                <!-- Quick tip -->
                                <div x-show="results.length > 0" class="px-3 py-2 bg-slate-50 text-xs text-slate-400 flex items-center gap-2">
                                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-500">↑↓</kbd>
                                    <span>Navigate</span>
                                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-500 ml-2">Enter</kbd>
                                    <span>Open</span>
                                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-500 ml-2">Esc</kbd>
                                    <span>Close</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Theme Toggle (Hidden - Light mode only for now) -->
                        {{--
                        <button id="theme-toggle" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800" title="Toggle theme">
                            <svg id="theme-icon-light" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <svg id="theme-icon-dark" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        </button>
                        --}}

                        <!-- Help -->
                        <button class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </button>

                        <!-- User Menu -->
                        <div class="dropdown dropdown-end">
                            <button tabindex="0" class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100">
                                @if(auth()->user()->avatar)
                                    <img src="{{ Storage::disk('do_spaces')->url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 shrink-0 rounded-lg object-cover">
                                @else
                                    <div class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                                        <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                                    </div>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 hidden sm:block"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden mt-2 w-56 p-2 bg-white shadow-xl border border-slate-200 rounded-xl">
                                <!-- User Info -->
                                <li class="p-3 mb-2 rounded-lg bg-slate-50 pointer-events-none">
                                    <p class="font-semibold text-sm text-slate-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                                </li>
                                <!-- Profile -->
                                <li>
                                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        Profile
                                    </a>
                                </li>
                                <!-- Settings -->
                                <li>
                                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Settings
                                    </a>
                                </li>
                                <li class="my-1 border-t border-slate-100"></li>
                                <!-- Logout -->
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                            Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <!-- Breadcrumbs & Page Actions -->
                @hasSection('breadcrumbs')
                    <div class="flex items-center justify-between mb-4">
                        <div class="breadcrumbs text-sm">
                            <ul>
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        Home
                                    </a>
                                </li>
                                @yield('breadcrumbs')
                            </ul>
                        </div>
                        @hasSection('page-actions')
                            <div class="flex items-center gap-2">
                                @yield('page-actions')
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Page Header -->
                @hasSection('page-title')
                    <div class="mb-6">
                        <h1 class="text-2xl lg:text-3xl font-bold text-slate-900">@yield('page-title')</h1>
                        @hasSection('page-description')
                            <p class="text-slate-500 mt-1">@yield('page-description')</p>
                        @endif
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white py-4 px-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-2 text-sm text-slate-400">
                    <p>&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
                    <div class="flex gap-4">
                        <a href="#" class="hover:text-violet-600">Privacy</a>
                        <a href="#" class="hover:text-violet-600">Terms</a>
                        <a href="#" class="hover:text-violet-600">Help</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const expandIcon = document.getElementById('expand-icon');
            const collapseIcon = document.getElementById('collapse-icon');

            sidebar.classList.toggle('expanded');

            if (sidebar.classList.contains('expanded')) {
                expandIcon.classList.add('hidden');
                collapseIcon.classList.remove('hidden');
                localStorage.setItem('sidebarExpanded', 'true');
            } else {
                expandIcon.classList.remove('hidden');
                collapseIcon.classList.add('hidden');
                localStorage.setItem('sidebarExpanded', 'false');
            }
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const expandIcon = document.getElementById('expand-icon');
            const collapseIcon = document.getElementById('collapse-icon');

            // Default is expanded, only collapse if explicitly set to false
            if (localStorage.getItem('sidebarExpanded') === 'false') {
                sidebar.classList.remove('expanded');
                expandIcon.classList.remove('hidden');
                collapseIcon.classList.add('hidden');
            }
        });

        // Theme - Force light mode only for now
        function initTheme() {
            const html = document.documentElement;
            html.setAttribute('data-theme', 'light');
        }

        document.addEventListener('DOMContentLoaded', initTheme);
    </script>

    {{-- Auto Logout After Inactivity --}}
    <div id="idleWarningModal" class="fixed inset-0 z-[60] hidden">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-warning/20 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2">Session Timeout Warning</h3>
                <p class="text-base-content/60 text-sm mb-4">You will be logged out in <span id="idleCountdown" class="font-bold text-warning">60</span> seconds due to inactivity.</p>
                <button type="button" onclick="resetIdleTimer()" class="btn btn-primary w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                    Stay Logged In
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const IDLE_TIMEOUT = 10 * 60 * 1000; // 10 minutes in milliseconds
        const WARNING_TIME = 60 * 1000; // Show warning 60 seconds before logout

        let idleTimer = null;
        let warningTimer = null;
        let countdownTimer = null;
        let countdownSeconds = 60;

        function showWarningModal() {
            const modal = document.getElementById('idleWarningModal');
            const countdownEl = document.getElementById('idleCountdown');
            countdownSeconds = 60;
            countdownEl.textContent = countdownSeconds;
            modal.classList.remove('hidden');

            // Start countdown
            countdownTimer = setInterval(function() {
                countdownSeconds--;
                countdownEl.textContent = countdownSeconds;

                if (countdownSeconds <= 0) {
                    clearInterval(countdownTimer);
                    performLogout();
                }
            }, 1000);
        }

        function hideWarningModal() {
            const modal = document.getElementById('idleWarningModal');
            modal.classList.add('hidden');
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
        }

        function performLogout() {
            // Create and submit logout form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("logout") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }

        window.resetIdleTimer = function() {
            // Clear existing timers
            if (idleTimer) clearTimeout(idleTimer);
            if (warningTimer) clearTimeout(warningTimer);

            // Hide warning if visible
            hideWarningModal();

            // Set warning timer (fires 60 seconds before logout)
            warningTimer = setTimeout(function() {
                showWarningModal();
            }, IDLE_TIMEOUT - WARNING_TIME);

            // Set logout timer
            idleTimer = setTimeout(function() {
                performLogout();
            }, IDLE_TIMEOUT);
        };

        // Activity events to track
        const activityEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'];

        // Throttle function to prevent too many resets
        let lastActivity = Date.now();
        function handleActivity() {
            const now = Date.now();
            // Only reset if more than 1 second has passed since last activity
            if (now - lastActivity > 1000) {
                lastActivity = now;
                // Only reset if warning modal is not showing
                const modal = document.getElementById('idleWarningModal');
                if (modal && modal.classList.contains('hidden')) {
                    resetIdleTimer();
                }
            }
        }

        // Add event listeners
        activityEvents.forEach(function(event) {
            document.addEventListener(event, handleActivity, { passive: true });
        });

        // Start the timer on page load
        document.addEventListener('DOMContentLoaded', function() {
            resetIdleTimer();
        });
    })();
    </script>

    {{-- Image Verification Modal --}}
    @include('partials.image-verification-modal')

    {{-- Global Co-parent Child Picker Modal (for sidebar navigation intercept) --}}
    @include('partials.coparent-child-picker-global')

    {{-- Global Notification Listener for Co-parent Messages --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Echo is available
        if (typeof window.Echo !== 'undefined') {
            const userId = {{ auth()->id() }};

            // Listen on the user's private notification channel
            window.Echo.private(`user.notifications.${userId}`)
                .listen('.coparent.message.received', (data) => {
                    // Don't show notification if already on that conversation page
                    if (window.location.pathname.includes(`/coparenting/messages/${data.conversation_id}`)) {
                        return;
                    }

                    // Show toast notification
                    showMessageNotification(data);
                });
        }
    });

    function showMessageNotification(data) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('message-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'message-toast-container';
            toastContainer.className = 'fixed top-20 right-4 z-[100] flex flex-col gap-2';
            document.body.appendChild(toastContainer);
        }

        // Category colors
        const categoryColors = {
            'General': { bg: 'bg-blue-500', icon: '💬' },
            'Schedule': { bg: 'bg-purple-500', icon: '📅' },
            'Medical': { bg: 'bg-red-500', icon: '🏥' },
            'Expense': { bg: 'bg-green-500', icon: '💰' },
            'Emergency': { bg: 'bg-orange-500', icon: '🚨' }
        };

        const cat = categoryColors[data.category] || categoryColors['General'];

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'animate-slideIn bg-white rounded-xl shadow-2xl border border-slate-200 p-4 max-w-sm cursor-pointer hover:shadow-xl transition-shadow';
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full ${cat.bg} flex items-center justify-center text-white text-lg flex-shrink-0">
                    ${cat.icon}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-slate-800 text-sm">${escapeHtml(data.sender_name)}</p>
                        <span class="text-xs text-slate-400">now</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-1">Re: ${escapeHtml(data.child_name)}</p>
                    <p class="text-sm text-slate-600 line-clamp-2">${escapeHtml(data.message_preview)}</p>
                </div>
                <button onclick="event.stopPropagation(); this.closest('.animate-slideIn').remove();" class="text-slate-400 hover:text-slate-600 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        `;

        // Click to go to conversation
        toast.onclick = function() {
            window.location.href = `/coparenting/messages/${data.conversation_id}`;
        };

        // Add to container
        toastContainer.appendChild(toast);

        // Play notification sound
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Onraxu8DGysnJxse+sJ2QhHdqY2Zve46epbK9xtPY3N7a1MrBs6SRfm9jXV5iaXeJm6m2wcvT2tzc2tXNwrSjkH5vYl1dYGd0hZiktL/K1Nvc3drVzcKzo5B+b2JdXWBnd4WZpLS/ytTb3N3a1c3CtKOQfm9iXV1gZ3eFmKS0v8rU29zd2tXNwrSjkH5vYl1dYGd3hZiktL/K1Nvc3drVzcK0o5B+b2JdXWBnd4WYpLS/ytTb3N3a1c3CtKOQfm9iXV1gZ3eFmKS0v8rU29zd2tXNwrSjkH5vYl1dYGd3');
            audio.volume = 0.5;
            audio.play().catch(() => {});
        } catch(e) {}

        // Auto remove after 8 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 8000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>

    <style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    .animate-slideIn {
        animation: slideIn 0.3s ease-out forwards;
    }
    </style>

    {{-- Global Search Component --}}
    <script>
    function globalSearch() {
        return {
            query: '',
            results: [],
            loading: false,
            showResults: false,
            selectedIndex: -1,

            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    this.showResults = false;
                    return;
                }

                this.loading = true;
                this.showResults = true;

                try {
                    const response = await fetch(`{{ route('search') }}?q=${encodeURIComponent(this.query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    this.results = data.results || [];
                    this.selectedIndex = -1;
                } catch (error) {
                    console.error('Search error:', error);
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            get groupedResults() {
                const groups = {};
                this.results.forEach(result => {
                    if (!groups[result.category]) {
                        groups[result.category] = [];
                    }
                    groups[result.category].push(result);
                });
                return groups;
            },

            getGlobalIndex(category, localIndex) {
                let globalIndex = 0;
                for (const [cat, items] of Object.entries(this.groupedResults)) {
                    if (cat === category) {
                        return globalIndex + localIndex;
                    }
                    globalIndex += items.length;
                }
                return -1;
            },

            navigateDown() {
                if (this.selectedIndex < this.results.length - 1) {
                    this.selectedIndex++;
                }
            },

            navigateUp() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                }
            },

            selectResult() {
                if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                    window.location.href = this.results[this.selectedIndex].url;
                }
            },

            closeSearch() {
                this.showResults = false;
                this.selectedIndex = -1;
            }
        }
    }
    </script>

    {{-- Form Double Submit Prevention --}}
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName !== 'FORM') return;

                // Skip if form has data-no-submit-protection attribute
                if (form.hasAttribute('data-no-submit-protection')) return;

                // Check if already submitting
                if (form.hasAttribute('data-submitting')) {
                    e.preventDefault();
                    return false;
                }

                // Mark form as submitting
                form.setAttribute('data-submitting', 'true');

                // Find all submit buttons in the form
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');

                submitButtons.forEach(function(btn) {
                    // Store original content
                    btn.setAttribute('data-original-content', btn.innerHTML);
                    btn.setAttribute('data-original-width', btn.offsetWidth + 'px');

                    // Disable button
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');

                    // Keep minimum width to prevent layout shift
                    btn.style.minWidth = btn.getAttribute('data-original-width');

                    // Add loading spinner
                    const originalText = btn.textContent.trim();
                    btn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Processing...</span>
                        </span>
                    `;
                });

                // Reset form after 10 seconds (failsafe for errors that don't redirect)
                setTimeout(function() {
                    form.removeAttribute('data-submitting');
                    submitButtons.forEach(function(btn) {
                        btn.disabled = false;
                        btn.classList.remove('opacity-75', 'cursor-not-allowed');
                        btn.style.minWidth = '';
                        if (btn.hasAttribute('data-original-content')) {
                            btn.innerHTML = btn.getAttribute('data-original-content');
                        }
                    });
                }, 10000);
            });
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
