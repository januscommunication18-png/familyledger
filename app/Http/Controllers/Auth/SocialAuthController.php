<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Controller for social authentication (Google, Apple, Facebook).
 */
class SocialAuthController extends Controller
{
    /**
     * Supported OAuth providers.
     */
    protected array $providers = ['google', 'apple', 'facebook'];

    /**
     * Redirect to OAuth provider.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(string $provider, Request $request)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Invalid provider');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Social auth failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect('/login')->with('error', 'Authentication failed. Please try again.');
        }

        // Check if social account already exists
        $socialAccount = SocialAccount::byProvider($provider, $socialUser->getId())->first();

        if ($socialAccount) {
            // Login existing user
            $user = $socialAccount->user;

            if (!$user->is_active) {
                return redirect('/login')->with('error', 'Your account has been deactivated.');
            }

            // Update tokens
            $socialAccount->update([
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
                'avatar' => $socialUser->getAvatar(),
                'expires_at' => isset($socialUser->expiresIn)
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]);

            $user->recordLogin();
            Auth::login($user, true);

            // Check if MFA is required
            if ($user->hasTwoFactorEnabled() || $user->hasSmsMfaEnabled()) {
                session(['mfa_required' => true, 'mfa_user_id' => $user->id]);
                return redirect('/auth/mfa');
            }

            return redirect('/dashboard');
        }

        // Check if email exists (link accounts)
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Link social account to existing user
            $this->createSocialAccount($existingUser, $provider, $socialUser);

            $existingUser->recordLogin();
            Auth::login($existingUser, true);

            if ($existingUser->hasTwoFactorEnabled() || $existingUser->hasSmsMfaEnabled()) {
                session(['mfa_required' => true, 'mfa_user_id' => $existingUser->id]);
                return redirect('/auth/mfa');
            }

            return redirect('/dashboard');
        }

        // Create new user
        $user = $this->createUserFromSocial($provider, $socialUser);

        Auth::login($user, true);

        // Redirect to onboarding for new users
        return redirect('/onboarding');
    }

    /**
     * Create a new user from social provider data.
     */
    protected function createUserFromSocial(string $provider, $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            // Create tenant (Family Circle) for new user
            $tenant = Tenant::create([
                'name' => explode(' ', $socialUser->getName())[0] . "'s Family",
                'slug' => Str::slug($socialUser->getEmail()) . '-' . Str::random(6),
            ]);

            // Create user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(), // Social auth emails are verified
                'avatar' => $socialUser->getAvatar(),
                'auth_provider' => $provider,
                'role' => User::ROLE_PARENT, // Default role for new signups
                'password' => bcrypt(Str::random(32)), // Random password (not used)
            ]);

            // Create social account link
            $this->createSocialAccount($user, $provider, $socialUser);

            Log::info('New user created via social auth', [
                'user_id' => $user->id,
                'provider' => $provider,
            ]);

            return $user;
        });
    }

    /**
     * Create social account link.
     */
    protected function createSocialAccount(User $user, string $provider, $socialUser): SocialAccount
    {
        return SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'avatar' => $socialUser->getAvatar(),
            'expires_at' => isset($socialUser->expiresIn)
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);
    }

    /**
     * Unlink a social account.
     */
    public function unlink(Request $request, string $provider)
    {
        $user = $request->user();

        // Ensure user has another way to login
        $socialCount = $user->socialAccounts()->count();
        $hasPassword = $user->auth_provider === User::PROVIDER_EMAIL;

        if ($socialCount <= 1 && !$hasPassword) {
            return response()->json([
                'error' => 'Cannot unlink. You need at least one way to sign in.',
            ], 400);
        }

        $user->socialAccounts()->where('provider', $provider)->delete();

        return response()->json(['message' => 'Account unlinked successfully']);
    }
}
