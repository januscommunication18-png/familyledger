<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Backoffice' }} - Family Ledger Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS (pre-built) via jsDelivr -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Custom primary colors & dark mode -->
    <style>
        .bg-primary-50 { background-color: #faf5ff; }
        .bg-primary-100 { background-color: #f3e8ff; }
        .bg-primary-600 { background-color: #9333ea; }
        .bg-primary-900 { background-color: #581c87; }
        .bg-primary-900\/20, .dark .bg-primary-900\/20 { background-color: rgba(88, 28, 135, 0.2); }
        .text-primary-300 { color: #d8b4fe; }
        .text-primary-700 { color: #7e22ce; }
        .hover\:bg-primary-700:hover { background-color: #7e22ce; }

        /* Dark mode support */
        .dark .dark\:bg-gray-800 { background-color: #1f2937; }
        .dark .dark\:bg-gray-900 { background-color: #111827; }
        .dark .dark\:bg-gray-700 { background-color: #374151; }
        .dark .dark\:bg-gray-600 { background-color: #4b5563; }
        .dark .dark\:text-gray-100 { color: #f3f4f6; }
        .dark .dark\:text-gray-300 { color: #d1d5db; }
        .dark .dark\:text-gray-400 { color: #9ca3af; }
        .dark .dark\:text-primary-300 { color: #d8b4fe; }
        .dark .dark\:border-gray-700 { border-color: #374151; }
        .dark .dark\:border-gray-800 { border-color: #1f2937; }
        .dark .dark\:hover\:bg-gray-700:hover { background-color: #374151; }
        .dark .dark\:bg-primary-900 { background-color: #581c87; }
        .dark .dark\:bg-green-900\/20 { background-color: rgba(20, 83, 45, 0.2); }
        .dark .dark\:bg-red-900\/20 { background-color: rgba(127, 29, 29, 0.2); }
        .dark .dark\:border-green-800 { border-color: #166534; }
        .dark .dark\:border-red-800 { border-color: #991b1b; }
        .dark .dark\:text-green-300 { color: #86efac; }
        .dark .dark\:text-red-300 { color: #fca5a5; }
    </style>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }

        /* Alert Text Color Fixes for Better Visibility */
        .alert-success, .bg-green-50 {
            background-color: #dcfce7 !important;
            border-color: #86efac !important;
        }
        .text-green-800, .text-green-700 {
            color: #166534 !important;
        }
        .alert-error, .bg-red-50 {
            background-color: #fee2e2 !important;
            border-color: #fca5a5 !important;
        }
        .text-red-800, .text-red-700 {
            color: #991b1b !important;
        }
        .alert-warning, .bg-amber-50, .bg-yellow-50 {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
        }
        .text-amber-800, .text-yellow-800, .text-amber-700 {
            color: #92400e !important;
        }
        .alert-info, .bg-blue-50 {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
        }
        .text-blue-800, .text-blue-700 {
            color: #1e40af !important;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen"
      x-data="{
          sidebarOpen: true,
          theme: localStorage.getItem('backoffice_theme') || 'light',
          setTheme(newTheme) {
              this.theme = newTheme;
              localStorage.setItem('backoffice_theme', newTheme);
              if (newTheme === 'dark') {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="if (theme === 'dark') document.documentElement.classList.add('dark')">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside x-show="sidebarOpen"
               x-transition:enter="transition-transform duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition-transform duration-300"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 lg:relative lg:translate-x-0">

            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">FL</span>
                    </div>
                    <span class="font-semibold text-lg">Backoffice</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('backoffice.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('backoffice.clients.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.clients.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Clients</span>
                </a>

                <a href="{{ route('backoffice.account-recovery.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.account-recovery.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Account Recovery</span>
                </a>

                <a href="{{ route('backoffice.package-plans.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.package-plans.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Package Plans</span>
                </a>

                <a href="{{ route('backoffice.discount-codes.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.discount-codes.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Discount Codes</span>
                </a>

                <a href="{{ route('backoffice.invoices.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.invoices.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Invoices</span>
                </a>

                <a href="{{ route('backoffice.drip-campaigns.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.drip-campaigns.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Drip Campaigns</span>
                </a>

                <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Settings</p>

                    <a href="{{ route('backoffice.settings.profile') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.settings.profile') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>My Profile</span>
                    </a>

                    <a href="{{ route('backoffice.settings.changePassword') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.settings.changePassword') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Change Password</span>
                    </a>

                    <a href="{{ route('backoffice.settings.dbReset') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('backoffice.settings.dbReset') ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>DB Reset</span>
                    </a>
                </div>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                        <span class="text-primary-700 dark:text-primary-300 font-semibold">
                            {{ substr(Auth::guard('backoffice')->user()->name, 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ Auth::guard('backoffice')->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::guard('backoffice')->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('backoffice.logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold">{{ $header ?? 'Dashboard' }}</h1>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button @click="setTheme(theme === 'dark' ? 'light' : 'dark')"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900">
                <!-- Flash Messages -->
                @if (session('message'))
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm text-green-800 dark:text-green-300">{{ session('message') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <ul class="text-sm text-red-800 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js via jsDelivr (CSP-allowed) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Form Double Submit Prevention --}}
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName !== 'FORM') return;
                if (form.hasAttribute('data-no-submit-protection')) return;
                if (form.hasAttribute('data-submitting')) {
                    e.preventDefault();
                    return false;
                }
                form.setAttribute('data-submitting', 'true');
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
                submitButtons.forEach(function(btn) {
                    btn.setAttribute('data-original-content', btn.innerHTML);
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    btn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Processing...</span></span>';
                });
                setTimeout(function() {
                    form.removeAttribute('data-submitting');
                    submitButtons.forEach(function(btn) {
                        btn.disabled = false;
                        btn.classList.remove('opacity-75', 'cursor-not-allowed');
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
