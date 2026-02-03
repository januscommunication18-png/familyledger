<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Family Ledger') }} - @yield('title', 'Home')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
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
<body class="min-h-screen bg-base-200">
    @yield('content')

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
