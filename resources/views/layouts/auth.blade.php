<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Family Ledger') }} - @yield('title', 'Login')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary">Family Ledger</h1>
            <p class="text-base-content/60 mt-2">Safeguard your family's important information</p>
        </div>

        <!-- Auth Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-base-content/50 mt-6">
            &copy; {{ date('Y') }} Family Ledger. All rights reserved.
        </p>
    </div>

    <!-- Add empty Vue mount point for FlyonUI components if needed -->
    <div id="app"></div>

    @stack('scripts')
</body>
</html>
