<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for authenticated user management.
 */
class AuthController extends Controller
{
    /**
     * Get the authenticated user's information.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'user' => new UserResource($user),
            'tenant' => new TenantResource($user->tenant),
        ]);
    }

    /**
     * Logout the user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        Log::info('API: User logged out', ['user_id' => $user->id]);

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Refresh the user's token.
     * Creates a new token and revokes the old one.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        // Create a new token
        $token = $user->createToken($request->device_name)->plainTextToken;

        Log::info('API: Token refreshed', ['user_id' => $user->id]);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Token refreshed successfully');
    }
}
