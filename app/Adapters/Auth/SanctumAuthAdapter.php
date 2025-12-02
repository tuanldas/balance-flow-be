<?php

namespace App\Adapters\Auth;

use App\Adapters\Auth\Contracts\AuthAdapterInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Laravel Sanctum authentication adapter
 *
 * Implements token-based authentication using Laravel Sanctum
 */
class SanctumAuthAdapter implements AuthAdapterInterface
{
    /**
     * Generate authentication token for user
     *
     * @return string Plain text token
     */
    public function generateToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke current access token
     */
    public function revokeCurrentToken(User $user): bool
    {
        $user->currentAccessToken()->delete();

        return true;
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): bool
    {
        $user->tokens()->delete();

        return true;
    }

    /**
     * Verify user credentials
     *
     * @param  array  $credentials  ['email' => string, 'password' => string]
     */
    public function verifyCredentials(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    /**
     * Get currently authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }
}
