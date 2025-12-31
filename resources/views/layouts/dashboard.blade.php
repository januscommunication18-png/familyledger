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
    </style>
</head>
<body class="min-h-screen bg-slate-100">
    {{-- Toastr Notifications --}}
    <x-toastr />

    <div id="app" class="min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none lg:hidden" onclick="document.body.classList.remove('sidebar-open')"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed top-0 left-0 z-50 h-screen bg-slate-900 overflow-hidden">
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
                    <svg id="expand-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    <svg id="collapse-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden"><path d="m15 18-6-6 6-6"/></svg>
                </button>

                <!-- Close button (mobile) -->
                <button class="lg:hidden absolute top-4 right-3 w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400" onclick="document.body.classList.remove('sidebar-open')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <!-- Profile Card -->
                <a href="{{ route('settings.index') }}" class="mx-3 mt-4 mb-2 flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-800">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                        <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <div class="profile-info flex-1 min-w-0 overflow-hidden">
                        <p class="font-medium text-sm text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->role_name ?? 'Member' }}</p>
                    </div>
                </a>

                <!-- Navigation Menu -->
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
                            <a href="{{ route('tasks.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('tasks.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                </div>
                                <span class="nav-text">To Do List</span>
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
                            <a href="{{ route('journey.index') }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-lg text-sm font-medium @if(request()->routeIs('journey.*')) bg-gradient-to-r from-violet-600 to-purple-600 text-white @else text-slate-400 hover:text-white hover:bg-slate-800 @endif">
                                <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                                </div>
                                <span class="nav-text">Journey</span>
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
                        <div class="relative w-full max-w-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" placeholder="Search..." class="w-full h-10 pl-10 pr-4 rounded-xl bg-slate-100 border-0 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:bg-white">
                        </div>
                    </div>

                    <!-- Right Side Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800" title="Toggle theme">
                            <!-- Sun icon (shown in dark mode) -->
                            <svg id="theme-icon-light" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg id="theme-icon-dark" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        </button>

                        <!-- Help -->
                        <button class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </button>

                        <!-- User Menu -->
                        <div class="dropdown dropdown-end">
                            <button tabindex="0" class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100">
                                <div class="w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                                    <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                                </div>
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
                <!-- Breadcrumbs -->
                @hasSection('breadcrumbs')
                    <div class="breadcrumbs text-sm mb-4">
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

            if (localStorage.getItem('sidebarExpanded') === 'true') {
                sidebar.classList.add('expanded');
                expandIcon.classList.add('hidden');
                collapseIcon.classList.remove('hidden');
            }
        });

        // Theme toggle functionality
        function initTheme() {
            const themeToggle = document.getElementById('theme-toggle');
            const lightIcon = document.getElementById('theme-icon-light');
            const darkIcon = document.getElementById('theme-icon-dark');
            const html = document.documentElement;

            // Get saved theme or detect system preference
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const currentTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

            // Apply theme
            function setTheme(theme) {
                html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);

                if (theme === 'dark') {
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                } else {
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
                }
            }

            // Initialize
            setTheme(currentTheme);

            // Toggle on click
            themeToggle.addEventListener('click', function() {
                const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });

            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('theme')) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', initTheme);
    </script>

    @stack('scripts')
</body>
</html>
