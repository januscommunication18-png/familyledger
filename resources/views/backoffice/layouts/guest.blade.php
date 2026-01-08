<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Login' }} - Family Ledger Backoffice</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                            800: '#6b21a8',
                            900: '#581c87',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex"
      x-data="{ theme: localStorage.getItem('backoffice_theme') || 'light' }"
      x-init="if (theme === 'dark') document.documentElement.classList.add('dark')">

    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-primary-600 dark:bg-primary-900 p-12 flex-col justify-between relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-xl">FL</span>
                </div>
                <span class="text-white font-semibold text-2xl">Family Ledger</span>
            </div>
        </div>

        <div class="relative z-10">
            <h1 class="text-4xl font-bold text-white mb-4">Backoffice Portal</h1>
            <p class="text-white/80 text-lg max-w-md">
                Secure administrative access to manage clients, subscriptions, and platform settings.
            </p>
        </div>

        <div class="relative z-10">
            <p class="text-white/60 text-sm">&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
        </div>
    </div>

    <!-- Right Panel - Form -->
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
                <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold">FL</span>
                </div>
                <span class="font-semibold text-xl text-gray-900 dark:text-white">Backoffice</span>
            </div>

            @yield('content')

            @hasSection('footer')
                <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    @yield('footer')
                </div>
            @endif
        </div>
    </div>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    @stack('scripts')
</body>
</html>
