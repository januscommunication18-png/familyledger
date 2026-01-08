@extends('backoffice.layouts.guest')

@section('content')
    <div x-data="{ code: '', autoSubmit: false }">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Security Verification</h2>
            <p class="text-gray-600 dark:text-gray-400">Enter the 6-digit code sent to your email</p>
        </div>

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

        <form method="POST" action="{{ route('backoffice.verify-code.submit') }}" class="space-y-6" id="verifyForm">
            @csrf

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Security Code
                </label>
                <div class="flex gap-2 justify-center">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            maxlength="1"
                            class="w-12 h-14 text-center text-2xl font-bold border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                            data-code-input="{{ $i }}"
                            x-on:input="handleInput($event, {{ $i }})"
                            x-on:keydown="handleKeydown($event, {{ $i }})"
                            x-on:paste="handlePaste($event)"
                            inputmode="numeric"
                            pattern="[0-9]*"
                        >
                    @endfor
                </div>
                <input type="hidden" name="code" x-model="code">
            </div>

            <button
                type="submit"
                class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            >
                Verify Code
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Didn't receive the code?</p>
            <form method="POST" action="{{ route('backoffice.resend-code') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                    Resend Code
                </button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('backoffice.login') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                &larr; Back to Login
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('codeInput', () => ({
        code: '',

        handleInput(event, index) {
            const input = event.target;
            const value = input.value.replace(/[^0-9]/g, '');
            input.value = value;

            if (value && index < 5) {
                const next = document.querySelector(`[data-code-input="${index + 1}"]`);
                if (next) next.focus();
            }

            this.updateCode();
        },

        handleKeydown(event, index) {
            if (event.key === 'Backspace' && !event.target.value && index > 0) {
                const prev = document.querySelector(`[data-code-input="${index - 1}"]`);
                if (prev) {
                    prev.focus();
                    prev.value = '';
                }
            }
        },

        handlePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);

            digits.split('').forEach((digit, i) => {
                const input = document.querySelector(`[data-code-input="${i}"]`);
                if (input) input.value = digit;
            });

            this.updateCode();

            if (digits.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        },

        updateCode() {
            let code = '';
            for (let i = 0; i < 6; i++) {
                const input = document.querySelector(`[data-code-input="${i}"]`);
                code += input ? input.value : '';
            }
            this.code = code;
        }
    }));
});

// Simple input handling without Alpine data binding issues
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('[data-code-input]');
    const hiddenInput = document.querySelector('input[name="code"]');

    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;

            if (value && index < 5) {
                inputs[index + 1].focus();
            }

            updateHiddenInput();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);

            digits.split('').forEach((digit, i) => {
                if (inputs[i]) inputs[i].value = digit;
            });

            updateHiddenInput();

            if (digits.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });
    });

    function updateHiddenInput() {
        let code = '';
        inputs.forEach(input => {
            code += input.value;
        });
        hiddenInput.value = code;
    }
});
</script>
@endpush
