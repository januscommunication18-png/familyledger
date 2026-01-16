<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Accept Invitation - {{ config('app.name', 'Family Ledger') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-violet-50 via-white to-purple-50">
    <!-- Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-4xl">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl shadow-lg shadow-violet-500/30 mb-4">
                    @if($invite->is_coparent_invite)
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-slate-800">You're Invited!</h1>
                <p class="text-slate-500 mt-2 text-lg">
                    @if($invite->is_coparent_invite)
                        Join as a co-parent on Family Ledger
                    @else
                        Join {{ $invite->inviter->name }}'s family circle
                    @endif
                </p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden">
                @if(session('error'))
                    <div class="bg-rose-50 border-b border-rose-100 px-6 py-4">
                        <div class="flex items-center gap-3 text-rose-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <div class="p-8 lg:p-10">
                    <!-- Inviter Section -->
                    <div class="flex items-center gap-4 p-5 bg-gradient-to-r from-violet-50 to-purple-50 rounded-2xl mb-8">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-white">{{ strtoupper(substr($invite->inviter->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 text-lg">{{ $invite->inviter->name }}</p>
                            <p class="text-slate-500">
                                @if($invite->is_coparent_invite)
                                    Inviting you as <span class="font-medium text-violet-600">Co-parent ({{ ucfirst($invite->parent_role ?? 'Parent') }})</span>
                                @else
                                    Inviting you as <span class="font-medium text-violet-600">{{ $invite->relationship_info['label'] }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($invite->message)
                        <div class="relative mb-8">
                            <div class="absolute -left-2 top-0 bottom-0 w-1 bg-gradient-to-b from-violet-400 to-purple-500 rounded-full"></div>
                            <div class="bg-slate-50 rounded-xl p-5 ml-4">
                                <p class="text-slate-600 italic leading-relaxed">"{{ $invite->message }}"</p>
                                <p class="text-sm text-slate-400 mt-2">- {{ $invite->inviter->name }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- What You'll Access -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">
                            @if($invite->is_coparent_invite)
                                Children You'll Have Access To
                            @else
                                Family Members You'll See
                            @endif
                        </h3>

                        <div class="grid gap-3">
                            @foreach($invite->familyMembers as $member)
                                <div class="flex items-center gap-4 p-4 bg-white border border-slate-200 rounded-xl hover:border-violet-300 hover:shadow-md transition-all">
                                    @if($member->profile_image_url)
                                        <img src="{{ $member->profile_image_url }}" alt="{{ $member->full_name }}" class="w-12 h-12 rounded-xl object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                            <span class="text-lg font-bold text-white">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                                        <p class="text-sm text-slate-500">
                                            @if($invite->is_coparent_invite && $member->age)
                                                {{ $member->age }} years old
                                            @else
                                                {{ ucfirst($member->relationship ?? 'Family Member') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if($invite->is_coparent_invite)
                                        <span class="badge badge-sm bg-pink-100 text-pink-700 border-0">Child</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Your Role/Permissions -->
                    @php
                        $roleColor = $invite->role_info['color'] ?? 'primary';
                        $bgColor = match($roleColor) {
                            'error' => 'bg-rose-50 border-rose-200 text-rose-700',
                            'warning' => 'bg-amber-50 border-amber-200 text-amber-700',
                            'success' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                            'info' => 'bg-sky-50 border-sky-200 text-sky-700',
                            default => 'bg-violet-50 border-violet-200 text-violet-700',
                        };
                    @endphp
                    <div class="flex items-center gap-3 p-4 {{ $bgColor }} border rounded-xl mb-8">
                        <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold">{{ $invite->role_info['label'] }} Access</p>
                            <p class="text-sm opacity-80">{{ $invite->role_info['description'] }}</p>
                        </div>
                    </div>

                    <!-- Action Section -->
                    @if(isset($emailMismatch) && $emailMismatch)
                        <!-- Email Mismatch Warning -->
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgb(217 119 6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-amber-800">Email Mismatch</p>
                                    <p class="text-sm text-amber-700 mt-1">
                                        This invitation was sent to <strong>{{ $invite->email }}</strong>, but you're logged in as <strong>{{ $user->email }}</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline btn-block h-12">
                            Log out and try again
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>

                    @elseif(isset($needsLogin) && $needsLogin)
                        <!-- Needs Login -->
                        <div class="text-center">
                            <div class="bg-slate-50 rounded-xl p-6 mb-6">
                                <div class="w-14 h-14 mx-auto rounded-xl bg-slate-200 flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgb(71 85 105)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                                </div>
                                <p class="text-slate-600">You already have an account with <strong>{{ $invite->email }}</strong>.</p>
                                <p class="text-slate-500 text-sm mt-1">Please log in to accept this invitation.</p>
                            </div>
                            <a href="{{ route('login', ['redirect' => route('collaborator.accept', $invite->token)]) }}" class="btn btn-primary btn-block h-12 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                                Log In to Accept
                            </a>
                            <p class="text-sm text-slate-500 mt-4">
                                Forgot your password?
                                <a href="{{ route('password.request', ['email' => $invite->email]) }}" class="text-violet-600 hover:underline font-medium">Reset it here</a>
                            </p>
                        </div>

                    @elseif(isset($needsSignup) && $needsSignup)
                        <!-- Needs Signup -->
                        <div class="text-center">
                            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-6 mb-6">
                                <div class="w-14 h-14 mx-auto rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mb-4 shadow-lg shadow-violet-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                </div>
                                <p class="text-slate-700 font-medium">Create your free account</p>
                                <p class="text-slate-500 text-sm mt-1">Join Family Ledger to access shared family information</p>
                            </div>
                            <a href="{{ route('register', ['email' => $invite->email, 'first_name' => $invite->first_name, 'last_name' => $invite->last_name, 'redirect' => route('collaborator.accept', $invite->token)]) }}"
                               id="create-account-btn"
                               class="btn btn-primary btn-block h-12 gap-2"
                               onclick="handleCreateAccount(this)">
                                <svg id="create-account-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                <span id="create-account-text">Create Account & Accept</span>
                                <span id="create-account-loading" class="loading loading-spinner loading-sm hidden"></span>
                            </a>
                        </div>
                        <script>
                            function handleCreateAccount(btn) {
                                btn.classList.add('btn-disabled', 'pointer-events-none');
                                document.getElementById('create-account-icon').classList.add('hidden');
                                document.getElementById('create-account-text').textContent = 'Creating account...';
                                document.getElementById('create-account-loading').classList.remove('hidden');
                            }
                        </script>

                    @else
                        <!-- Ready to Accept -->
                        <div class="space-y-3">
                            <form action="{{ route('collaborator.accept.process', $invite->token) }}" method="POST" onsubmit="handleAcceptSubmit(this)">
                                @csrf
                                <button type="submit" id="accept-btn" class="btn btn-primary btn-block h-14 text-base gap-2 shadow-lg shadow-violet-500/30 hover:shadow-xl hover:shadow-violet-500/40 transition-all">
                                    <svg id="accept-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span id="accept-text">Accept Invitation</span>
                                    <span id="accept-loading" class="loading loading-spinner hidden"></span>
                                </button>
                            </form>

                            <form action="{{ route('collaborator.decline', $invite->token) }}" method="POST" onsubmit="handleDeclineSubmit(this)">
                                @csrf
                                <button type="submit" id="decline-btn" class="btn btn-ghost btn-block h-12 text-slate-500 hover:text-slate-700 hover:bg-slate-100">
                                    <span id="decline-text">Decline Invitation</span>
                                    <span id="decline-loading" class="loading loading-spinner loading-sm hidden"></span>
                                </button>
                            </form>
                        </div>
                        <script>
                            function handleAcceptSubmit(form) {
                                const btn = document.getElementById('accept-btn');
                                btn.classList.add('btn-disabled');
                                btn.disabled = true;
                                document.getElementById('accept-icon').classList.add('hidden');
                                document.getElementById('accept-text').textContent = 'Accepting invitation...';
                                document.getElementById('accept-loading').classList.remove('hidden');
                            }
                            function handleDeclineSubmit(form) {
                                const btn = document.getElementById('decline-btn');
                                btn.classList.add('btn-disabled');
                                btn.disabled = true;
                                document.getElementById('decline-text').textContent = 'Declining...';
                                document.getElementById('decline-loading').classList.remove('hidden');
                            }
                        </script>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-8 py-5 border-t border-slate-100">
                    <div class="flex items-center justify-between text-sm">
                        <p class="text-slate-400">
                            Expires {{ $invite->expires_at->diffForHumans() }}
                        </p>
                        <p class="text-slate-500">
                            Questions? <a href="mailto:{{ $invite->inviter->email }}" class="text-violet-600 hover:underline">Contact {{ $invite->inviter->name }}</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Trust Badge -->
            <div class="text-center mt-8">
                <div class="inline-flex items-center gap-2 text-slate-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    Your data is secure with Family Ledger
                </div>
            </div>
        </div>
    </div>
</body>
</html>
