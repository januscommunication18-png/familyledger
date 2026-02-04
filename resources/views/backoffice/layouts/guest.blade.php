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

    <!-- Tailwind CSS (pre-built) via jsDelivr with SRI -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" integrity="sha256-tq2XQC7duQPnpdenPuR6Z5IE773aRSGjkcutnfUJuTI=" crossorigin="anonymous">

    <!-- Custom primary colors & dark mode -->
    <style>
        .bg-primary-50 { background-color: #faf5ff; }
        .bg-primary-100 { background-color: #f3e8ff; }
        .bg-primary-600 { background-color: #9333ea; }
        .bg-primary-900 { background-color: #581c87; }
        .text-primary-300 { color: #d8b4fe; }
        .text-primary-700 { color: #7e22ce; }
        .hover\:bg-primary-700:hover { background-color: #7e22ce; }
        .focus\:ring-primary-500:focus { --tw-ring-color: #a855f7; }
        .focus\:border-primary-500:focus { border-color: #a855f7; }

        /* Dark mode */
        .dark .dark\:bg-gray-900 { background-color: #111827; }
        .dark .dark\:bg-primary-900 { background-color: #581c87; }
        .dark .dark\:text-white { color: #ffffff; }
        .dark .dark\:text-gray-400 { color: #9ca3af; }
    </style>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }

        /* Alert Text Color Fixes for Better Visibility */
        .alert-success, .bg-green-50 {
            background-color: #dcfce7 !important;
            border-color: #86efac !important;
        }
        .alert-success *, .text-green-800, .text-green-700 {
            color: #166534 !important;
        }
        .alert-error, .bg-red-50 {
            background-color: #fee2e2 !important;
            border-color: #fca5a5 !important;
        }
        .alert-error *, .text-red-800, .text-red-700 {
            color: #991b1b !important;
        }
        .alert-warning, .bg-amber-50, .bg-yellow-50 {
            background-color: #fef3c7 !important;
            border-color: #fcd34d !important;
        }
        .alert-warning *, .text-amber-800, .text-yellow-800 {
            color: #92400e !important;
        }
        .alert-info, .bg-blue-50 {
            background-color: #dbeafe !important;
            border-color: #93c5fd !important;
        }
        .alert-info *, .text-blue-800, .text-blue-700 {
            color: #1e40af !important;
        }
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

    <!-- Alpine.js via jsDelivr with SRI -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js" defer integrity="sha256-tgDjY9mdlURNtUrL+y3v/smueSqpmgkim82geOW1VkM=" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
