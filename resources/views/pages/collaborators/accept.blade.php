@extends('layouts.auth')

@section('title', 'Accept Invitation')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">You're Invited!</h1>
            <p class="text-slate-600 mt-2">{{ $invite->inviter->name }} has invited you to join their family circle</p>
        </div>

        <!-- Invitation Card -->
        <div class="card bg-white shadow-xl">
            <div class="card-body p-8 lg:p-10">
                @if(session('error'))
                    <div class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Inviter Info -->
                <div class="flex items-center gap-4 p-4 bg-base-200/50 rounded-xl mb-6">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-12">
                            <span class="text-lg">{{ strtoupper(substr($invite->inviter->name ?? 'U', 0, 1)) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800">{{ $invite->inviter->name }}</div>
                        <div class="text-sm text-slate-500">Invited you as {{ $invite->relationship_info['label'] }}</div>
                    </div>
                </div>

                @if($invite->message)
                    <div class="bg-primary/5 border-l-4 border-primary p-4 rounded-r-xl mb-6">
                        <p class="text-sm text-slate-600 italic">"{{ $invite->message }}"</p>
                    </div>
                @endif

                <!-- Access Summary -->
                <div class="mb-6">
                    <h3 class="font-medium text-slate-800 mb-3">What you'll be able to access:</h3>

                    <div class="space-y-3">
                        @foreach($invite->familyMembers as $member)
                            <div class="flex items-center gap-3 p-3 border rounded-lg">
                                <div class="avatar placeholder">
                                    <div class="bg-secondary/10 text-secondary rounded-full w-10">
                                        <span>{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-sm">{{ $member->first_name }} {{ $member->last_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $member->relationship ?? 'Family Member' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Role Info -->
                <div class="flex items-center gap-2 p-3 bg-{{ $invite->role_info['color'] }}/10 rounded-lg mb-6">
                    <span class="badge badge-{{ $invite->role_info['color'] }}">{{ $invite->role_info['label'] }}</span>
                    <span class="text-sm text-slate-600">{{ $invite->role_info['description'] }}</span>
                </div>

                @if(isset($emailMismatch) && $emailMismatch)
                    <!-- Email Mismatch Warning -->
                    <div class="alert alert-warning mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        <div>
                            <div class="font-medium">Email Mismatch</div>
                            <p class="text-sm">This invitation was sent to <strong>{{ $invite->email }}</strong>, but you're logged in as <strong>{{ $user->email }}</strong>.</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 mb-4">Please log in with the correct email address to accept this invitation.</p>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline btn-block">
                        Log out and try again
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>

                @elseif(isset($needsLogin) && $needsLogin)
                    <!-- Needs Login -->
                    <div class="text-center">
                        <p class="text-slate-600 mb-4">You already have an account. Please log in to accept this invitation.</p>
                        <a href="{{ route('login', ['redirect' => route('collaborator.accept', $invite->token)]) }}" class="btn btn-primary btn-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                            Log In to Accept
                        </a>
                        <p class="text-sm text-slate-500 mt-4">
                            Forgot your password?
                            <a href="{{ route('password.request', ['email' => $invite->email]) }}" class="text-primary hover:underline">Reset it here</a>
                        </p>
                    </div>

                @elseif(isset($needsSignup) && $needsSignup)
                    <!-- Needs Signup -->
                    <div class="text-center">
                        <p class="text-slate-600 mb-4">Create an account to accept this invitation and access the shared family information.</p>
                        <a href="{{ route('register', ['email' => $invite->email, 'first_name' => $invite->first_name, 'last_name' => $invite->last_name, 'redirect' => route('collaborator.accept', $invite->token)]) }}"
                           id="create-account-btn"
                           class="btn btn-primary btn-block"
                           onclick="handleCreateAccount(this)">
                            <svg id="create-account-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                            <span id="create-account-text">Create Account</span>
                            <span id="create-account-loading" class="loading loading-spinner loading-sm hidden"></span>
                        </a>
                    </div>
                    <script>
                        function handleCreateAccount(btn) {
                            btn.classList.add('btn-disabled', 'pointer-events-none');
                            document.getElementById('create-account-icon').classList.add('hidden');
                            document.getElementById('create-account-text').textContent = 'Creating...';
                            document.getElementById('create-account-loading').classList.remove('hidden');
                        }
                    </script>

                @else
                    <!-- Ready to Accept -->
                    <div class="space-y-3">
                        <form action="{{ route('collaborator.accept.process', $invite->token) }}" method="POST" onsubmit="handleAcceptSubmit(this)">
                            @csrf
                            <button type="submit" id="accept-btn" class="btn btn-primary btn-block gap-2">
                                <svg id="accept-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span id="accept-text">Accept Invitation</span>
                                <span id="accept-loading" class="loading loading-spinner loading-sm hidden"></span>
                            </button>
                        </form>

                        <form action="{{ route('collaborator.decline', $invite->token) }}" method="POST" onsubmit="handleDeclineSubmit(this)">
                            @csrf
                            <button type="submit" id="decline-btn" class="btn btn-ghost btn-block text-slate-500">
                                <span id="decline-text">Decline</span>
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
                            document.getElementById('accept-text').textContent = 'Accepting...';
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

                <!-- Expiration Notice -->
                <div class="mt-6 pt-4 border-t text-center">
                    <p class="text-xs text-slate-400">
                        This invitation expires {{ $invite->expires_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-sm text-slate-500">
                Questions? Contact {{ $invite->inviter->name }} at {{ $invite->inviter->email }}
            </p>
        </div>
    </div>
</div>
@endsection
